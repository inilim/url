<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Inilim\URL\URL;
use Inilim\Tool\Str;
use Inilim\Dump\Dump;
use Inilim\URL\URLv2;
use Inilim\IPDO\IPDOSQLite;


Dump::init();

d(Str::parseUrl('####!!!123123'));


de();
$urls = \file(__DIR__ . '/good_with_path_en.txt');

foreach ($urls as $url) {
    $url = \trim($url);
    if ($url === '') continue;
    d(Str::parseUrl($url));
}

de();
$sqlite = new IPDOSQLite('D:\projects\txt_to_sqlite\domains.sqlite');
$url = new URL;
// de(52.94285714285714 % 12);
$res = $url->punycode->encode('址dа.com');

// $res = $url->domainPunycode('xn-------43dcbbaejg4abf1alafg6bji4blgc8dql5b7b1co34a.com');

dde($res);
// 252_757_000

// $d = '0--0.xn--p1ai';
// $d = '000c.xn--ses554g';
// $d = 'custom-.ws';
// $d = '0--0----------------------------------------------------------0.com';

// de(_str()->isUrl2($d));
// de($url->domain($d));

// de();
$offset = 55_512_100;
while (true) {
    $domains = $sqlite->exec('SELECT * FROM domain WHERE id > {offset} LIMIT 100', ['offset' => $offset], 2);

    if (!$domains) break;

    foreach ($domains as $domain) {
        if (!$url->domain($domain['domain'])) {
            $sqlite->exec('UPDATE domain SET valid = 0 WHERE id = {id}', ['id' => $domain['id']]);
            d($offset, $domain);
        }
    }
    $offset += 100;
}
