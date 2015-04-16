<?php

function main()
{
    $board = readBoard();

    $resultUD = solveUD($board);
    $resultLR = solveLR($board);
    // echo 'UD=' . count($resultUD) . ', LR=' . count($resultLR) . "\n";

    $result = count($resultUD) < count($resultLR) ? $resultUD : $resultLR;
    writeResult($result);
}

function solveUD($board)
{
    $result = [];
    $result = array_merge($result, upperTwoRows($board));
    // printBoard($board);
    $result = array_merge($result, lowerTwoRows($board));
    // printBoard($board);
    return $result;
}

function solveLR($board)
{
    $board = array_map(null, $board[0], $board[1], $board[2], $board[3]);
    return solveUD($board);
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
    $result = [];
    moveTo(1, 0, 0, $board, $result);
    moveTo(2, 1, 0, $board, $result);
    moveTo(3, 2, 0, $board, $result);
    fixRow(0, $board, $result);

    moveTo(5, 0, 1, $board, $result);
    moveTo(6, 1, 1, $board, $result);
    moveTo(7, 2, 1, $board, $result);
    fixRow(1, $board, $result);

    return $result;
}

function lowerTwoRows(&$board)
{
    $result = [];
    if (!($board[2][0] == 9 && $board[3][0] == 13)) {
        lowerMoveTo(13, 0, 2, $board, $result);
        fixColumn(0, $board, $result);
    }

    if (!($board[2][1] == 10 && $board[3][1] == 14)) {
        lowerMoveTo(14, 1, 2, $board, $result);
        fixColumn(1, $board, $result);
    }

    lowerMoveTo(11, 2, 2, $board, $result);
    moveSpaceTo(3, 3, $board, buildWallsUpTo(11, $board), $result);

    return $result;
}

function fixRow($row, &$board, &$result)
{
    list($sx, $sy) = locationOf(0, $board);
    if ($sx == 3 && $sy == $row) {
        step($sx, $sy, [0, 1], $board, $result);
    }
    $n = ($row + 1) * 4;
    if ($board[$row][3] != $n) {
        moveTo($n, 3, $row + 2, $board, $result);
        moveSpaceTo(3, $row, $board, buildWallsUpTo($n, $board), $result);
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
            step($sx, $sy, $m, $board, $result);
        }
    }
}

function fixColumn($col, &$board, &$result)
{
    $n = $col + 9;
    list($x, $y) = locationOf($n, $board);
    if ($x != $col + 1 || $y != 2) {
        list($sx, $sy) = locationOf(0, $board);
        if ($sx == $col && $sy == 3) {
            step($sx, $sy, [1, 0], $board, $result);
        } else {
            moveSpaceTo($col + 1, 3, $board, buildWallsUpTo($n, $board), $result);
        }
        if ($board[3][$col] == $n) {
            list($sx, $sy) = [$col + 1, 3];
            $moves = [
                [-1, 0], [0, -1], [1, 0], [0, 1], [1, 0], [0, -1], [-1, 0],
                [-1, 0], [0, 1], [1, 0], [0, -1], [1, 0],
            ];
            foreach ($moves as $m) {
                step($sx, $sy, $m, $board, $result);
            }
        } else {
            moveTo($n, $col + 1, 2, $board, $result);
        }
    }
    moveSpaceTo($col, 3, $board, buildWallsUpTo($n, $board), $result);
    list($sx, $sy) = [$col, 3];
    step($sx, $sy, [0, -1], $board, $result);
    step($sx, $sy, [1, 0], $board, $result);
}

function moveTo($n, $tx, $ty, &$board, &$result)
{
    list($x, $y) = locationOf($n, $board);
    while ($x != $tx) {
        $dx = $x < $tx ? 1 : -1;
        $sx = $x + $dx;
        moveSpaceTo($sx, $y, $board, buildWallsUpTo($n, $board), $result);
        step($sx, $y, [-$dx, 0], $board, $result);
        $x += $dx;
    }
    while ($y != $ty) {
        $dy = $y < $ty ? 1 : -1;
        $sy = $y + $dy;
        moveSpaceTo($x, $sy, $board, buildWallsUpTo($n, $board), $result);
        step($x, $sy, [0, -$dy], $board, $result);
        $y += $dy;
    }
}

function lowerMoveTo($n, $tx, $ty, &$board, &$result)
{
    list($x, $y) = locationOf($n, $board);
    while ($x > $tx) {
        $sx = $x - 1;
        moveSpaceTo($sx, $y, $board, buildWallsEqualsTo($n, $tx, $board), $result);
        step($sx, $y, [1, 0], $board, $result);
        --$x;
    }
    while ($y > $ty) {
        $sy = $y - 1;
        moveSpaceTo($x, $sy, $board, buildWallsEqualsTo($n, $tx, $board), $result);
        step($x, $sy, [0, 1], $board, $result);
        --$y;
    }
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

function moveSpaceTo($tx, $ty, &$board, $walls, &$result)
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
        step($sx, $sy, $walls[$sy][$sx], $board, $result);
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

function step(&$x, &$y, $d, &$board, &$result)
{
    list($dx, $dy) = $d;
    $n = $board[$y + $dy][$x + $dx];
    $board[$y][$x] = $n;
    $board[$y + $dy][$x + $dx] = 0;

    $x += $dx;
    $y += $dy;
    $result[] = $n;
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

function writeResult($result)
{
    foreach ($result as $n) {
        echo $n . "\n";
    }
}

main();
