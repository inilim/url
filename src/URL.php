<?php

namespace Inilim\URL;

use Inilim\Tool\Str;
use Inilim\PunyCode\Punycode;

/**
 * TODO что если url начинается сразу с домена
 * TODO проверка path
 * TODO проверка query
 * TODO проверка fragment
 * TODO проверить на наличие двух и более разных алфавитов, encode'ер это не проверяет
 */
final class URL
{
    const LEN_MAX_ITEM_LEVEL_DOMAIN = 63;
    const LEN_MIN_ITEM_LEVEL_DOMAIN = 2;
    const LEN_MAX_DOMAIN            = 255;

    public readonly Punycode $punycode;

    function __construct()
    {
        $this->punycode = new Punycode;
    }

    function test(string $url): bool
    {
        if (!\str_contains($url, '://')) return false;

        $details = Str::parseURL($url);

        if (
            $details['protocol'] === null
            ||
            $details['domain'] === null
        ) {
            return false;
        }

        // ------------------------------------------------------------------
        // 
        // ------------------------------------------------------------------

        if (!$this->protocol($details['protocol'])) {
            return false;
        }

        // ------------------------------------------------------------------
        // INFO 
        // ------------------------------------------------------------------

        if (!$this->domain($details['domain'])) {
            return false;
        }

        // ------------------------------------------------------------------
        // 
        // ------------------------------------------------------------------

        if (!$this->path($details['path'])) {
            return false;
        }
        // 



        if (($details['query'] ?? '') !== '') {
            $p = $details['query'];
        }
    }

    function path(?string $path): bool
    {
        if ($path === null) {
            return true;
        }

        $path = Str::trim(
            Str::trim($path),
            '/'
        );

        if ($path === '') {
            return true;
        }

        if (Str::isMatch('#[^\p{Latin}\/\d_-]#iu', $path)) {
            return false;
        }
    }

    function protocol(string $protocol): bool
    {
        $protocol = $this->normalizeProtocol($protocol);
        return \str_contains(
            Str::wrap(\_data()->URLProtocolsAsString('|'), '|'),
            Str::wrap($protocol, '|')
        );
    }

    // ------------------------------------------------------------------
    // Domain
    // ------------------------------------------------------------------

    function domain(string $domain): bool
    {
        $domain = $this->normalizeDomain($domain);

        // INFO первичная проверка
        if (
            !\str_contains($domain, '.')
            ||
            Str::length($domain) > self::LEN_MAX_DOMAIN
            ||
            // INFO 
            Str::isMatch('#[^\pL\.\[\]\:\d\-]#u', $domain)
            ||
            Str::startsWith($domain, ['-', '.'])
            ||
            Str::endsWith($domain, ['-', '.'])
            ||
            // "custom-.ws" < почему то браузер открывает именно с доменом "ws"
            //                          "-."
            Str::contains($domain, ['-.', '.-', '..'])
        ) {
            return false;
        }

        // INFO IPv6 +
        if (Str::contains($domain, ['[', ']'])) {
            return $this->domainIPv6($domain);
        }
        // INFO Punycode
        elseif (
            \str_starts_with($domain, Punycode::PREFIX)
            ||
            \str_contains($domain, '.' . Punycode::PREFIX)
        ) {
            return $this->domainPunycode($domain);
        }
        // INFO IP +
        elseif (Str::isMatch('#\.\d{1,3}\.#u', $domain) && !Str::isMatch('#\pL#u', $domain)) {
            return $this->domainIP($domain);
        }
        // INFO IDN - это доменные имена, содержащие символы национальных алфавитов
        elseif (Str::isMatch('#[^\p{Latin}\d\-\.]#u', $domain)) {
            return $this->domainIDN($domain);
        }
        // INFO классический домен
        else {
            return $this->domainClassic($domain);
        }
    }

    function domainClassic(string $domain): bool
    {
        return Str::isMatch('#^([a-z\d\-]++\.)+[a-z]++$#u', $domain);
    }

    function domainIDN(string $domain): bool
    {
        // (?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++
        // INFO есть домены содержашие национальный алфавит и латиницу
        // if (Str::isMatch('#\p{Latin}#u', $domain)) {
        //     return false;
        // }
        // TODO проверить на наличие двух и более разных алфавитов (исключая латиницу), encode'ер это не проверяет
        return Str::isMatch('#^([\pL\d\-]++\.)+[\pL]++$#u', $domain) && $this->checkPunycodeEncode($domain);
        // return Str::isMatch('#^([\pL\d\-]++\.)+[\pL\d]++$#u', $domain);
    }

    function domainIP(string $domain): bool
    {
        return Str::isMatch('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#u', $domain);
    }

    function domainPunycode(string $domain): bool
    {
        // (xn--[a-z\d-]++\.)*+xn--[a-z\d-]++

        return Str::isMatch([
            '#^(' . Punycode::PREFIX . '[a-z\d-]++\.)*+' . Punycode::PREFIX . '[a-z\d-]++$#u',
            // для таких случаях "0--0.xn--p1ai" и "1xbet.xn--6frz82g"
            '#^([a-z\d-]++\.)*+' . Punycode::PREFIX . '[a-z\d-]++$#u',
            // для таких случаях "xn-------43dcbbaejg4abf1alafg6bji4blgc8dql5b7b1co34a.com"
            '#^(' . Punycode::PREFIX . '[a-z\d-]++\.)*+[a-z\d-]++$#u',
        ], $domain) && $this->checkPunycodeDecode($domain);
    }

    function domainIPv6(string $domain): bool
    {
        if (
            !\str_starts_with($domain, '[')
            ||
            !\str_ends_with($domain, ']')
        ) {
            return false;
        }

        return Str::isMatch('#^\[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]$#u', $domain);
    }

    function normalizeProtocol(string $protocol): string
    {
        return Str::lower($protocol);
    }

    function normalizeDomain(string $domain): string
    {
        $d = $domain;
        // "www.world" | "www.wtf" | "www.xn--8y0a063a"
        if (\substr_count($d, '.') > 1) {
            $d = Str::removeWWW($d);
        }
        return Str::lower($d);
    }

    // ------------------------------------------------------------------
    // 
    // ------------------------------------------------------------------

    protected function checkPunycodeDecode(string $domain): bool
    {
        try {
            $this->punycode->decode($domain);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function checkPunycodeEncode(string $domain): bool
    {
        try {
            $this->punycode->encode($domain);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
