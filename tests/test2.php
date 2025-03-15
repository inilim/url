<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Inilim\Dump\Dump;

Dump::init();



$patterns = [
    // '#\p{Common}#',
    '#\p{Arabic}#ui',
    '#\p{Armenian}#ui',
    '#\p{Bengali}#ui',
    '#\p{Bopomofo}#ui',
    '#\p{Braille}#ui',
    '#\p{Buhid}#ui',
    '#\p{Canadian_Aboriginal}#ui',
    '#\p{Cherokee}#ui',
    '#\p{Cyrillic}#ui',
    '#\p{Devanagari}#ui',
    '#\p{Ethiopic}#ui',
    '#\p{Georgian}#ui',
    '#\p{Greek}#ui',
    '#\p{Gujarati}#ui',
    '#\p{Gurmukhi}#ui',
    '#\p{Han}#ui',
    '#\p{Hangul}#ui',
    '#\p{Hanunoo}#ui',
    '#\p{Hebrew}#ui',
    '#\p{Hiragana}#ui',
    '#\p{Inherited}#ui',
    '#\p{Kannada}#ui',
    '#\p{Katakana}#ui',
    '#\p{Khmer}#ui',
    '#\p{Lao}#ui',
    '#\p{Latin}#ui',
    '#\p{Limbu}#ui',
    '#\p{Malayalam}#ui',
    '#\p{Mongolian}#ui',
    '#\p{Myanmar}#ui',
    '#\p{Ogham}#ui',
    '#\p{Oriya}#ui',
    '#\p{Runic}#ui',
    '#\p{Sinhala}#ui',
    '#\p{Syriac}#ui',
    '#\p{Tagalog}#ui',
    '#\p{Tagbanwa}#ui',
    '#\p{TaiLe}#ui',
    '#\p{Tamil}#ui',
    '#\p{Telugu}#ui',
    '#\p{Thaana}#ui',
    '#\p{Thai}#ui',
    '#\p{Tibetan}#ui',
    '#\p{Yi}#ui',
];




// dde(\_str()->isMatch('#\pL#', '񺄖'));
// de();

$skip = [];
$chars = [];
for ($i = 0; $i <= 500_000; $i++) {
    $s = \mb_chr($i, 'UTF-8');

    // if (\_str()->isMatch($patterns, $s)) {
    if (\_str()->isMatch('#\p{L}#ui', $s)) {
        $chars[$i] = \sprintf('"%s" - "%s"', $s, \urlencode($s));
    } else {
        $skip[$i] = \sprintf('"%s" - "%s"', $s, \urlencode($s));
    }
}

ob_start();
d([
    'count_$chars' => sizeof($chars),
    '$chars' => $chars,

    'count_$skip' => sizeof($skip),
    '$skip' => $skip,
]);
$a = ob_get_clean();

file_put_contents('test.txt', $a);
