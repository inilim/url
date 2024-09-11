<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/URL.php';

use Inilim\Dump\Dump;
use Inilim\IPDO\IPDOSQLite;
use Inilim\URL\URL;

Dump::init();

$sqlite = new IPDOSQLite('D:\projects\txt_to_sqlite\domains.sqlite', \_int(), \_arr());
$url = new URL;

$res = $url->domainPunycode('xn-------43dcbbaejg4abf1alafg6bji4blgc8dql5b7b1co34a.com');

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
    $domains = $sqlite->exec('SELECT * FROM domain WHERE id > :offset LIMIT 100', ['offset' => $offset], 2);

    if (!$domains) break;

    foreach ($domains as $domain) {
        if (!$url->domain($domain['domain'])) {
            $sqlite->exec('UPDATE domain SET valid = 0 WHERE id = :id', ['id' => $domain['id']]);
            d($offset, $domain);
        }
    }
    $offset += 100;
}
