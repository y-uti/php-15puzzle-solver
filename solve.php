<?php

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
        foreach ($chain as $s) {
            $sres = $s($b);
            if ($sres !== false) {
                list($rnext, $b) = $s($b);
                $r = array_merge($r, $rnext);
            } else {
                $r = false;
                break;
            }
        }
        if ($r !== false) {
            // echo 'Length = ' . count($r) . "\n";
            // printBoard($b);
            if ($best === false || count($r) < count($best)) {
                $best = $r;
            }
        }
    }

    return $best;
}

//////////////////////////////////////////////////////////////////////

function lowerTwoRowsFast(&$board, $goal)
{
    $data = 'EABEgBABRIAQEQSAzAxExAARQIQRAESEAAEyohEBRIABAETAEREAgEgMAIQAEUCEERBEgBAAIrIBAACAAABAwAARAISMCISIIjJIhCIyxIwiMiOiRIQREUSEEQFEgBEBIuIREQDAABEEwBEAQIBEDESEERFEwBEBRMQBASKzEQBEgAARQIQQAESAiAxEgBEAAIAAAADEABEyoTMyRIQiEsiEEjFEjMwIEBFEhBERRIQREUSEiAgAhAARQIQRAUTEAREisREARIARAADAEAEAgMgMRIQBEQCEERBExBERI7IAEUCEABFEgBERAITMCIiMIhGIhDEzRMwTETKzRIQREUSEEREEhBERIqIREUCEERFEhAEQAITMCESAEQBEgBABAMQAETKzEREEhBERRIQREESAiAhAhAAAAIAAEEDEARAyoyIjxIwTMYzIEzGIjMgIzAwzk8wMM/NEBDOzjAgRs8QEM5PMBBPxRAQyocwEM5HMBBGTRAwRs8wIM7FEBDOxxAwR0cwEI7FEBBGRRAQR0UQEEZFICMSMEzFEjDERzMwTMTOxRIARAEDEEAFAhAABEqIREQCEARFExBERQICMCECAAQBEwBABQIQAASKzEAFEhAERBMQREQSAiAgAgAABAIAQAACEABEi4xIzhIwiMojMMyGMiIgMEQFExBERRMAREUSETAxAhAAAQIQRAASAAAAz0xERRIQQAESEEREEwMwMBIQREEDEABBEgBEAE9IREETAAAAEwAARAMRECESMMRFMxCESRIwREhPTRIQREUTEERFEhBERIvIAAQDEABFAwBERAMCIBESEEQBEwAAABIQAACLTEREAxAARBMAREUTESAgAhAABAMQRAASEABET0yIziMQiEUzIERFExEQEERFEgBERRMQAEUTESAxAhBABRMARAQSEABET0xEQRMARAETEABAAxEQERIQAEQTAERFEgBEAE+IQEQDEAAAAwAEBAMCEBMyMESFEzDEihIQiMyLiM7HMBDPTzAwRscwMM9LEBBHTzAQz00QEEdNECDORzAQz0cQMEZFEBBPixAQR8UwEEfPEBDPRSAQRkUQEEdFEBBGRRAQi0zETRMwRM0zEMxFExEQEEQAEgAABRIAAEQDEiAQExAARAMARAQCEARER0wEARIAQAETAABEAxEgEAIQAEQDEARFEhBERE+IAEQDEAAFEwBERBIRICMiEIiNMxDEijIgiMiKiRIQREUTEEQFEhBERItMAAADAABEEwAARAIREDESEEQFEwAEAAIQBEROjEREExAARRMQREUSERAgEhBERAIQREUSEEREjozMTRIwSM0SIMSGEzMwIERFAhBERRMQRAUTAiAQAhAABAMAREUSAEQAToxERRMQRAEDEEQAAhEgMBIQAEETEEABEhBARE6IRAQCEABEEgBEAQISICIyEETFIhBEzzIwzMTKjBIAREUTAEBFEhBEREuIAEQCEERBAhAERRICMDESAEABEgAABRIQQECKjERFExBERRMQREETESAgEhAAARIQQAASEABAj4xMijMgiIUjIIjGIjIgITAQz8cwEMfPEDDOzRAwRs0QEE5HEDBGzxAQi48QEMZHMBBGTxAwRkcwMEbPMDDPxzAwzkcwEI/NEDBGRRAQR0UQMEbFMCESEMzHEzDERzIgRITPjIqAICCKiAAgAgogII6KICCKiiAgCoggIIIKICACgCAAg4oAIIKKICBPjiAgAgogIIuIAACCiSAwggIAIIOIACCCiiAgi0zMzzMwhMUjIIxPIxEgEgAgAgoAIAuKICCCijAwgoIgIIuCICCCAiAgT0wgIIOCICCLgiAAgoEQIIKKICCDgCAgggIgIE9OICCDgiAgi4ogAIoBECMSEERFEzCEiRIwxMSOjIIAACCDiAAgggogAMqKIACDiiAgC4IgAIqBECACgiAgg4IgIIKCICCLTgAAg4IgIIuCIACCgSAggoIgIIuCICCCAiAgS0yIziMwiEczMMxFMjEgMiAAAoogIAuKAACCiTAwgoIgIIOCICCCCiAgi04gIIOCICCLgiAAgoEQIIIKICCLgiAggoIgIE9OICCDgiAgi4IgAIqBECEyMMxFEyDERhIwRMxOiMZNEBDOTzAwxkcwEItJEBBHRRAQz80QEE5FIDDORzAQz0UQEEZFEBBOyRAQR8cQMEdHEBDGRRAwRkUQMEbNEBDGRRAwjozMTTIwRIkSEIzHEiMwMAIARAQDAEAFAhAARE7IBEACAAAFEgAARAITMDESEEQFEgBEAAIQAASOzEBEAhAABRMQREQSASAxAhAARAIAREACEEREyojMjzIQhMYzIEyLIiEgIEQBEgBERRIAAEUCEjAwEgBAAAIAAAADEABEz0RERRIQRAASAAREExMgEQIQAEQCEERFExBERI9IREQTEAAFExBERRIRECMTEERFMzDEiRIgiMiOyQIQREUSEERFEgBEQIqIREQCEEAFExBEABMCMBESEEQFEgBEAQMAAASLREQAEgAERQIAQEUDEiAhEhAABAMQQAATAABASoTMzjMQiEczMEzFMjEgMEBFEgBERRIQAEUSEjAhAwAARBMABEUDEEREToQEARMARAEDEAREAhEgMRIQREUSEERFEwBEAI6EAEQCEARBEwAERBIRICISIMSJIiCIiiMwiMyLiMbHEBDORzAwRs8wMIqJEBBHRTAQR88QMM7NICBHRxAQz0cQMEdNEBBPixAwRs0wMM7PMBDORiAwRsUQMEZFEBBPzRAwiozMzTIwTM8zEMyJEiEwMABEExBARRMQREUSERAwEhAABAMQREUSEERETshERRMQRAUTAERFAhEgMRIQREUSEERFEhBERM/IREUCEARFEwBERAIRMCESEExJIhCESRIgSMjKiRIAREETEEABAhBABErIRAADAABAAwAAAAIBEDECEABEAwAEABIABEBKzEBEEhAAAAMQRAASAyAwEgBEAAMAAAACAARAyoxMzRIQTEczEEzFEhEwMERFEhBERRMQREUSAjAxEhAAQQMQBAASAAQAzsxERRIQRAADEAQAEgMgMBIAREACEAABAhAERM7IBEQCEABEAgBEAQIDMCMiEERFIhBETRIwTETKzQIQQEUSEERFEhBERMqIAEQCAERAAhBEBQITMCETAEQAEgAARQIQBEDOzERFAhBERBIQRAESAyAxAhAEQAIAAAACEARAysxIiRIgSEcyEEhFIhMwMxAwRs8wEM/NMDDOzTAwRk0QEE5FEDBORxAQys8wEM5FMBBGTRAwRkcwMM7NEDDORTAwTkUwEM7NEBBGRRAQRkUQMEbHMCMSMEzFEhBERRIgRETOzAIQREQCEERFEhBERIuIREQCAAABExBERRIRMCESEEQFEwBEBQIQBESKyERFAhAERRMQREUTASAhEhAERAIAREUSEEREiojMiRIgiIkjIIiKIiIgIERBEgBERRIAQAEDAjAREwAAABIQAAACAAQAz0xERBIQAAACAAREEwMgEQMABAADEAABAgAAAM/MRAATAAAAAwAAAAISICMSEExFIhBITyIwRMzPTRIQREUSEERFEhBERIrIQEQCEERAAxBEBAMDMBESEEQAEwAAAAIAAABLTERFEhAERAIQREUTEyAhEhAARAMQRAASAARAS0yITxMwTEUzIEzFMxEgEARFAxBERRMQREUSERAhAhAERBMQREASAERAT0hEARMARAADEAAAAwEgERIQREATEAQEEgBEAI9IBEQDEAAAAwBEBAIRMCEiMIhNIxDMTRIwSExLTEbPEDDOzzAwzs8wMMtLEDBHRTAQT0UwEMbFEDDORTAQz0UQMEZFEBBPTzAwzsUwEEdFEBBPRSAQRsUQEEdFEBBORRAQS0xMjTMwREUzEEhNExEwEiAgggogAAqIIAAKCzAggogAAIIIICAKACAAyoggAAKIICALiCAAiokwMIoAIACKggAgAggAIIrKIACKAiAACooAIIICICISIEiJIjCIiiIgiIjKjAoCAACLiAAACgoAAEqKICCCiiAgC4ogAIoLMCACgiAgg4gAIIoKICBLziAgioogIAuIIAACCiAwigIAAIoIACACCgAgy0zMzRIwTE8zEExFExMwECAACggAAAqKAAACCiAwigIAAIqIACACCgAAzswgIIOKACCLigAAi4kwEAICACCLiAAgggoAIE/KAAADiiAgi4oAAIoJMCIiMEhNExBEzRIwTMRPTAoCAACKAAAgAgoAIMqKIACLigAAC4oAAIoLMCACAgAAi4oAIIIKACBPTgAgAgogIAuKAACLiSAwAgIAIIuKACCCCgAgSszIixIgRE0zEEhFEzEwIzAwzs8wMM/PMDDPzSAQRkUQEEZFEBBGRRAQz08wEM5FMBBHRRAQR0UwIM/NEDBPxRAQTkUQEM9PMDBGTRAwT0UwEEbFICMyIEzJEyDIRSIwRIRPTIgEIiCIgRIghEIiIjAiIiAIiiIASIEiIIRAj8gEBQIABIITIASKIgMwICIABEgiAIgBAhAAiIqIhAgSIIgCEyAIiiICICISIMSKEiCIiiIwjESPzBIgREADIEAAIiAEgI/IBAYCIAiEAiCEiiIjMDACAASEAiAAhiMghATKiAQKIiCEBCIACIQiIiAwIgCEiiIgCIojIIgIyozEiRIQTMoiIEzPExIwEAQIIiAEARIACEACAiAgIiCEiiIgiAQjAAAEioQEBAIABIQDIAQEIiIgMCIAiAYiAAAEAwAEBI6EBAYCAACEAyAEBgIBICIiM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';
    $data = base64_decode($data);
    $dirs = [[0, -1], [-1, 0], [1, 0], [0, 1]];

    $numbers = array_merge($goal[2], $goal[3]);

    $result = [];
    $encnum = encode($board, $numbers);
    while ($encnum != 0) {
        list($x, $y) = locationOf(0, $board);
        $i = ord($data[intval($encnum / 4)]) / pow(4, $encnum % 4) % 4;
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

function dirLRandPackR($y, $board, $goal)
{
    $bwFun = function ($x, $y) {
        return buildWallsUL($x, $y);
    };

    $result = [];
    moveTo($goal[$y][0], 0, $y, $board, $result, $bwFun);
    moveTo($goal[$y][1], 1, $y, $board, $result, $bwFun);
    moveTo($goal[$y][2], 2, $y, $board, $result, $bwFun);
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
        if (moveSpaceTo($tx, $ty, $board, $walls, $result) === false) {
            return false;
        }
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

function dirLR2($y, $board, $goal)
{
    $bwFun1 = function ($x, $y) {
        return buildWallsUL($x, $y);
    };

    $bwFun2 = function ($x, $y) {
        return buildWallsUL($x + 1, $y - 1);
    };

    $bwFun3 = function ($x, $y) {
        $walls = buildWallsUL($x, $y);
        $walls[$y + 1][0] = 1;
        return $walls;
    };

    $result = [];
    moveTo($goal[$y][1], 0, $y, $board, $result, $bwFun1);
    moveTo2($goal[$y][0], 0, $y + 1, $board, $result, $bwFun2);
    if (moveTo($goal[$y][2], 1, $y, $board, $result, $bwFun3) === false) {
        return false;
    }
    if (moveTo($goal[$y][3], 2, $y, $board, $result, $bwFun3) === false) {
        return false;
    }
    if (moveSpaceTo(3, $y, $board, $bwFun3(3, $y), $result) === false) {
        return false;
    }
    list($sx, $sy) = [3, $y];
    $moves = [[-1, 0], [-1, 0], [-1, 0], [0, 1]];
    foreach ($moves as $m) {
        step($sx, $sy, $m, $board, $result);
    }

    return [$result, $board];
}

function dirLR3($y, $board, $goal)
{
    $bwFun1 = function ($x, $y) {
        return buildWallsUL($x, $y);
    };

    $bwFun2 = function ($x, $y) {
        return buildWallsUL(3, $y - 1);
    };

    $bwFun3 = function ($x, $y) {
        $walls = buildWallsUL($x, $y);
        $walls[$y + 1][1] = 1;
        return $walls;
    };

    $result = [];
    moveTo($goal[$y][0], 0, $y, $board, $result, $bwFun1);
    if (moveTo($goal[$y][2], 1, $y, $board, $result, $bwFun1) === false) {
        return false;
    }
    if (moveTo($goal[$y][3], 2, $y, $board, $result, $bwFun1) === false) {
        return false;
    }
    if (moveTo2($goal[$y][1], 1, $y + 1, $board, $result, $bwFun2) === false) {
        return false;
    }
    if (moveSpaceTo(3, $y, $board, $bwFun3(3, $y), $result) === false) {
        return false;
    }
    list($sx, $sy) = [3, $y];
    $moves = [[-1, 0], [-1, 0], [0, 1]];
    foreach ($moves as $m) {
        step($sx, $sy, $m, $board, $result);
    }

    return [$result, $board];
}

function dirLR4($y, $board, $goal)
{
    $bwFun1 = function ($x, $y) {
        return buildWallsUL($x, $y);
    };

    $bwFun2 = function ($x, $y) {
        return buildWallsUL(3, $y - 1);
    };

    $bwFun3 = function ($x, $y) {
        $walls = buildWallsUL($x, $y);
        $walls[$y + 1][2] = 1;
        return $walls;
    };

    $result = [];
    moveTo($goal[$y][0], 0, $y, $board, $result, $bwFun1);
    if (moveTo($goal[$y][1], 1, $y, $board, $result, $bwFun1) === false) {
        return false;
    }
    if (moveTo($goal[$y][3], 2, $y, $board, $result, $bwFun1) === false) {
        return false;
    }
    if (moveTo2($goal[$y][2], 2, $y + 1, $board, $result, $bwFun2) === false) {
        return false;
    }
    if (moveSpaceTo(3, $y, $board, $bwFun3(3, $y), $result) === false) {
        return false;
    }
    list($sx, $sy) = [3, $y];
    $moves = [[-1, 0], [0, 1]];
    foreach ($moves as $m) {
        step($sx, $sy, $m, $board, $result);
    }

    return [$result, $board];
}

function flipStrategyDirection($strategy, $y, $board, $goal)
{
    $board = flipLR($board);
    $goal = flipLR($goal);
    $result = call_user_func($strategy, $y, $board, $goal);

    return $result === false ? false : [$result[0], flipLR($result[1])];
}

//////////////////////////////////////////////////////////////////////

function moveTo($n, $tx, $ty, &$board, &$result, $buildWallsFun)
{
    list($x, $y) = locationOf($n, $board);
    while ($x != $tx) {
        $dx = $x < $tx ? 1 : -1;
        $sx = $x + $dx;
        $walls = $buildWallsFun($tx, $ty);
        $walls[$y][$x] = 1;
        if (moveSpaceTo($sx, $y, $board, $walls, $result) === false) {
            return false;
        }
        step($sx, $y, [-$dx, 0], $board, $result);
        $x += $dx;
    }
    while ($y != $ty) {
        $dy = $y < $ty ? 1 : -1;
        $sy = $y + $dy;
        $walls = $buildWallsFun($tx, $ty);
        $walls[$y][$x] = 1;
        if (moveSpaceTo($x, $sy, $board, $walls, $result) === false) {
            return false;
        }
        step($x, $sy, [0, -$dy], $board, $result);
        $y += $dy;
    }
}

function moveTo2($n, $tx, $ty, &$board, &$result, $buildWallsFun)
{
    list($x, $y) = locationOf($n, $board);
    while ($y != $ty) {
        $dy = $y < $ty ? 1 : -1;
        $sy = $y + $dy;
        $walls = $buildWallsFun($tx, $ty);
        $walls[$y][$x] = 1;
        if (moveSpaceTo($x, $sy, $board, $walls, $result) === false) {
            return false;
        }
        step($x, $sy, [0, -$dy], $board, $result);
        $y += $dy;
    }
    while ($x != $tx) {
        $dx = $x < $tx ? 1 : -1;
        $sx = $x + $dx;
        $walls = $buildWallsFun($tx, $ty);
        $walls[$y][$x] = 1;
        if (moveSpaceTo($sx, $y, $board, $walls, $result) === false) {
            return false;
        }
        step($sx, $y, [-$dx, 0], $board, $result);
        $x += $dx;
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
        return false;
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
