<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PhilSMS gateway client.
 *
 * Deliberately small: one outbound call, no delivery-report webhook, no inbound
 * route. Nothing here can receive a reply, which is half of what keeps these
 * messages one-way (the other half is registering an alphanumeric sender ID
 * with PhilSMS).
 */
class PhilSms
{
    /**
     * PhilSMS accepts comma-separated recipients, so a blast goes out in a
     * handful of calls instead of one per person. That matters because there is
     * no queue worker — the send happens inside the staff member's request.
     */
    public const BATCH_SIZE = 100;

    /** A single GSM-7 message; longer messages are split and billed per part. */
    public const GSM_SINGLE = 160;
    public const GSM_CONCAT = 153;

    /** Unicode messages (emoji, ñ, accents) fit far fewer characters. */
    public const UNICODE_SINGLE = 70;
    public const UNICODE_CONCAT = 67;

    public static function enabled(): bool
    {
        return (bool) config('services.philsms.enabled');
    }

    public static function configured(): bool
    {
        return (bool) config('services.philsms.token');
    }

    /**
     * Send one message to many numbers.
     *
     * @param  array<int, string>  $numbers  Already normalised to 639XXXXXXXXX.
     * @return array{sent: array<int, string>, failed: array<string, string>}
     *         `failed` maps number => reason, so each announcement row can be
     *         marked individually rather than the whole blast being one verdict.
     */
    public static function send(array $numbers, string $message): array
    {
        $numbers = array_values(array_unique(array_filter($numbers)));

        $result = ['sent' => [], 'failed' => []];

        if ($numbers === []) {
            return $result;
        }

        // Dry run. The subscription may not exist yet, and local development
        // must never bill the office, so the payload is logged and the caller
        // sees the same shape it would see on a real send.
        if (!self::enabled()) {
            Log::info('PhilSMS dry run — not sent', [
                'recipients' => count($numbers),
                'numbers'    => $numbers,
                'message'    => $message,
                'segments'   => self::segments($message),
            ]);
            $result['sent'] = $numbers;

            return $result;
        }

        if (!self::configured()) {
            foreach ($numbers as $number) {
                $result['failed'][$number] = 'PhilSMS token is not configured.';
            }

            return $result;
        }

        foreach (array_chunk($numbers, self::BATCH_SIZE) as $batch) {
            $error = self::sendBatch($batch, $message);

            if ($error === null) {
                foreach ($batch as $number) {
                    $result['sent'][] = $number;
                }
                continue;
            }

            foreach ($batch as $number) {
                $result['failed'][$number] = $error;
            }
        }

        return $result;
    }

    /**
     * @return string|null  Null on success, otherwise the reason to record.
     */
    private static function sendBatch(array $batch, string $message): ?string
    {
        try {
            $response = Http::acceptJson()
                ->withToken(config('services.philsms.token'))
                ->timeout(30)
                ->retry(2, 500, throw: false)
                ->post(config('services.philsms.endpoint'), [
                    'recipient' => implode(',', $batch),
                    'sender_id' => config('services.philsms.sender_id'),
                    'type'      => 'plain',
                    'message'   => $message,
                ]);
        } catch (\Throwable $e) {
            // A gateway outage must not become a 500 page for the staff member.
            Log::error('PhilSMS request failed', [
                'recipients' => count($batch),
                'error'      => $e->getMessage(),
            ]);

            return 'Could not reach the SMS gateway.';
        }

        if ($response->failed()) {
            $reason = $response->json('message') ?? ('HTTP ' . $response->status());
            Log::error('PhilSMS rejected the request', [
                'recipients' => count($batch),
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            return is_string($reason) ? $reason : 'The SMS gateway rejected the request.';
        }

        // A 200 with status=error still means nothing was delivered.
        if ($response->json('status') === 'error') {
            $reason = $response->json('message') ?? 'The SMS gateway reported an error.';
            Log::error('PhilSMS returned an error', [
                'recipients' => count($batch),
                'body'       => $response->body(),
            ]);

            return is_string($reason) ? $reason : 'The SMS gateway reported an error.';
        }

        return null;
    }

    /**
     * How many messages one text will actually be billed as.
     */
    public static function segments(string $message): int
    {
        $length = mb_strlen($message);

        if ($length === 0) {
            return 0;
        }

        $unicode = self::isUnicode($message);
        $single  = $unicode ? self::UNICODE_SINGLE : self::GSM_SINGLE;
        $concat  = $unicode ? self::UNICODE_CONCAT : self::GSM_CONCAT;

        if ($length <= $single) {
            return 1;
        }

        return (int) ceil($length / $concat);
    }

    /**
     * Emoji and accented characters force the whole message into unicode, which
     * cuts the per-part allowance from 160 characters to 70.
     */
    public static function isUnicode(string $message): bool
    {
        return (bool) preg_match('/[^\x20-\x7E\r\n]/u', $message);
    }

    /**
     * Characters left before the message costs another segment.
     */
    public static function remainingInSegment(string $message): int
    {
        $length   = mb_strlen($message);
        $segments = max(1, self::segments($message));
        $unicode  = self::isUnicode($message);

        $capacity = $segments === 1
            ? ($unicode ? self::UNICODE_SINGLE : self::GSM_SINGLE)
            : $segments * ($unicode ? self::UNICODE_CONCAT : self::GSM_CONCAT);

        return max(0, $capacity - $length);
    }
}
