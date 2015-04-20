<?php

class PathNotFoundException extends Exception
{
}

function main()
{
    $board = readBoard();

    $resultUD = solveUD($board);
    $resultLR = solveLR($board);

    $result = count($resultUD) < count($resultLR) ? $resultUD : $resultLR;
    writeResult($result);
}

// function main()
// {
//     $board = randomBoard();
//     $resultUD = solveUD($board);
//     $resultLR = solveLR($board);
// }

// function randomBoard()
// {
//     $board = [
//         [1, 2, 3, 4],
//         [5, 6, 7, 8],
//         [9, 10, 11, 12],
//         [13, 14, 15, 0],
//     ];

//     for ($i = 0; $i < 1000; ++$i) {
//         list($x, $y) = locationOf(0, $board);
//         $cands = [];
//         if ($x != 0) {
//             $cands[] = [-1, 0];
//         }

//         if ($x != 3) {
//             $cands[] = [1, 0];
//         }

//         if ($y != 0) {
//             $cands[] = [0, -1];
//         }

//         if ($y != 3) {
//             $cands[] = [0, 1];
//         }

//         $r = [];
//         step($x, $y, $cands[rand() % count($cands)], $board, $r);
//     }

//     return $board;
// }

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

function solveUD($board)
{
    $goal = [
        [1, 2, 3, 4],
        [5, 6, 7, 8],
        [9, 10, 11, 12],
        [13, 14, 15, 0],
    ];
    return solve($board, $goal);
}

function solveLR($board)
{
    $board = array_map(null, $board[0], $board[1], $board[2], $board[3]);
    $goal = [
        [1, 5, 9, 13],
        [2, 6, 10, 14],
        [3, 7, 11, 15],
        [4, 8, 12, 0],
    ];
    return solve($board, $goal);
}

function solve($board, $goal)
{
    $strategies = [];

    $strategiesForUpperTwoRows = [
        'dirLRandPackR',
        'dirLR2',
        'dirLR3',
        'dirLR4',
    ];

    foreach (range(0, 1) as $y) {
        $strategies[$y] = [];
        foreach ($strategiesForUpperTwoRows as $s) {
            $strategies[$y][] = function ($b) use ($y, $s, $goal) {
                return call_user_func($s, $y, $b, $goal);
            };
            $strategies[$y][] = function ($b) use ($y, $s, $goal) {
                return flipStrategyDirection($s, $y, $b, $goal);
            };
        }
    }

    $strategies[1][] = function ($b) use ($goal) {
        return optimalSolverForSecondRow($b, $goal);
    };

    $strategies[] = [
        function ($b) use ($goal) {
            $result = lowerTwoRowsFast($b, $goal);
            return [$result, $b];
        },
    ];

    $best = false;
    foreach (cartesianProduct($strategies) as $chain) {
        $b = $board;
        $r = [];
        try {
            foreach ($chain as $s) {
                list($rnext, $b) = $s($b);
                $r = array_merge($r, $rnext);
            }
            if ($best === false || count($r) < count($best)) {
                $best = $r;
            }
        } catch (PathNotFoundException $e) {
        }
    }

    return $best;
}

//////////////////////////////////////////////////////////////////////

$dataForLowerTwoRowsSolver = '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';
$dataForLowerTwoRowsSolver = gzdecode(base64_decode($dataForLowerTwoRowsSolver));
function lowerTwoRowsFast(&$board, $goal)
{
    global $dataForLowerTwoRowsSolver;
    $dirs = [[0, -1], [-1, 0], [1, 0], [0, 1]];
    $numbers = array_merge($goal[2], $goal[3]);

    $result = [];
    $encnum = encode($board, $numbers);
    while ($encnum != 0) {
        list($x, $y) = locationOf(0, $board);
        $i = ord($dataForLowerTwoRowsSolver[intval($encnum / 4)]) / pow(4, $encnum % 4) % 4;
        step($x, $y, $dirs[$i], $board, $result);
        $encnum = encode($board, $numbers);
    }

    return $result;
}

function encode($board, $numbers)
{
    $encnum = 0;
    $scale = 1;
    foreach (range(2, 3) as $r) {
        foreach (range(0, 3) as $c) {
            $n = $board[$r][$c];
            $i = array_search($n, $numbers);
            $encnum += $i * $scale;
            $scale *= count($numbers);
            array_splice($numbers, $i, 1);
        }
    }
    return $encnum;
}

$dataForFirstThreeNumbersSolver = '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';
$dataForFirstThreeNumbersSolver = gzdecode(base64_decode($dataForFirstThreeNumbersSolver));
function optimalSolverForFirstThreeNumbers($board, $numbers)
{
    global $dataForFirstThreeNumbersSolver;
    $dirs = [[0, -1], [-1, 0], [1, 0], [0, 1]];
    $numbers[] = 0;

    $result = [];
    $encnum = encodeForFirstThreeNumbersSolver($board, $numbers);
    while ($encnum % 3360 != 0) {
        list($x, $y) = locationOf(0, $board);
        $i = ord($dataForFirstThreeNumbersSolver[intval($encnum / 4)]) / pow(4, $encnum % 4) % 4;
        step($x, $y, $dirs[$i], $board, $result);
        $encnum = encodeForFirstThreeNumbersSolver($board, $numbers);
    }

    return [$result, $board];
}

$dataForSecondThreeNumbersSolver = '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';
$dataForSecondThreeNumbersSolver = gzdecode(base64_decode($dataForSecondThreeNumbersSolver));
function optimalSolverForSecondThreeNumbers($board, $numbers)
{
    global $dataForSecondThreeNumbersSolver;
    $dirs = [[0, -1], [-1, 0], [1, 0], [0, 1]];
    $numbers[] = 0;

    $result = [];
    $encnum = encodeForSecondThreeNumbersSolver($board, $numbers);
    while ($encnum % 1320 != 0) {
        list($x, $y) = locationOf(0, $board);
        $i = ord($dataForSecondThreeNumbersSolver[intval($encnum / 4)]) / pow(4, $encnum % 4) % 4;
        step($x, $y, $dirs[$i], $board, $result);
        $encnum = encodeForSecondThreeNumbersSolver($board, $numbers);
    }

    return [$result, $board];
}

function encode2($board, $numbers, $cells)
{
    $encnum = 0;
    $scale = 1;
    foreach ($numbers as $n) {
        list($x, $y) = locationOf($n, $board);
        $c = $y * 4 + $x;
        $i = array_search($c, $cells);
        $encnum += $i * $scale;
        $scale *= count($cells);
        array_splice($cells, $i, 1);
    }

    return $encnum;
}

function encodeForFirstThreeNumbersSolver($board, $numbers)
{
    return encode2($board, $numbers, range(0, 15));
}

function encodeForSecondThreeNumbersSolver($board, $numbers)
{
    return encode2($board, $numbers, range(4, 15));
}

$dataForSecondRowSolver = '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';
$dataForSecondRowSolver = gzdecode(base64_decode($dataForSecondRowSolver));
function optimalSolverForSecondRow($board, $goal)
{
    global $dataForSecondRowSolver;
    $dirs = [[0, -1], [-1, 0], [1, 0], [0, 1]];
    $numbers = array_merge($goal[1], [0]);

    $result = [];
    $encnum = encodeForSecondRowSolver($board, $numbers);
    while ($encnum % 11880 != 0) {
        list($x, $y) = locationOf(0, $board);
        $i = ord($dataForSecondRowSolver[intval($encnum / 4)]) / pow(4, $encnum % 4) % 4;
        step($x, $y, $dirs[$i], $board, $result);
        $encnum = encodeForSecondRowSolver($board, $numbers);
    }

    return [$result, $board];
}

function encodeForSecondRowSolver($board, $numbers)
{
    return encode2($board, $numbers, range(4, 15));
}

//////////////////////////////////////////////////////////////////////

function moveThreePieces($y, $numbers, $board)
{
    if ($y == 0) {
        list($result, $board) = optimalSolverForFirstThreeNumbers(
            $board,
            $numbers
        );
    } else {
        list($result, $board) = optimalSolverForSecondThreeNumbers(
            $board,
            $numbers
        );
    }

    return [$result, $board];
}

function dirLRandPackR($y, $board, $goal)
{
    list($result, $board) = moveThreePieces(
        $y,
        [$goal[$y][0], $goal[$y][1], $goal[$y][2]],
        $board
    );
    fixRow($goal[$y][3], 3, $y, $board, $result);

    return [$result, $board];
}

function fixRow($n, $tx, $ty, &$board, &$result)
{
    $bwFun = function ($x, $y) {
        return buildWallsUL($x, $y - 2);
    };

    list($sx, $sy) = locationOf(0, $board);
    if ($sx == $tx && $sy == $ty) {
        step($sx, $sy, [0, 1], $board, $result);
    }
    if ($board[$ty][$tx] != $n) {
        moveTo($n, $tx, $ty + 2, $board, $result, $bwFun);
        $walls = buildWallsUL($tx, $ty);
        $walls[$ty + 2][$tx] = 1;
        moveSpaceTo($tx, $ty, $board, $walls, $result);
        list($sx, $sy) = [$tx, $ty];
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

function dirLRk($k, $y, $board, $goal)
{
    $bwFun2 = function ($x, $y) {
        return buildWallsUL(3, $y - 1);
    };

    $bwFun3 = function ($x, $y) use ($k) {
        $walls = buildWallsUL($x, $y);
        $walls[$y + 1][$k] = 1;
        return $walls;
    };

    $numbers = [];
    foreach (range(0, 2) as $i) {
        $numbers[$i] = $goal[$y][$i + ($i < $k ? 0 : 1)];
    }
    list($result, $board) = moveThreePieces($y, $numbers, $board);

    moveTo($goal[$y][$k], $k, $y + 1, $board, $result, $bwFun2);
    moveSpaceTo(3, $y, $board, $bwFun3(3, $y), $result);
    list($sx, $sy) = [3, $y];
    for ($i = 0; $i < 3 - $k; ++$i) {
        step($sx, $sy, [-1, 0], $board, $result);
    }
    step($sx, $sy, [0, 1], $board, $result);

    return [$result, $board];
}

function dirLR2($y, $board, $goal)
{
    return dirLRk(0, $y, $board, $goal);
}

function dirLR3($y, $board, $goal)
{
    return dirLRk(1, $y, $board, $goal);
}

function dirLR4($y, $board, $goal)
{
    return dirLRk(2, $y, $board, $goal);
}

function flipStrategyDirection($strategy, $y, $board, $goal)
{
    $board = flipLR($board);
    $goal = flipLR($goal);
    list($result, $board) = call_user_func($strategy, $y, $board, $goal);
    return [$result, flipLR($board)];
}

//////////////////////////////////////////////////////////////////////

function moveTo($n, $tx, $ty, &$board, &$result, $buildWallsFun)
{
    list($x, $y) = locationOf($n, $board);
    while ($x != $tx || $y != $ty) {
        $cands = [];
        if ($x != $tx) {
            $dx = $x < $tx ? 1 : -1;
            $sx = $x + $dx;
            $walls = $buildWallsFun($tx, $ty);
            $walls[$y][$x] = 1;
            $bx = $board;
            $rx = [];
            try {
                moveSpaceTo($sx, $y, $bx, $walls, $rx);
                step($sx, $y, [-$dx, 0], $bx, $rx);
                $cands[] = [$dx, 0, $rx, $bx];
            } catch (PathNotFoundException $e) {
            }
        }
        if ($y != $ty) {
            $dy = $y < $ty ? 1 : -1;
            $sy = $y + $dy;
            $walls = $buildWallsFun($tx, $ty);
            $walls[$y][$x] = 1;
            $by = $board;
            $ry = [];
            try {
                moveSpaceTo($x, $sy, $by, $walls, $ry);
                step($x, $sy, [0, -$dy], $by, $ry);
                $cands[] = [0, $dy, $ry, $by];
            } catch (PathNotFoundException $e) {
            }
        }
        if (count($cands) == 0) {
            throw new PathNotFoundException();
        } elseif (count($cands) == 1) {
            list($dx, $dy, $r, $b) = $cands[0];
        } else {
            $i = count($cands[0][2]) < count($cands[1][2]) ? 0 : 1;
            list($dx, $dy, $r, $b) = $cands[$i];
        }
        $x += $dx;
        $y += $dy;
        $result = array_merge($result, $r);
        $board = $b;
    }
}

function buildWallsUL($x, $y)
{
    $walls = emptyWalls();
    for ($i = 0; $i < $y; ++$i) {
        $walls[$i] = [1, 1, 1, 1];
    }
    for ($i = 0; $i < $x; ++$i) {
        $walls[$y][$i] = 1;
    }
    // array_splice($walls, 0, $y, array_fill(0, $y, [1, 1, 1, 1]));
    // array_splice($walls[$y], 0, $x, array_fill(0, $x, 1));

    return $walls;
}

function emptyWalls()
{
    return array_fill(0, 4, [0, 0, 0, 0]);
}

function moveSpaceTo($tx, $ty, &$board, $walls, &$result)
{
    if ($walls[$ty][$tx] !== 0) {
        throw new PathNotFoundException();
    }

    list($sx, $sy) = locationOf(0, $board);

    $queue = [[$tx, $ty]];
    while ($walls[$sy][$sx] == 0 && count($queue) > 0) {
        list($x, $y) = array_shift($queue);
        if ($x < 3 && $walls[$y][$x + 1] === 0) {
            $walls[$y][$x + 1] = [-1, 0];
            $queue[] = [$x + 1, $y];
        }
        if ($x > 0 && $walls[$y][$x - 1] === 0) {
            $walls[$y][$x - 1] = [1, 0];
            $queue[] = [$x - 1, $y];
        }
        if ($y < 3 && $walls[$y + 1][$x] === 0) {
            $walls[$y + 1][$x] = [0, -1];
            $queue[] = [$x, $y + 1];
        }
        if ($y > 0 && $walls[$y - 1][$x] === 0) {
            $walls[$y - 1][$x] = [0, 1];
            $queue[] = [$x, $y - 1];
        }
    }

    if ($walls[$sy][$sx] == 0) {
        throw new PathNotFoundException();
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

//
// Array utilities
//

function flipLR(array $arrays)
{
    return array_map('array_reverse', $arrays);
}

function cartesianProduct(array $arrays)
{
    if (!$arrays) {
        return [[]];
    } else {
        $tail = array_pop($arrays);
        $result = [];
        foreach (cartesianProduct($arrays) as $values) {
            foreach ($tail as $v) {
                $result[] = array_merge($values, array($v));
            }
        }
        return $result;
    }
}

main();
