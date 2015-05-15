<?php

function locationOf($n, $board)
{
    foreach ($board as $y => $r) {
        foreach ($r as $x => $i) {
            if ($i == $n) {
                return array($x, $y);
            }
        }
    }
}

function step($p, $d, $board)
{
    list($x, $y) = $p;
    list($dx, $dy) = $d;
    $n = $board[$y + $dy][$x + $dx];
    $board[$y][$x] = $n;
    $board[$y + $dy][$x + $dx] = 0;

    return $board;
}

function packString($str)
{
    $packed = str_repeat(chr(0), strlen($str) / 4);
    for ($i = 0; $i < strlen($str); $i += 4) {
        $n0 = strnum($str[$i + 0]);
        $n1 = strnum($str[$i + 1]);
        $n2 = strnum($str[$i + 2]);
        $n3 = strnum($str[$i + 3]);
        $n = $n0 + $n1 * 4 + $n2 * 16 + $n3 * 64;
        $packed[$i / 4] = chr($n);
    }

    return $packed;
}

function strnum($str)
{
    $table = ['U', 'L', 'R', 'D'];
    $n = array_search($str, $table);
    return $n !== false ? $n : 0;
}
