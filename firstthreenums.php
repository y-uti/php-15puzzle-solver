<?php

require_once __DIR__ . '/gendata.php';

function main()
{
    $numbers = [1, 2, 3];
    $row = 0;
    $ignoreOrder = false;

    $result = str_repeat(' ', 16 * 15 * 14 * 13);

    $queue = [];
    $board = array_fill(0, 4, array_fill(0, 4, -1));
    $board[0] = [1, 2, 3, 0];
    $encnum = encode($board, $numbers, $row, $ignoreOrder);
    $result[$encnum] = 'S';
    $queue[] = $encnum;
    $board[0][3] = -1;
    foreach (range(1, 3) as $sy) {
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
