<?php

namespace App\Support;

use App\Mail\ResetCodeMail;
use App\Models\EmployerAccountTransfer;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// ── Ang pag-ilis sa authorized contact sa usa ka employer account.
// ──
// ── Duha ang makabuhat niini — ang LRA/SRA (kasagaran) ug ang Admin (kung wala
// ── sila) — ug parehas gyud dapat ang gibuhat nila. Usa ra ka lugar ang
// ── naghubad niini aron ang audit ug ang epekto dili gyud magkalahi.
// ──
// ── Duha ka paagi:
// ──   reset_code    — mo-padala ug 6-digit code sa bag-ong email. Walay tawo
// ──                   sa opisina nga makahibalo sa password. Mao ni ang una
// ──                   nga pilion kung naay email ang bag-ong HR.
// ──   temp_password — mo-buhat ug pansamantalang password nga basahon sa
// ──                   telepono. Para sa nagtawag nga wala gyuy maabot nga
// ──                   inbox — mao man kadto ang rason nganong nitawag siya.
// ──                   Ang must_change_password gi-set, mao nga ang employer
// ──                   dili makaabot sa bisan unsang page hangtod mo-ilis siya.
class EmployerAccountRecovery
{
    public const METHOD_RESET_CODE    = 'reset_code';
    public const METHOD_TEMP_PASSWORD = 'temp_password';

    /**
     * @return array{method:string, temp_password:?string, mail_failed:bool}
     */
    public static function perform(User $employerUser, array $data, User $actor): array
    {
        $nsrp   = $employerUser->employerNsrp;
        $method = ($data['method'] ?? self::METHOD_RESET_CODE) === self::METHOD_TEMP_PASSWORD
            ? self::METHOD_TEMP_PASSWORD
            : self::METHOD_RESET_CODE;

        $previousContact = $nsrp->contact_person;
        $previousEmail   = $employerUser->email;

        // Ang staff row gigamit gihapon para sa daan nga column; ang Admin
        // walay staff row, mao nga ang performed_by_user_id maoy kanunay
        // napuno ug kana ang basahon sa bag-ong mga rekord.
        $staffId = Staff::where('user_id', $actor->users_id)->value('staff_id');

        // Ang staff ang nagsulat sa pansamantalang password, dili ang sistema.
        //
        // Basahon ni siya sa telepono. Ang gi-generate nga napulog-duha ka
        // karakter kinahanglan pang litukon, isulat sa papel ug balikon sa
        // dili pa siya mapuslan, ug ang matag balik usa ka higayon nga masayop.
        // Ang gi-type sa staff mao gyud ang iyang isulti.
        //
        // Luwas ra kini tungod sa mosunod: gi-set gihapon ang
        // must_change_password sa ubos, ug ang EnsurePasswordChanged nagpugong
        // sa account gikan sa tanan nga panid hangtod mopuli ang tinuod nga
        // password ubos sa tibuok PasswordPolicy. Parehas nga rason sa
        // App\Support\StarterPassword ug sa walk-in nga porma.
        //
        // Ang plaintext gibalik kausa ra ngadto sa nagtawag — wala gyud kini
        // gitipigan, wala gi-email, ug dili na makita pag-usab.
        $tempPassword = $method === self::METHOD_TEMP_PASSWORD
            ? ($data['temp_password'] ?? null)
            : null;

        DB::transaction(function () use (
            $employerUser, $nsrp, $data, $previousContact, $previousEmail,
            $staffId, $actor, $method, $tempPassword
        ) {
            // ── ANG TIBUOK ACCOUNT, DILI ANG USA KA KOMPANYA.
            // ──
            // ── PESO IT, 2026-08-26: usa ka HR mahimong maghawid ug duha ka
            // ── kompanya sa usa ka email. Ang handover mahitungod sa TAWO —
            // ── mibiya siya, ug ang mipuli mao nay bag-ong authorized contact.
            // ── Ang email nagpuyo sa account ug mo-usab para sa tanan, mao nga
            // ── ang ngalan ug ang numero mo-uban gyud. Kung ang usa ra ka
            // ── kompanya ang mabalhin, kana kay pagbahin sa account, ug wala
            // ── pa kana gipangayo. ──
            $contactUpdate = array_filter([
                'contact_person' => $data['new_contact_person'],
                'position_title' => $data['new_position_title'] ?? null,
                'mobile_number'  => $data['new_mobile_number'] ?? null,
            ], fn($v) => $v !== null);

            $employerUser->employerCompanies()->update($contactUpdate);

            $update = [
                'name'  => $data['new_contact_person'],
                'email' => $data['new_email'],
            ];

            if ($tempPassword !== null) {
                $update['password']             = Hash::make($tempPassword);
                $update['must_change_password'] = true;
            }

            $employerUser->update($update);

            EmployerAccountTransfer::create([
                'employer_id'             => $nsrp->employer_nsrp_registrations_id,
                'performed_by'            => $staffId,
                'performed_by_user_id'    => $actor->users_id,
                'method'                  => $method,
                'previous_contact_person' => $previousContact,
                'previous_email'          => $previousEmail,
                'new_contact_person'      => $data['new_contact_person'],
                'new_email'               => $data['new_email'],
                'reason'                  => $data['reason'],
            ]);
        });

        if ($method === self::METHOD_TEMP_PASSWORD) {
            // Ang daan nga reset code dili na angay molihok: nausab na ang
            // password, ug ang code sa daan nga email dili na sakto.
            DB::table('password_reset_codes')->where('email', $previousEmail)->delete();

            return [
                'method'        => $method,
                'temp_password' => $tempPassword,
                'mail_failed'   => false,
            ];
        }

        return [
            'method'        => $method,
            'temp_password' => null,
            'mail_failed'   => !self::sendCode($data['new_email'], $data['new_contact_person']),
        ];
    }

    /**
     * Reset only the password. The contact on the account is left alone.
     *
     * PESO, 2026-08-26: the admin is asked for this when the employer still
     * has the same person in charge and simply cannot get in — a forgotten
     * password, a phone that no longer receives the reset e-mail. Running a
     * handover for that would rewrite the contact person and the e-mail on the
     * record to the values they already hold, and file an audit row saying the
     * account changed hands when nothing did.
     *
     * The audit row is still written, with the contact unchanged on both
     * sides: somebody with admin rights set a password on an account that is
     * not theirs, and that is worth keeping whatever the reason.
     *
     * Returns the plaintext once. It is never stored and never e-mailed.
     */
    public static function resetPassword(
        User $employerUser,
        string $reason,
        User $actor,
        string $tempPassword
    ): string {
        $nsrp = $employerUser->employerNsrp;

        // Ang nagpangita sa password mao ang nagsulat niini.
        //
        // Kaniadto gikan kini sa unang ngalan sa contact person. Mas maayo pa
        // kana kaysa random nga hilo, apan wala gihapoy pagpili ang tawo nga
        // maoy mobasa niini sa telepono — ug siya ra ang nakabalo kung unsay
        // masabtan sa linya, unsay dali litukon, ug unsay dili maglibog sa
        // ngalan sa kompanya. `must_change_password` sa ubos nagpadala kaniya
        // diretso sa change screen, diin ang tibuok PasswordPolicy ang mo-hukom.

        $staffId = Staff::where('user_id', $actor->users_id)->value('staff_id');
        $email        = $employerUser->email;

        DB::transaction(function () use ($employerUser, $nsrp, $reason, $staffId, $actor, $tempPassword, $email) {
            $employerUser->update([
                'password'             => Hash::make($tempPassword),
                // Ang pansamantala kay pansamantala gyud: dili siya makaabot sa
                // bisan unsang page hangtod mo-ilis siya.
                'must_change_password' => true,
            ]);

            EmployerAccountTransfer::create([
                'employer_id'             => $nsrp?->employer_nsrp_registrations_id,
                'performed_by'            => $staffId,
                'performed_by_user_id'    => $actor->users_id,
                'method'                  => self::METHOD_TEMP_PASSWORD,
                'previous_contact_person' => $nsrp?->contact_person,
                'previous_email'          => $email,
                // Parehas ang duha ka kilid: walay nabalhin, ang password ra
                // ang gi-ilisan.
                'new_contact_person'      => $nsrp?->contact_person,
                'new_email'               => $email,
                'reason'                  => $reason,
            ]);
        });

        // Ang daan nga reset code dili na angay molihok — nausab na ang password.
        DB::table('password_reset_codes')->where('email', $email)->delete();

        return $tempPassword;
    }

    // ── Ang code gitipigan una sa dili pa mo-padala, mao nga ang pagkapakyas
    // ── sa mail server dili mo-guba sa handover — ma-resend ra kini pinaagi sa
    // ── Forgot Password sa login page. ──
    private static function sendCode(string $email, string $name): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $email],
            [
                'code'       => $code,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        try {
            Mail::to($email)->send(new ResetCodeMail($code, $name));
            return true;
        } catch (\Throwable $e) {
            Log::error('Employer account recovery code could not be sent', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ── Nausab ba gyud ang contact, o gi-submit ra ang porma nga wala hilabti?
    // ──
    // ── Ang porma pun-on na sa kasamtangan nga datos, mao nga ang staff nga
    // ── nag-ilis lang sa Account Status mo-submit gihapon sa parehas nga
    // ── contact person ug email. Kung wala ni ilhi, ang matag pag-ilis sa
    // ── status mo-padala ug reset code ngadto sa employer nga walay giusab —
    // ── mensahe nga wala silay gipangayo, ug usa ka audit row nga bakak. ──
    public static function contactChanged(User $employerUser, array $data): bool
    {
        $nsrp = $employerUser->employerNsrp;

        $same = fn(?string $new, ?string $current) => trim((string) $new) === trim((string) $current);

        return ! $same($data['new_contact_person'] ?? null, $nsrp?->contact_person)
            || ! $same($data['new_email'] ?? null, $employerUser->email)
            || ! $same($data['new_position_title'] ?? null, $nsrp?->position_title)
            || ! $same($data['new_mobile_number'] ?? null, $nsrp?->mobile_number);
    }

    // ── Ang mga rule, parehas sa staff ug sa admin. ──
    public static function rules(int $employerUserId): array
    {
        return [
            'new_contact_person' => 'required|string|max:255',
            'new_position_title' => 'nullable|string|max:255',
            'new_email'          => 'required|email|max:255|unique:users,email,' . $employerUserId . ',users_id',
            'new_mobile_number'  => 'nullable|string|max:20',
            'reason'             => 'required|string|max:500',
            'method'             => 'nullable|in:reset_code,temp_password',

            // Gi-type sa staff, dili gi-generate. Ang gidaghanon dinhi gituyo
            // nga luag: ang tibuok PasswordPolicy modagan kung ilisan na kini
            // sa employer, ug ang pagpugos ug simbolo sa usa ka pulong nga
            // basahon sa telepono maghimo lang niini nga sayop nga malitok.
            'temp_password'      => 'nullable|string|min:4|max:64',
        ];
    }

    public static function messages(): array
    {
        return [
            'new_email.unique' => 'That email is already used by another account.',
            'reason.required'  => 'State why the contact is being changed — it is kept on record.',
            // Tulo ka ngalan sa parehas nga pagsulay: required, required_if
            // (gipili ang temporary password) ug required_with (gitikan ang
            // Reset). Ang default nga teksto sa Laravel mo-ngalan sa field ug
            // sa kondisyon; ang desk nangayo lang unsay isulat.
            'temp_password.required'      => 'Write the temporary password you are giving this employer.',
            'temp_password.required_if'   => 'Write the temporary password you will read out to them.',
            'temp_password.required_with' => 'Write the temporary password you will read out to them.',
            'temp_password.min'           => 'The temporary password needs at least 4 characters.',
        ];
    }
}
