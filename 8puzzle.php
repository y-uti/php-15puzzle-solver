<?php

function main()
{
    $xlim = 3;
    $ylim = 3;
    $board = array_fill(0, $ylim, array_fill(0, $xlim, -1));
    $result = str_repeat(' ', array_product(range(1, $xlim * $ylim)));
    $result[0] = 'S';
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

    echo $result . "\n";
}

function tryNext($curr, $d, $c, $board, &$queue, &$result)
{
    $xlim = count($board[0]);
    $ylim = count($board);

    list($nx, $ny) = [$curr[0] + $d[0], $curr[1] + $d[1]];
    if (0 <= $nx && $nx < $xlim && 0 <= $ny && $ny < $ylim) {
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
    $xlim = count($board[0]);
    $ylim = count($board);
    $numbers = range(1, $xlim * $ylim - 1);
    $numbers[] = 0;

    $encnum = 0;
    $scale = 1;
    foreach (range(0, $ylim - 1) as $r) {
        foreach (range(0, $xlim - 1) as $c) {
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
    $xlim = count($board[0]);
    $ylim = count($board);
    $numbers = range(1, $xlim * $ylim - 1);
    $numbers[] = 0;

    foreach (range(0, $ylim - 1) as $r) {
        foreach (range(0, $xlim - 1) as $c) {
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

main();
