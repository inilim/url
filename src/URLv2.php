<?php

namespace Inilim\URL;

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
        $parse = $this->parseUrl($url);

        if ($parse['count_elements'] === 0) {
            throw new \Exception('не url');
        } elseif ($parse['count_elements'] === 1) {
            if ($parse['path'] !== null) {
                // 
            } elseif ($parse['host'] !== null) {
                return $this->onlyHost($parse['host']);
            } elseif ($parse['anchor'] !== null) {
                return true;
            } elseif ($parse['scheme'] !== null) {
                return false;
            }
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
            \_str()->isMatch('#[^\p{Latin}\.\d\-]#', $host)
            ||
            \_str()->startsWith($host, ['-', '.'])
            ||
            \_str()->endsWith($host, ['-', '.'])
            ||
            \_str()->contains($host, ['-.', '.-', '..'])
        ) {
            return false;
        }

        return \_str()->isMatch('#^([a-z\d\-]++\.)+[a-z]++$#', $host);
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
            $host = \_str()->removeWWW($host);
        }
        return \_str()->lower($host);
    }



    /**
     * @return string
     */
    function normalizeScheme(string $scheme)
    {
        return \_str()->lower($scheme);
    }

    /**
     * new parse_url
     * @return array{count_elements:int,raw:string,scheme:null|string,host:null|string,port:null|int,login:null|string,password:null|string,path:null|string,query:null|string,anchor:null|string}
     */
    static function parseUrl(string $url)
    {
        $r = \parse_url($url, -1);
        if (!\is_array($r)) {
            $r = [];
        }

        return [
            'count_elements' => \sizeof($r),
            'raw'            => $url,
            'scheme'         => $r['scheme']   ?? null,
            'login'          => $r['user']     ?? null,
            'password'       => $r['pass']     ?? null,
            'host'           => $r['host']     ?? null,
            'port'           => $r['port']     ?? null,
            'path'           => $r['path']     ?? null,
            'query'          => $r['query']    ?? null,
            'anchor'         => $r['fragment'] ?? null,
        ];
    }
}
