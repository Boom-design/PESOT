<?php

namespace App\Support;

// ── Ang porma sa usa ka email address nga igo ra aron mailhan sa tag-iya,
// ── apan dili igo aron magamit sa dili tag-iya. Sama sa gibuhat sa GCash sa
// ── pangalan.
// ──
// ── Gigamit ra kini sa employer nga account recovery: ang bag-ong HR nga wala
// ── kabalo unsa nga email ang gigamit sa pag-register makailhan pa gihapon
// ── niini kung iyaha ba o iya sa ni-hawa nga HR.
// ──
// ── reachselwyn1566@gmail.com  →  r*************6@g****.com
// ──
// ── Ang gipabilin: unang letra ug katapusang letra sa local part, unang letra
// ── sa domain, ug ang tinuod nga TLD. Ang gitas-on makita — kana ang bili sa
// ── pag-ila — apan walay igong letra aron matag-an ang address. ──
class MaskedEmail
{
    public static function mask(?string $email): string
    {
        $email = trim((string) $email);

        if ($email === '' || !str_contains($email, '@')) {
            return '';
        }

        [$local, $domain] = explode('@', $email, 2);

        return self::maskLocal($local) . '@' . self::maskDomain($domain);
    }

    private static function maskLocal(string $local): string
    {
        $length = mb_strlen($local);

        // Mubo kaayo — tabuni tanan. Ang "a@..." walay itago kung ipakita ang
        // unang letra, ug wala pud kini ikatabang sa pag-ila.
        if ($length <= 2) {
            return str_repeat('*', max($length, 1));
        }

        return mb_substr($local, 0, 1)
             . str_repeat('*', $length - 2)
             . mb_substr($local, -1);
    }

    private static function maskDomain(string $domain): string
    {
        // Ang TLD gipabilin nga buo: ang ".com" ug ".gov.ph" walay itago, ug
        // ang kalainan nila makatabang sa pag-ila.
        $dot = mb_strpos($domain, '.');

        if ($dot === false) {
            return self::maskLocal($domain);
        }

        $name = mb_substr($domain, 0, $dot);
        $tld  = mb_substr($domain, $dot);

        $masked = mb_strlen($name) <= 1
            ? str_repeat('*', max(mb_strlen($name), 1))
            : mb_substr($name, 0, 1) . str_repeat('*', mb_strlen($name) - 1);

        return $masked . $tld;
    }
}
