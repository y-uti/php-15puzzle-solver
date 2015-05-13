<?php

function main()
{
    $board = readBoard();

    $resultUD = solveUD($board);
    $resultLR = solveLR($board);
    $result = count($resultUD) < count($resultLR) ? $resultUD : $resultLR;

    writeResult($result);
}

function readBoard()
{
    $board = [];
    $lines = file('php://stdin', FILE_IGNORE_NEW_LINES);
    foreach ($lines as $l) {
        $board[] = array_map(function ($e) {
            return $e == '*' ? 0 : intval($e);
        }, explode(' ', $l));
    }

    return $board;
}

function solveUD($board)
{
    return solve($board, goal());
}

function solveLR($board)
{
    return solve(transpose($board), transpose(goal()));
}

function solve($board, $goal)
{
    $strategyChain = [
        'optimalSolverForToUpperThreeRows',
        'optimalSolverForFirstRow',
        'optimalSolverForSecondRow',
        'optimalSolverForLowerTwoRows',
    ];

    $result = [];
    foreach ($strategyChain as $strategy) {
        list($r, $board) = $strategy($board, $goal);
        $result = array_merge($result, $r);
    }

    return $result;
}

//////////////////////////////////////////////////////////////////////

$dataForToUpperThreeRowsSolver = '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';
$dataForToUpperThreeRowsSolver = gzdecode(base64_decode($dataForToUpperThreeRowsSolver));
function optimalSolverForToUpperThreeRows($board, $goal)
{
    global $dataForToUpperThreeRowsSolver;

    $numbers = $goal[0];
    $goalFun = function ($encnum) {
        foreach ([16, 15, 14, 13, 12] as $k) {
            if ($encnum % $k >= $k - 4) {
                return false;
            }
            $encnum = intval($encnum / $k);
        }

        return true;
    };

    return optimalSolver(
        $board,
        $numbers,
        $dataForToUpperThreeRowsSolver,
        0,
        $goalFun,
        true
    );
}

function optimalSolverForFirstRow($board, $goal)
{
    list($result, $board) = optimalSolverForSecondRow(
        rotateRow($board, -1),
        rotateRow($goal, -1)
    );

    return [$result, rotateRow($board, 1)];
}

$dataForSecondRowSolver = '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';
$dataForSecondRowSolver = gzdecode(base64_decode($dataForSecondRowSolver));
function optimalSolverForSecondRow($board, $goal)
{
    global $dataForSecondRowSolver;

    $numbers = $goal[1];
    $goalFun = function ($encnum) {
        return $encnum % 11880 == 0;
    };

    return optimalSolver(
        $board,
        $numbers,
        $dataForSecondRowSolver,
        1,
        $goalFun
    );
}

$dataForLowerTwoRowsSolver = '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';
$dataForLowerTwoRowsSolver = gzdecode(base64_decode($dataForLowerTwoRowsSolver));
function optimalSolverForLowerTwoRows($board, $goal)
{
    global $dataForLowerTwoRowsSolver;

    $numbers = array_merge($goal[2], $goal[3]);
    $goalFun = function ($encnum) {
        return $encnum == 0;
    };

    return optimalSolver(
        $board,
        $numbers,
        $dataForLowerTwoRowsSolver,
        2,
        $goalFun
    );
}

function optimalSolver($board, $numbers, $data, $encRow, $goalFun, $ignoreOrder = false)
{
    $dirs = [[0, -1], [-1, 0], [1, 0], [0, 1]];

    $result = [];
    while (!$goalFun($encnum = encode($board, $numbers, $encRow, $ignoreOrder))) {
        list($x, $y) = locationOf(0, $board);
        $i = ord($data[intval($encnum / 4)]) / pow(4, $encnum % 4) % 4;
        list($n, $board) = step($x, $y, $dirs[$i][0], $dirs[$i][1], $board);
        $result[] = $n;
    }

    return [$result, $board];
}

function sortByCellNumber($numbers, $board)
{
    usort($numbers, function ($a, $b) use ($board) {
        return cellNumberOf($a, $board) - cellNumberOf($b, $board);
    });

    return $numbers;
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

function step($x, $y, $dx, $dy, $board)
{
    $n = $board[$y + $dy][$x + $dx];
    $board[$y][$x] = $n;
    $board[$y + $dy][$x + $dx] = 0;

    return [$n, $board];
}

function printBoard($board)
{
    foreach ($board as $row) {
        echo implode(' ', array_map(function ($n) {
            return sprintf('%2s', $n ?: ' ');
        }, $row)) . "\n";
    }
    echo "\n";
}

function writeResult($result)
{
    echo implode("\n", $result) . "\n";
}

function goal()
{
    return [
        [ 1,  2,  3,  4],
        [ 5,  6,  7,  8],
        [ 9, 10, 11, 12],
        [13, 14, 15,  0],
    ];
}

function transpose($board)
{
    return array_map(null, $board[0], $board[1], $board[2], $board[3]);
}

function rotateRow($board, $n)
{
    return array_map(function ($i) use ($board) {
        return $board[($i + 4) % 4];
    }, range($n, $n + 3));
}

main();
