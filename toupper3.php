<?php

function main()
{
    $result = str_repeat(' ', 16 * 15 * 14 * 13 * 12);

    $queue = [];
    for ($i = 0; $i < strlen($result); ++$i) {
        $encnum = encode(decode($i));
        if (isSolved($encnum)) {
            $result[$encnum] = 'S';
            $queue[$encnum] = true;
        }
    }
    $queue = array_keys($queue);

    $cnt = 0;
    while ($cnt < count($queue)) {
        $encnum = $queue[$cnt];
        ++$cnt;
        $board = decode($encnum);
        $p = locationOf(0, $board);
        tryNext($p, [0, -1], 'D', $board, $queue, $result);
        tryNext($p, [-1, 0], 'R', $board, $queue, $result);
        tryNext($p, [1, 0], 'L', $board, $queue, $result);
        tryNext($p, [0, 1], 'U', $board, $queue, $result);
    }

    echo base64_encode(gzencode(packString($result))) . "\n";
}

function isSolved($encnum)
{
    $board = decode($encnum);
    foreach ([1, 2, 3, 4, 0] as $n) {
        list($x, $y) = locationOf($n, $board);
        if ($y == 3) {
            return false;
        }
    }
    return true;
}

function tryNext($curr, $d, $c, $board, &$queue, &$result)
{
    list($x, $y) = $curr;
    list($dx, $dy) = $d;
    if (0 <= $x + $dx && $x + $dx <= 3 && 0 <= $y + $dy && $y + $dy <= 3) {
        $next = step($curr, $d, $board);
        $nenc = encode($next);
        if ($result[$nenc] == ' ') {
            $result[$nenc] = $c;
            $queue[] = $nenc;
        }
    }
}

function encode($board)
{
    $cellnums = [];

    $numbers = [1, 2, 3, 4];
    foreach ($numbers as $n) {
        list($x, $y) = locationOf($n, $board);
        $cellnums[] = $y * 4 + $x;
    }
    sort($cellnums);

    list($x, $y) = locationOf(0, $board);
    $cellnums[] = $y * 4 + $x;

    $encnum = 0;
    $cells = range(0, 15);
    $scale = 1;
    foreach ($cellnums as $c) {
        $i = array_search($c, $cells);
        $encnum += $i * $scale;
        $scale *= count($cells);
        array_splice($cells, $i, 1);
    }

    return $encnum;
}

function decode($encnum)
{
    $numbers = [1, 2, 3, 4, 0];

    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $cells = range(0, 15);
    foreach ($numbers as $n) {
        $i = $encnum % count($cells);
        $c = $cells[$i];
        $x = $c % 4;
        $y = ($c - $x) / 4;
        $board[$y][$x] = $n;
        $encnum = ($encnum - $i) / count($cells);
        array_splice($cells, $i, 1);
    }

    return $board;
}

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

function printBoard($board)
{
    foreach ($board as $row) {
        foreach ($row as $n) {
            if ($n > 0) {
                printf("%2d ", $n);
            } elseif ($n == 0) {
                echo "   ";
            } else {
                echo " * ";
            }
        }
        echo "\n";
    }
    echo "\n";
}

main();
