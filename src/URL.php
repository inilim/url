<?php

namespace Inilim\URL;

use Inilim\PunyCode\Punycode;

class URL
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

        $details = \_str()->parseURL($url);

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

        $path = \_str()->trim(
            \_str()->trim($path),
            '/'
        );

        if ($path === '') {
            return true;
        }

        if (\_str()->isMatch('#[^\p{Latin}\/\d_-]#iu', $path)) {
            return false;
        }
    }

    function protocol(string $protocol): bool
    {
        $protocol = $this->normalizeProtocol($protocol);
        return \str_contains(
            \_str()->wrap(\_data()->URLProtocolsAsString('|'), '|'),
            \_str()->wrap($protocol, '|')
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
            \_str()->length($domain) > self::LEN_MAX_DOMAIN
            ||
            // INFO 
            \_str()->isMatch('#[^\pL\.\[\]\:\d\-]#u', $domain)
            ||
            \_str()->startsWith($domain, ['-', '.'])
            ||
            \_str()->endsWith($domain, ['-', '.'])
            ||
            // "custom-.ws" < почему то браузер открывает именно с доменом "ws"
            //                          "-."
            \_str()->contains($domain, ['-.', '.-', '..'])
        ) {
            return false;
        }

        // INFO IPv6 +
        if (\_str()->contains($domain, ['[', ']'])) {
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
        elseif (\_str()->isMatch('#\.\d{1,3}\.#u', $domain) && !\_str()->isMatch('#\pL#u', $domain)) {
            return $this->domainIP($domain);
        }
        // INFO IDN - это доменные имена, содержащие символы национальных алфавитов
        elseif (\_str()->isMatch('#[^\p{Latin}\d\-\.]#u', $domain)) {
            return $this->domainIDN($domain);
        }
        // INFO классический домен
        else {
            return $this->domainClassic($domain);
        }
    }

    function domainClassic(string $domain): bool
    {
        return \_str()->isMatch('#^([a-z\d\-]++\.)+[a-z]++$#u', $domain);
    }

    function domainIDN(string $domain): bool
    {
        // (?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++
        if (\_str()->isMatch('#\p{Latin}#u', $domain)) {
            return false;
        }
        // TODO проверить на наличие двух и более разных алфавитов
        return \_str()->isMatch('#^([\pL\d\-]++\.)+[\pL]++$#u', $domain);
        // return \_str()->isMatch('#^([\pL\d\-]++\.)+[\pL\d]++$#u', $domain);
    }

    function domainIP(string $domain): bool
    {
        return \_str()->isMatch('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#u', $domain);
    }

    function domainPunycode(string $domain): bool
    {
        // (xn--[a-z\d-]++\.)*+xn--[a-z\d-]++

        return \_str()->isMatch([
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

        return \_str()->isMatch('#^\[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]$#u', $domain);
    }

    function normalizeProtocol(string $protocol): string
    {
        return \_str()->lower($protocol);
    }

    function normalizeDomain(string $domain): string
    {
        $d = $domain;
        // "www.world" | "www.wtf" | "www.xn--8y0a063a"
        if (\substr_count($d, '.') > 1) {
            $d = \_str()->removeWWW($d);
        }
        return \_str()->lower($d);
    }

    // ------------------------------------------------------------------
    // 
    // ------------------------------------------------------------------

    protected function checkPunycodeDecode(string $domain): bool
    {
        try {
            $r = $this->punycode->decode($domain);
            de($r);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
