<?php

require_once __DIR__ . '/gendata.php';

function main()
{
    $numbers = [9, 10, 11, 12, 13, 14, 15];
    $row = 2;
    $ignoreOrder = false;

    $result = str_repeat(' ', 8 * 7 * 6 * 5 * 4 * 3 * 2 * 1);
    $result[0] = 'S';
    $queue = [0];

    $result = findAllPath($numbers, $row, $ignoreOrder, $queue, $result);

    echo base64_encode(gzencode(packString($result))) . "\n";
}

main();
