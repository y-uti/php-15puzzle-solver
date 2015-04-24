<?php

function main()
{
    $sequence = range(0, 15);
    shuffle($sequence);

    $board = toBoard($sequence);

    if (!isSolvable($sequence)) {
        $board = array_map('array_reverse', $board);
    }

    printBoard($board);
}

function isSolvable(array $sequence)
{
    $n = count($sequence);

    $sum = 0;
    for ($i = 0; $i < $n; ++$i) {
        for ($j = $i + 1; $j < $n; ++$j) {
            if ($sequence[$j] < $sequence[$i]) {
                ++$sum;
            }
        }
    }

    $rownum = intval((array_search(0, $sequence) - 1) / 4);
    $sum += $rownum;

    return $sum % 2 == 0;
}

function toBoard(array $sequence)
{
    return array_map(function ($i) use ($sequence) {
        return array_slice($sequence, $i * 4, 4);
    }, range(0, 3));
}

function flipLR(array $board)
{
    return array_map('array_reverse', $board);
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

function printBoard(array $board)
{
    foreach ($board as $row) {
        echo implode(' ', array_map(function ($n) {
            return $n == 0 ? '*' : $n;
        }, $row)) . "\n";
    }
}

main();
