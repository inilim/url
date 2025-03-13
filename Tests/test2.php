<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Inilim\Dump\Dump;

Dump::init();



$patterns = [
    // '#\p{Common}#',
    '#\p{Arabic}#',
    '#\p{Armenian}#',
    '#\p{Bengali}#',
    '#\p{Bopomofo}#',
    '#\p{Braille}#',
    '#\p{Buhid}#',
    '#\p{Canadian_Aboriginal}#',
    '#\p{Cherokee}#',
    '#\p{Cyrillic}#',
    '#\p{Devanagari}#',
    '#\p{Ethiopic}#',
    '#\p{Georgian}#',
    '#\p{Greek}#',
    '#\p{Gujarati}#',
    '#\p{Gurmukhi}#',
    '#\p{Han}#',
    '#\p{Hangul}#',
    '#\p{Hanunoo}#',
    '#\p{Hebrew}#',
    '#\p{Hiragana}#',
    '#\p{Inherited}#',
    '#\p{Kannada}#',
    '#\p{Katakana}#',
    '#\p{Khmer}#',
    '#\p{Lao}#',
    '#\p{Latin}#',
    '#\p{Limbu}#',
    '#\p{Malayalam}#',
    '#\p{Mongolian}#',
    '#\p{Myanmar}#',
    '#\p{Ogham}#',
    '#\p{Oriya}#',
    '#\p{Runic}#',
    '#\p{Sinhala}#',
    '#\p{Syriac}#',
    '#\p{Tagalog}#',
    '#\p{Tagbanwa}#',
    '#\p{TaiLe}#',
    '#\p{Tamil}#',
    '#\p{Telugu}#',
    '#\p{Thaana}#',
    '#\p{Thai}#',
    '#\p{Tibetan}#',
    '#\p{Yi}#',
];




// dde(\_str()->isMatch('#\pL#', '񺄖'));
// de();

for ($i = 0; $i <= 500_000; $i++) {
    $s = \mb_chr($i, 'UTF-8');

    if (\_str()->isMatch($patterns, $s)) {
        echo $i . ' ' . $s . PHP_EOL;
    }
}
