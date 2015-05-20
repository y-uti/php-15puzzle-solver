<?php

require_once __DIR__ . '/gendata.php';

function main()
{
    $numbers = [5, 6, 7];
    $row = 1;
    $ignoreOrder = false;

    $result = str_repeat(' ', 12 * 11 * 10 * 9);

    $queue = [];
    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $board[1] = [5, 6, 7, 0];
    $encnum = encode($board, $numbers, $row, $ignoreOrder);
    $result[$encnum] = 'S';
    $queue[] = $encnum;
    $board[1][3] = -1;
    foreach (range(2, 3) as $sy) {
        foreach (range(0, 3) as $sx) {
            $board[$sy][$sx] = 0;
            $encnum = encode($board, $numbers, $row, $ignoreOrder);
            $result[$encnum] = 'S';
            $queue[] = $encnum;
            $board[$sy][$sx] = -1;
        }
    }

    $result = findAllPath($numbers, $row, $ignoreOrder, $queue, $result);

    echo base64_encode(gzencode(packString($result))) . "\n";
}

main();
