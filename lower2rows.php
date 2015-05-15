<?php

require_once __DIR__ . '/gendata.php';

function main()
{
    $result = str_repeat(' ', 8 * 7 * 6 * 5 * 4 * 3 * 2 * 1);
    $result[0] = 'S';
    $queue = [0];

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
    if (0 <= $x + $dx && $x + $dx <= 3 && 2 <= $y + $dy && $y + $dy <= 3) {
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
    $numbers = [9, 10, 11, 12, 13, 14, 15, 0];

    $encnum = 0;
    $cells = range(8, 15);
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
    $numbers = [9, 10, 11, 12, 13, 14, 15, 0];

    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $cells = range(8, 15);
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

main();
