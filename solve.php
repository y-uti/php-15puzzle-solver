<?php

function main()
{
    // $board = [
    //     [1, 2, 3, 4],
    //     [5, 6, 7, 8],
    //     [13, 10, 11, 12],
    //     [9, 0, 14, 15],
    //     ];
    $board = readBoard();

    upperTwoRows($board);
    lowerTwoRows($board);
    printBoard($board);
}

function readBoard()
{
    $board = [];
    $lines = file('php://stdin', FILE_IGNORE_NEW_LINES);
    foreach ($lines as $l) {
        $b = explode(' ', $l);
        foreach ($b as &$e) {
            if ($e == '*') {
                $e == 0;
            } else {
                $e = intval($e);
            }
        }
        $board[] = $b;
    }
    return $board;
}

function upperTwoRows(&$board)
{
    moveTo(1, 0, 0, $board);
    moveTo(2, 1, 0, $board);
    moveTo(3, 2, 0, $board);
    fixRow(0, $board);

    moveTo(5, 0, 1, $board);
    moveTo(6, 1, 1, $board);
    moveTo(7, 2, 1, $board);
    fixRow(1, $board);
}

function lowerTwoRows(&$board)
{
    if (!($board[2][0] == 9 && $board[3][0] == 13)) {
        lowerMoveTo(13, 0, 2, $board);
        fixColumn(0, $board);
    }

    if (!($board[2][1] == 10 && $board[3][1] == 14)) {
        lowerMoveTo(14, 1, 2, $board);
        fixColumn(1, $board);
    }

    lowerMoveTo(11, 2, 2, $board);
    moveSpaceTo(3, 3, $board, buildWallsUpTo(11, $board));
}

function fixRow($row, &$board)
{
    list($sx, $sy) = locationOf(0, $board);
    if ($sx == 3 && $sy == $row) {
        step($sx, $sy, [0, 1], $board);
    }
    $n = ($row + 1) * 4;
    if ($board[$row][3] != $n) {
        moveTo($n, 3, $row + 2, $board);
        moveSpaceTo(3, $row, $board, buildWallsUpTo($n, $board));
        list($sx, $sy) = [3, $row];
        $moves = [
            [-1, 0],
            [0, 1],
            [1, 0],
            [0, 1],
            [-1, 0],
            [0, -1],
            [0, -1],
            [1, 0],
            [0, 1],
        ];
        foreach ($moves as $m) {
            step($sx, $sy, $m, $board);
        }
    }
    printBoard($board);
}

function fixColumn($col, &$board)
{
    $n = $col + 9;
    list($x, $y) = locationOf($n, $board);
    if ($x != $col + 1 || $y != 2) {
        list($sx, $sy) = locationOf(0, $board);
        if ($sx == $col && $sy == 3) {
            step($sx, $sy, [1, 0], $board);
        } else {
            moveSpaceTo($col + 1, 3, $board, buildWallsUpTo($n, $board));
        }
        if ($board[3][$col] == $n) {
            list($sx, $sy) = [$col + 1, 3];
            $moves = [
                [-1, 0], [0, -1], [1, 0], [0, 1], [1, 0], [0, -1], [-1, 0],
                [-1, 0], [0, 1], [1, 0], [0, -1], [1, 0],
            ];
            foreach ($moves as $m) {
                step($sx, $sy, $m, $board);
                printBoard($board);
            }
        } else {
            moveTo($n, $col + 1, 2, $board);
        }
    }
    moveSpaceTo($col, 3, $board, buildWallsUpTo($n, $board));
    list($sx, $sy) = [$col, 3];
    step($sx, $sy, [0, -1], $board);
    step($sx, $sy, [1, 0], $board);
    printBoard($board);
}

function moveTo($n, $tx, $ty, &$board)
{
    list($x, $y) = locationOf($n, $board);
    while ($x != $tx) {
        $dx = $x < $tx ? 1 : -1;
        $sx = $x + $dx;
        moveSpaceTo($sx, $y, $board, buildWallsUpTo($n, $board));
        step($sx, $y, [-$dx, 0], $board);
        $x += $dx;
    }
    while ($y != $ty) {
        $dy = $y < $ty ? 1 : -1;
        $sy = $y + $dy;
        moveSpaceTo($x, $sy, $board, buildWallsUpTo($n, $board));
        step($x, $sy, [0, -$dy], $board);
        $y += $dy;
    }
    printBoard($board);
}

function lowerMoveTo($n, $tx, $ty, &$board)
{
    list($x, $y) = locationOf($n, $board);
    while ($x > $tx) {
        $sx = $x - 1;
        moveSpaceTo($sx, $y, $board, buildWallsEqualsTo($n, $tx, $board));
        step($sx, $y, [1, 0], $board);
        --$x;
    }
    while ($y > $ty) {
        $sy = $y - 1;
        moveSpaceTo($x, $sy, $board, buildWallsEqualsTo($n, $tx, $board));
        step($x, $sy, [0, 1], $board);
        --$y;
    }
    printBoard($board);
}

function buildWallsUpTo($n, $board)
{
    $walls = array_fill(0, 4, array_fill(0, 4, 0));
    for ($y = 0; $y <= 3; ++$y) {
        for ($x = 0; $x <= 3; ++$x) {
            if ($board[$y][$x] != 0 && $board[$y][$x] <= $n) {
                $walls[$y][$x] = 1;
            }
        }
    }

    return $walls;
}

function buildWallsEqualsTo($n, $tx, $board)
{
    $walls = array_fill(0, 4, array_fill(0, 4, 0));
    for ($y = 0; $y <= 3; ++$y) {
        for ($x = 0; $x <= 3; ++$x) {
            if ($x < $tx || $y < 2 || $board[$y][$x] == $n) {
                $walls[$y][$x] = 1;
            }
        }
    }

    return $walls;
}

function moveSpaceTo($tx, $ty, &$board, $walls)
{
    list($sx, $sy) = locationOf(0, $board);

    $queue = [[$tx, $ty]];
    while ($walls[$sy][$sx] == 0) {
        list($x, $y) = array_shift($queue);
        if ($x < 3 && $walls[$y][$x + 1] == 0) {
            $walls[$y][$x + 1] = [-1, 0];
            $queue[] = [$x + 1, $y];
        }
        if ($x > 0 && $walls[$y][$x - 1] == 0) {
            $walls[$y][$x - 1] = [1, 0];
            $queue[] = [$x - 1, $y];
        }
        if ($y < 3 && $walls[$y + 1][$x] == 0) {
            $walls[$y + 1][$x] = [0, -1];
            $queue[] = [$x, $y + 1];
        }
        if ($y > 0 && $walls[$y - 1][$x] == 0) {
            $walls[$y - 1][$x] = [0, 1];
            $queue[] = [$x, $y - 1];
        }
    }

    while ($sx != $tx || $sy != $ty) {
        step($sx, $sy, $walls[$sy][$sx], $board);
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

function step(&$x, &$y, $d, &$board)
{
    list($dx, $dy) = $d;
    $n = $board[$y + $dy][$x + $dx];
    $board[$y][$x] = $n;
    $board[$y + $dy][$x + $dx] = 0;

    $x += $dx;
    $y += $dy;
    // echo $n . "\n";
}

function printBoard($board)
{
    foreach ($board as $row) {
        foreach ($row as $n) {
            if ($n > 0) {
                printf("%2d ", $n);
            } else {
                echo "   ";
            }
        }
        echo "\n";
    }
    echo "\n";
}

main();
