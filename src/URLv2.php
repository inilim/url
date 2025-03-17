<?php

namespace Inilim\URL;

use Inilim\Tool\Str;

/**
 */
final class URLv2
{
    /**
     * @var \stdClass
     */
    protected $params = [
        'key' => null,
    ];

    /**
     * @param \Stringable|string $url
     */
    function __invoke($url)
    {
        $url   = (string)$url;
        $parse = Str::parseUrl($url);

        if ($parse['count_elements'] === 0) {
            throw new \Exception('не url');
        } elseif ($parse['host'] === null && $parse['path'] === null) {
        } elseif ($parse['count_elements'] === 1) {
            if ($parse['path'] !== null) {
                // 
            } elseif ($parse['host'] !== null) {
                return $this->onlyHost($parse['host']);
            } elseif ($parse['anchor'] !== null) {
                return true;
            } elseif ($parse['query'] !== null) {
                return true;
            }
            return false;
        } elseif ($parse['count_elements'] === 2) {
            // 
        }
    }

    /**
     * @return bool
     */
    function onlyHost(string $host)
    {
        $host = $this->normalizeHost($host);

        // ------------------------------------------------------------------
        // первичная проверка
        // ------------------------------------------------------------------
        if (
            !\str_contains($host, '.')
            ||
            // INFO 
            Str::isMatch('#[^a-z\.\d\-]#', $host)
            ||
            Str::startsWith($host, ['-', '.'])
            ||
            Str::endsWith($host, ['-', '.'])
            ||
            Str::contains($host, ['-.', '.-', '..'])
        ) {
            return false;
        }

        return Str::isMatch('#^([a-z\d\-]++\.)+[a-z]++$#', $host);
    }

    function onlyPath(string $path)
    {
        // 
    }

    /**
     * @return string
     */
    function normalizeHost(string $host)
    {
        // "www.world" | "www.wtf" | "www.xn--8y0a063a"
        if (\substr_count($host, '.') >= 2) {
            $host = Str::removeWWW($host);
        }
        return Str::lower($host);
    }



    /**
     * @return string
     */
    function normalizeScheme(string $scheme)
    {
        return Str::lower($scheme);
    }
}
