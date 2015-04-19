<?php

function main()
{
    $result = str_repeat(' ', 8 * 7 * 6 * 5 * 4 * 3 * 2 * 1);
    $result[0] = 'S';
    $board = array_fill(0, 4, array_fill(0, 4, -1));

    $queue = [0];
    while ($queue) {
        $encnum = array_shift($queue);
        decode($encnum, $board);
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

function test()
{
    $board = [
        [1, 2, 3, 4],
        [5, 6, 7, 8],
        [10, 14, 13, 9],
        [15, 0, 12, 11],
    ];

    $encnum = encode($board);
    echo $encnum . "\n";

    $board2 = array_fill(0, 4, array_fill(0, 4, 0));
    decode($encnum, $board2);

    echo json_encode($board2) . "\n";
}

function encode($board)
{
    $encnum = 0;
    $numbers = [9, 10, 11, 12, 13, 14, 15, 0];
    $scale = 1;
    foreach (range(2, 3) as $r) {
        foreach (range(0, 3) as $c) {
            $n = $board[$r][$c];
            $i = array_search($n, $numbers);
            $encnum += $i * $scale;
            $scale *= count($numbers);
            array_splice($numbers, $i, 1);
        }
    }
    return $encnum;
}

function decode($encnum, &$board)
{
    $numbers = [9, 10, 11, 12, 13, 14, 15, 0];
    foreach (range(2, 3) as $r) {
        foreach (range(0, 3) as $c) {
            $i = $encnum % count($numbers);
            $n = $numbers[$i];
            $board[$r][$c] = $n;
            $encnum = ($encnum - $i) / count($numbers);
            array_splice($numbers, $i, 1);
        }
    }
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
