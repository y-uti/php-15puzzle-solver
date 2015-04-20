<?php

function main()
{
    $result = str_repeat(' ', 12 * 11 * 10 * 9);

    $queue = [];
    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $board[1] = [5, 6, 7, 0];
    $encnum = encode($board);
    $result[$encnum] = 'S';
    $queue[] = $encnum;
    $board[1][3] = -1;
    foreach (range(2, 3) as $sy) {
        foreach (range(0, 3) as $sx) {
            $board[$sy][$sx] = 0;
            $encnum = encode($board);
            $result[$encnum] = 'S';
            $queue[] = $encnum;
            $board[$sy][$sx] = -1;
        }
    }

    while ($queue) {
        $encnum = array_shift($queue);
        $board = decode($encnum);
        $p = locationOf(0, $board);
        tryNext($p, [0, -1], 'D', $board, $queue, $result);
        tryNext($p, [-1, 0], 'R', $board, $queue, $result);
        tryNext($p, [1, 0], 'L', $board, $queue, $result);
        tryNext($p, [0, 1], 'U', $board, $queue, $result);
    }

    echo base64_encode(gzencode(packString($result))) . "\n";
}

function tryNext($curr, $d, $c, $board, &$queue, &$result)
{
    list($x, $y) = $curr;
    list($dx, $dy) = $d;
    if (0 <= $x + $dx && $x + $dx <= 3 && 1 <= $y + $dy && $y + $dy <= 3) {
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
    $numbers = [5, 6, 7, 0];

    $encnum = 0;
    $cells = range(4, 15);
    $scale = 1;
    foreach ($numbers as $n) {
        list($x, $y) = locationOf($n, $board);
        $c = $y * 4 + $x;
        $i = array_search($c, $cells);
        $encnum += $i * $scale;
        $scale *= count($cells);
        array_splice($cells, $i, 1);
    }

    return $encnum;
}

function decode($encnum)
{
    $numbers = [5, 6, 7, 0];

    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $cells = range(4, 15);
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

main();
