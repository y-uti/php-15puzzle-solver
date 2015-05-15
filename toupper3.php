<?php

require_once __DIR__ . '/gendata.php';

function main()
{
    $numbers = [1, 2, 3, 4];
    $row = 0;
    $ignoreOrder = true;

    $result = str_repeat(' ', 16 * 15 * 14 * 13 * 12);

    $queue = [];
    for ($i = 0; $i < strlen($result); ++$i) {
        $encnum = encode(decode($i, $numbers, $row), $numbers, $row, $ignoreOrder);
        if (isSolved($encnum)) {
            $result[$encnum] = 'S';
            $queue[$encnum] = true;
        }
    }
    $queue = array_keys($queue);

    $result = findAllPath($numbers, $row, $ignoreOrder, $queue, $result);

    echo base64_encode(gzencode(packString($result))) . "\n";
}

function isSolved($encnum)
{
    foreach ([16, 15, 14, 13, 12] as $k) {
        if ($encnum % $k >= $k - 4) {
            return false;
        }
        $encnum = intval($encnum / $k);
    }
    return true;
}

main();
