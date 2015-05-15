<?php

function findAllPath($numbers, $row, $ignoreOrder, $queue, $result)
{
    $cnt = 0;
    while ($cnt < count($queue)) {
        $encnum = $queue[$cnt++];
        $board = decode($encnum, $numbers, $row);
        $p = locationOf(0, $board);
        tryNext($p, [0, -1], 'D', $board, $numbers, $row, $ignoreOrder, $queue, $result);
        tryNext($p, [-1, 0], 'R', $board, $numbers, $row, $ignoreOrder, $queue, $result);
        tryNext($p, [1, 0], 'L', $board, $numbers, $row, $ignoreOrder, $queue, $result);
        tryNext($p, [0, 1], 'U', $board, $numbers, $row, $ignoreOrder, $queue, $result);
    }

    return $result;
}

function tryNext($curr, $d, $c, $board, $numbers, $row, $ignoreOrder, &$queue, &$result)
{
    list($x, $y) = $curr;
    list($dx, $dy) = $d;
    if (0 <= $x + $dx && $x + $dx <= 3 && $row <= $y + $dy && $y + $dy <= 3) {
        $next = step($curr, $d, $board);
        $nenc = encode($next, $numbers, $row, $ignoreOrder);
        if ($result[$nenc] == ' ') {
            $result[$nenc] = $c;
            $queue[] = $nenc;
        }
    }
}

function encode($board, $numbers, $row, $ignoreOrder = false)
{
    $numbers = $ignoreOrder ? sortByCellNumber($numbers, $board) : $numbers;
    $numbers[] = 0;

    $cells = range($row * 4, 15);

    $encnum = 0;
    $scale = 1;
    foreach ($numbers as $n) {
        $c = cellNumberOf($n, $board);
        $i = array_search($c, $cells);
        $encnum += $i * $scale;
        $scale *= count($cells);
        array_splice($cells, $i, 1);
    }

    return $encnum;
}

function decode($encnum, $numbers, $row)
{
    $numbers[] = 0;
    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $cells = range($row * 4, 15);
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

function sortByCellNumber($numbers, $board)
{
    usort($numbers, function ($a, $b) use ($board) {
        return cellNumberOf($a, $board) - cellNumberOf($b, $board);
    });

    return $numbers;
}

function locationOf($n, $board)
{
    $cell = cellNumberOf($n, $board);
    $x = $cell % 4;
    $y = intval($cell / 4);

    return [$x, $y];
}

function cellNumberOf($n, $board)
{
    return array_search($n, array_reduce($board, 'array_merge', []));
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
