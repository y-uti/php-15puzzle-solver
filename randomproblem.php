<?php

function main()
{
    $problem = range(0, 15);
    shuffle($problem);

    if (!isSolvable($problem)) {
        $problem = flipLR($problem);
    }

    printProblem($problem);
}

function isSolvable(array $problem)
{
    $i = 0;
    while (!is_null($n = array_shift($problem))) {
        if ($n > 0) {
            $i += count(array_filter($problem, function ($m) use ($n) {
                return $m != 0 && $m < $n;
            }));
        } else {
            $i += intval(count($problem) / 4);
        }
    }

    return $i % 2 == 0;
}

function flipLR(array $problem)
{
    return array_map(function ($i) use ($problem) {
        return $problem[$i];
    }, [3, 2, 1, 0, 7, 6, 5, 4, 11, 10, 9, 8, 15, 14, 13, 12]);
}

function printProblem(array $problem)
{
    foreach ($problem as $i => $n) {
        printf("%s%s", $n ?: '*', $i % 4 < 3 ? ' ' : "\n");
    }
}

main();
