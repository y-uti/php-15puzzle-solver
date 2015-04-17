<?php

function main()
{
    $board = readBoard();

    $resultUD = solveUD($board);
    $resultLR = solveLR($board);

    $result = count($resultUD) < count($resultLR) ? $resultUD : $resultLR;
    writeResult($result);
}

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
//         if ($x != 0) $cands[] = [-1, 0];
//         if ($x != 3) $cands[] = [1, 0];
//         if ($y != 0) $cands[] = [0, -1];
//         if ($y != 3) $cands[] = [0, 1];
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

    $strategies[] = [
        function ($b) use ($goal) {
            return dirLRandPackR(0, $b, $goal);
        },
        function ($b) use ($goal) {
            return dirRLandPackL(0, $b, $goal);
        },
        function ($b) use ($goal) {
            return dirLR2(0, $b, $goal);
        },
        function ($b) use ($goal) {
            return dirRL2(0, $b, $goal);
        },
    ];

    $strategies[] = [
        function ($b) use ($goal) {
            return dirLRandPackR(1, $b, $goal);
        },
        function ($b) use ($goal) {
            return dirRLandPackL(1, $b, $goal);
        },
        function ($b) use ($goal) {
            return dirLR2(1, $b, $goal);
        },
        function ($b) use ($goal) {
            return dirRL2(1, $b, $goal);
        },
    ];

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
            // pathCompression($r);
            // echo 'Length = ' . count($r) . "\n";
            // printBoard($b);

            if ($best === false || count($r) < count($best)) {
                $best = $r;
            }
        }
    }

    return $best;
}

// function pathCompression(&$r)
// {
//     $i = 0;
//     while ($i < count($r) - 1) {
//         if ($r[$i] == $r[$i + 1]) {
//             array_splice($r, $i, 2);
//         } else {
//             ++$i;
//         }
//     }
// }

//////////////////////////////////////////////////////////////////////

function lowerTwoRowsFast(&$board, $goal)
{
    $data = 'S L U U  L L U RU L L U  L L U RU L L L  L U U R D D D   L L L DU U L L  U L L RL L U U  L L L RU U L U R D R RRL L L U  L L U RL U U U  L L U DL L L L  U U U R R L D   U U L RU U L L  U L L RL L U L  L L U RU L U U R R R DRL U U U  U U U RU U U U  U L U DU U L L  U U L R D R R   L R R RR R R D  R L L RR R R D  L D D RR R R D D R R RR L L L RL L L L  L L L RL L L U  L L U RL L L U R R R RDL L L L  U U U DU U L L  L U U DL L U U  U L U R L L D   L L L RL L L L  L L U DL L L U  L L L DL U L U R R D DRL L U U  L L U RU U L L  U L L RU L U U  L L U R R R D   L L U RL L U U  U U U RU U U U  U U L DU U L L R D L RRD D R D  L L L RR R R L  R D L RR L L D  L L D R D D R  U L L L  L L L RL L L L  L L L RL L L L  L L L R R R R   U U L RU U L L  U L L RL L L U  L L L DL U L L R R L DRL L U U  L L U RL L U U  U U U DU L L U  U U U R R D D   L L L RL U L L  U U L RL L U L  L L L DL L L L D R R DRU U L L  U L L RU U L L  L L U RL L L L  U U L R D D R   R R D RR R L L  R R L RL D D D  L L D DD L L L R D D DR L L L RL L L L  L L L RL L L L  L U L RL L L L R R R RRL L L L  U L L RL L L L  L L L RL U U L  U U L R D D R   L L U RL L U U  L L U RU L L U  U U L DU U L L R D D DRL L L L  L U L RL L L L  L L L RL L U L  L L U R R R R   U L L RU U U U  U U U RU U U L  U L L DL U U L R D D RRR R D R  L D D RD L L D  D R R DD L L D  R R D R R D R   D D D  D D D LR D D D  D D D DD L L L  D D D DR D R R  L L D DR L D L  D D D LR D D L  D L L DD L L L  R D L RR D D L  D D L LR D D L  L L D LR L L D  L L D DR D D R  D D L DR L L L  D D L DR L D D  L L L LD D D L  D R L DR L L L  L L L LR L L L  L L L LD L L L  L L L LR R L R   L D D RD L L D  L L D RL D L L  D D D DD L L D D D L DR L L U RL L U U  U L L DU L L U  U L L RU U L U R L R RRL L L L  U U L RL U L L  L L L DL L L L  U L U R D R R   U L U RL U U U  L L U DU L L U  U L L RU U L U R R D DRU L L U  L L L RL U L L  L U L DL L L L  L U U R R R R   U U U RU U L U  U U U RU L U U  U U L RU U L L R R D RDR L D D  L R D RR R R D  R R D DD D L R  D R R R R R D  L L L U  L L L DL L L L  L L U DL L L L  L L L R D L D   U L L RU U U U  U L L RL L U U  L U U RU U U U D D D LDL L L L  L L L RU L U U  L L L RL L L L  L U U D D D D   L U L RL L U L  U L L DU U U L  L L U RL L U U D L R LDL L U L  L L U DU U U U  L U U DU U L L  U U L D L L R   L L D RL D L L  D L L DL R R L  L L D RL L R L D L D LD L L L RL L L L  L L L DL L L L  L L L RL L L L R R R DDU U L U  U U L DU U L L  U L U DL L L L  U U U D R R L   L L L RL L U U  L L U DU U U U  L U L RU U U U R R D LDL L L L  U U L DU U L L  L U U DL L L L  L L L D R L R   U U L RU U L U  U U L DL L U U  L U L RU U L L D L D LDR R D D  R R L DR R L L  D L R DL L L L  L L L D L L L  L L L L  L L U RL L L L  L L L DU U L L  L L L D R L D   U L L RU L L U  L L U DL L L U  L U L RU U L L D L D LDL L U L  L L U DL L U U  L L L DU U U L  U U L D L L L   L L L RU U L L  L U U DL L L L  L L U RL L U U D L R RDU L L L  U U L DU U U U  U U U DL U L U  U U U D L R L   D D D RL L L R  L L D DL D R R  L R L RR R D D R R R RDD D L DR D D L  D D D LD D D D  L L L DR D D D  D D R LD L D L  L L D LD D D L  D D D LD L L L  L L D LD L L R  D D L LR D D L  D D L LD L D D  L L L LR L L L  D L R RD L D L  L L L DD D L L  L L D DD L D L  D D L LD R L L  L L L LR L L L  L L L LD L L L  L L L LR L L L  R R D LDL D D L  L L D DL L D D  D L L DD D L L  L L L D L L L  L L U U  L U U RU U L U  L L U RU U L L  U U L D R R L   L U L DU U L L  U U U DL L L U  U U L RL U L L L L D LDL U U U  L L U RU L U U  L L U DU U L L  U U L D R L L   U U L RU U L L  U U L DL U L L  L L L RL L L L D L R RDU U L L  U U L DU U L U  L L U DL L L L  L U L R R L R   R D L RR R D R  D L L DL D R R  D R R RR R R D R R R RR L L L RL L L L  L L L DL L L U  L L L RL L L L R R D LDU U U U  U U U DU U L L  L U U DU U L L  U U L R L L D   L L L RL L L U  L L U DL U U U  U U L RL U L L D L D RRL L L L  L U L DU U L L  L L L DL L L L  L L L R L L R   L U L RL L L L  U U L RL L L L  L L L RL L L L D R D RRD D D L  L L D RR L D D  L L R RL D L R  L R D D D D R  L L L L  U L L RL L L L  L L L DL L L U  L L U D R R L   U U L RU U L U  U U U DL L L L  L L U RL L U U D L D RRL L L L  L L L DL L U U  U L L DL L U U  U U L R R L D   L U L RU U U L  L L L DU L U U  L L L RU L L L D L R RRL L L U  U U L RU U L L  L U U RL L U U  U L L R R R R   D R L RL L L D  R L L RL L D D  D D D RD D L D R D D RR L U U RL L L L  L L U DU L L L  L L L RL L L L R L R RDU U L L  U U L RL L U L  U L L RL U L L  L L U R D R D   L L U RU L U U  L L U RU U L U  L L L RU L U L R R D RRL L L L  L L L DL L L L  L L L DL L U L  L L L D R L R   L U L RU U U U  L L L RU L U U  L U L RU U U L D R D RDD L R R  D R R DR R L R  R L R DR R L D  R R D R R R R   D L L  D D L DD D D L  L D D DD L D D  D D D DR L L D  L L D DR L L L  D L L LR L D D  L L D DR L D L  R R D RD L D L  L D L LR D D L  L L D LR L D D  L L L LR D D D  L L D DR D D D  D D L DD D D D  D D L LR D D L  D R D DD L L D  L L L LR L L L  L L L LD L L D  L L L DR D L R   L L L RD D L D  L D D DL D L L  D D R RL L L R D D D RDR R U RR R U R  R R R RR U U R  U U R UR R R R  D R R RR R R R  R R R RR R R R  R U R RR R U R  U R R UR R R R  U U U RR R U U  U R R RD U R R  U R R RR R R R  D L D RD R R R  U U R UR R R R  R R R RD U U U  U R R RR R L D  U R U UR U R R  U R R RD U U R  U R R RR R R R  R R D LDD D D D  D D D DL R L D  R L R DD R D L  R D L D R L L   U R R  U U R UR U R R  R U R RD R R R  U R R RR D R D  U R U RR R R R  R R U RD R R R  U R U UR R R R  D L D LD R U R  U R U RD R R R  R R U RD R R U  U R U RR L L R  U R R RR R R R  U R U RD R U R  U R U UR R R R  D L D LD R R R  U R U RD R R R  R R R RD R R U  R R U UR L L R   L D L RL L L L  L L D DL R R R  L L D RL D L D D R D RRU R U UR U U R  U R R RD U U R  U R R UR R R U  R D R RR R R U  U R R RD R R R  R U U RD R R U  R R U RR L L R  U U U RR R R R  U R U RD R R R  U R U RR R R R  R R D LD U R U  U R U RD R R R  R R U RD R R U  U R U RR R L R  U R U RR R R R  R R U RD R R R  U R U UR R R R  R L D LDR R D D  R R D DR R L L  D D D DD D L L  D L D R R L D   R R U  U U R RR R R R  R U R RD U R U  U R R RR D L D  U R U RR R R R  U R U RD R R R  U R R UR R R R  R R D LD R R R  U R U RD R R R  R R U RD R R U  U R U RR L L R  U R R UR R R R  R R U RD R R R  U R U RR R R R  D L D LD R R R  U R U RD R R R  R R U RD R R U  R R U RR L L R   D L D RD D L L  L L R DL D L L  L R D RL L D D D L R RRL D D LR L L L  D D D LR D D D  L D L LR D D L  R R R LD L L L  L L L LD L L L  D D D DD L L L  D L L LR R L D  D D L LR D D L  D D L LD L L L  L L L LR L L L  D L R DR L L L  L L L DD L D D  L L L LD L D L  L D L LR L L D  L L L LR L L D  L L D DR L L L  L D L LR L L D  D R D RRD D D L  D L D RL L R R  L L L RD R L D  L D R R D D D   U U U RL L L U  U U U DU L L U  U L L RU U L L D L R DRL U U L  U U U RU U L U  L L U RU U L L  U U L R D D D   L L L RL L L U  L L U RL L U U  U U L RU U L U D R D DRU L L L  U U L RU U L U  L L L DL L L L  L U U R R L D   U L L RU U L L  U U U RL L U L  U U L RL L L L R D R RRD D D R  D D L RL R L D  D R R DD L R R  R D R R R L R  L L U U  L L U RL L L L  L L U RU U L L  U L L R D R D   L U U RU L U U  U U U RU U U U  U U L DU U L L D D L LDL L L L  L L L RL L U U  L U U RL U L L  L U L D R D L   U L L RU U L L  U U L RL L L L  L L L DL L L L D R R LDL L L L  L U L DU U L U  L L L DL L L L  L L L R L L R   L D L DL L L L  D L D DL D R R  L L R RR R R D D R R DR U L L RL L L L  L L L RL L L L  L L U RL L U L R R R RRL L L L  U U L RU L L U  L L L DL L U U  L U U D D R L   L L L RL L L U  L L U RL L U U  U L U DU U L U R R L LDL L U U  L U U RL U L L  U L U RU L L L  U L L D R R R   L L L RU U L U  U U L DU L U U  L U U DU U U L R L L RRD D D D  D R L DR R L L  D D D DD L L D  D L D R R L D  U L L L  L L U RL L L L  L L L RU U L L  L L L R D R R   U L U DU U L L  L U U DL U L L  U L L DL L L L D L L RRL U U U  L L U DL L U U  U L L DL U L L  U U L R R L D   L L L RL L L L  L L L RL L L L  L L U DL L U U D R L RRU U L L  U U L RL U U L  L L U DL U L L  L U L R R L R   L R R RL D R R  R L R RR R R R  R R D DR R D D R R R RDL D L DR L D L  D D L LR D D D  L L D DR D D D  R R R RR L L L  L L L LD D L L  L L D DD L D D  D D D DR R L R  L L L LD L D L  D D L LD L D D  L L D LD L L L  D L R RD L D D  L L D DR D L D  D D D DR D D L  D D L LR R R D  L L L DR L L D  L L L LR L L L  D L D DD L L D  R R D RRD D D D  D L D RD L D D  D D L DD D R R  L L R R D L D  U U L L  L U L DU L L L  L L L DL L L L  L L L R L L D   L U L RU U L U  U U L DL L L L  L L L RL L L L D L R DRL L L L  L L L DL L L U  L L U DL L L L  U L L R R L D   L L L RL L L L  L L L RL L L L  L L L RL L L L D D R DDL L L L  U L L RL U L L  L L U DL L L L  U U L R D L R   L L L RD L R L  R L L RL R R L  L L R RR L R D R D R RR L L U RL L U L  L L L DU L U U  U L L RU L L U R L R DRL L U U  U U U DU U U L  U U U DU U U U  U U U R L L D   U L L RU U L L  U U U DL U U U  L U U RL U U L R L D DRU L L L  L U L RU U U U  U U L DL L U U  L U U R R D D   L U U RL L U U  U U U DU U U U  U U U RL U U L R D D RRD L D D  L L L RD L L L  D D L DD L L D  L L L R D L D  L L L L  L L L RL L L L  L L L DL L L L  L L U R D R D   L L L RU U U L  U L L DL U U U  L U U RL U U U D D D DRL L L L  L L L RL L U U  U U L DL U U U  L U U R R D D   L U U RL L U L  U U L RU U U U  U L L RL U L L D D R DRL U L L  U U L RU U L L  U U U RL L U U  U L U R D D R   R D L RL L L L  R L L RL L D L  L L D RD L L L R D D DR U L L RU L L L  L L L RL L L L  L L L RL L L L R D R RRU U L L  U U U RL L U L  U U L RL L L U  U L L R D D R   L L U DL L U U  L U U RU U L L  U L L RL U U L D D D DRL L L L  U L L RL L L L  L U L RL L U U  L L U R R D D   U L L RL U U L  U U U RU U U U  U U L RL U U L R D D DRR L R R  L L R RR L L L  D D L RR L L L  R L L R D D D   L D D  L L D DR D D L  D D D DD D L D  D D D DR D L D  L L D LR L L L  D L L LR L L D  D L L LR L D L  R D D DR D D L  D D L LR D L L  L L D LR L L D  L L L LR D D D  D D D DR L L D  D D L LR D L D  D L L LR D L L  D D D DR L L L  L L L LR L L L  L L L LR L L D  L L L DR D D R   L D D RD L L D  L L L RL L L L  L L R RL L L L D D D DR U U L RL L L L  U U L RL L L L  L L L RL L L L R R R RDL L L L  U U U RU U U U  L L L DL L L L  L L L R D L R   L L L RL L L U  L L U DL L L U  U L L RL U L L R R R DRL L L L  U L L RL U L L  L L L DL L L L  L L U D R L R   L L L RL U L L  U U U RL L L L  L L L RL L L L R R R RRD D R R  L L R RR R R R  R L R DR R R R  R R R R R R R  L L U L  L L U RL L L L  L L U RU L U U  U L U D D R L   L L U DU U U U  L U L RU U U U  U U U RL U U U D D D LDL L L L  L U L RU U U U  U U U RL U L L  L U U D R D L   U L U DL U U U  U U L DU U U U  U L U RU U U U D D D DDL L U U  L U U DU U U U  U U U DU U U U  U U L R R R R   L D L RD L L L  R L L RR L D L  R D D RL L D D D D D LD L L L RL L L L  L L L RL L L L  L L L RL L L L R R R DRU L L L  U U L RL L U L  U U L DL L L U  U U U D D D L   L L L RL L U U  L U U DU U U U  U U U RU U U U R L D LDL L L L  L L L RL U L L  U U L RL L L L  L L L D R D R   L L L RU U L L  U U L DL L U U  L U U RL U U L R L D LDR R D L  L D D DD L L L  D L R DD L L D  D L L D R L L  L U L L  U L L DL L L L  L L L DL L L L  L L L R L L R   U L L RL U L L  L U L DL L U L  L U U RL L U L D L R LDL L U U  L L U DL L U U  U U L DU U U U  U U U D R L L   L L L RL L U L  L U L DL U L U  L U U RL L U U D R R LDL U L L  U U L DU U U U  U U U DL L L U  U U L R D L R   R L D RR R D L  R L L DD D D L  L L D RR L D L R L D LDL L D DR L D D  D D D DR D D D  D D D DR D D D  R D R LD L D D  L L L LD D L L  D L L LD D L L  L D L DR L L D  D D L LR D L L  D D L LD L L D  L L L LR L L L  D L D LD D D D  D D L DR D L L  L L L LD L L L  D L L LD R L L  L L L DR L L L  L L L LD L L L  D L L LR L L L  R L D LDD L D R  D L D DL L L L  D L L DR L D L  L L L D D L L   R R R  U R R UR R R U  R U R RR R U U  R U R UR D D R  U R R RR U U U  U R R UR R U R  R U U UR R U U  R D R RR R U U  U U R RR R U R  R U R RD R U U  R R R RR D L D  R R U UR R U U  R R U RR U R R  U U R UR U U R  R R R DR R R U  R R U UR R R U  R U R RR U R R  U R U UR R R R   L R R RR L R R  R L D RR R R R  R R R RR R R R R D D RRR U U UR U R U  R R R RD U U U  R U R UR U R U  R L R RR R R R  U R R RR R R R  R U R RD R R U  R R R UR D D R  U U U RR R R R  U R R RD U U R  R R R UR R R R  R L D DD R R R  R R R RR R R R  R U R RD R U U  U U R UR R R D  R R U UR U R U  R R R UR U U R  U U R UR U R R  R D D LDD D D D  L L D RD L D L  D D L DD L L L  L L L D D D L   R U U  R U R UR U U U  R U R RR U R U  U U R UR R R D  R R U UR U R U  R R R RR U U R  U U R UR U R U  D D D DR R U R  U R R RD U R R  R R R RD U R U  R R R RD D L L  U U U UR U R R  R R R RD U U R  U R R UR U R R  D L R DD U R U  U U R RD R R R  R R R RD U R U  R R R UR D L R   R R D RR L D L  L L L DL L D D  L L D RD L L D D L D LDR U U UR U R U  R R U UR U U R  U U R UR U R R  R D R RR R R U  R R R RD U R U  R U R RD U R U  R R R UR D D R  U U U UR U R U  R R R RD U R R  U R R UR U R R  D L D LD U R R  U U R UR R R R  R U R RD U R U  R R R RD R L D  U U U UR U R R  R R R RD U R R  U R R UR U R R  R L D DRR D R R  L D R RL L D L  D L L DR L L L  L L D D D L R   D D D  D D D DR D D D  D D D DD D D D  D D D DD R L L  L L L LR L L L  L L L LR L L L  L L L LR L L L  D D D LD D D L  D D L LR D L L  L L L LD L L L  L L L LD D L R  D D D DD L L D  D L L DD L L L  D L L LR L L L  D D D LD D D D  L L D LR L L D  D L L LD D L L  L L L DR R L R   D D R RD L R D  L L R DR D L L  R L D RL L L R D L D LDR R L U  R U R RR R U R  L L R RL R U L  R R R R D R R   R R R RR U R R  R R U RR L U R  R L R RL R U L D R R DDL U L U  U L U RL U U R  L R R DL U R R  R R U R D D R   R U U RL U R L  R U U RR R U U  U L L RU U R R R R R RRL R R U  L U R RR R U U  L R R DR U R R  R R U R R R R   L R R RL D R R  L R R RR R R R  R R D RD R L L D R D DD L U R RL L U L  U U R DU L U U  R U R RL U U R D R R DDL U L U  U R R RR U L R  U U R RL R R R  R R R R D D D   U U U RL U L R  U U R RU U L R  R R R DL R L U R D R RRL U R U  R R R RL R L U  R U U RR U L R  R U R R R R D   R U U RL R R R  R R R RR U R R  R R R DR R R U R D D RRL D R R  L L L RD L R D  R R R RD L D D  L D L D D R L  L U R U  R U R RL U U U  L L U RR U U L  U U U R R R R   R U R RL R R R  R R R RR R L U  R U U DU U L U R R L RRL U L U  U U U RL U L R  U U R DL U L U  R U R R R R D   R U U RR R L U  R R U RU U L U  U U U DL U L U D R L RRL U L U  U R U RU U L R  U U R DL U L U  U R U R R L R   R R D RR R L D  R R L RL D D D  D D D DD D D L R R L LD R U U RL L U U  R U U RU L R R  L U R RR R U U R R R RRL U L U  R R U RL R L U  U U R RL U R U  U U R R R R R   U U U RL U R R  R U R DR U L R  R U U DL R U U D L R RRL U R U  U R R RR R L U  R U U RR U L U  U U R R R R D   U U U RL R R R  R R R RR U U U  R U U DU U L U R R L RRD D D D  D D D RR D D R  D D L DD D L D  L R D D D R R   L D D  D L D DD D D D  D D D DD D D L  D D L LR R L D  L L D DR L L D  L L D LR D D D  D D L LD L L L  R R D LD D D L  D D L LR D D L  L L L LD L L L  L L L LD D L R  D D L LR L L L  D L L DR L L L  L L L LD L D L  D R L LD L L L  L L L LD L L D  L D L LD L L L  L L L LR L L R   D D L DD D D L  L D R DL R L L  L L D DL L D D D L R RR U U R RL U R U  R U R DR L U R  R U L RR U U R R L R DRL U R U  R U R DL R R U  R L U DR R U L  U U L R R L R   R U U RL L U U  U U R DU L R R  U R R RR R U R D D R DRR U L U  U U U RU R L U  L U U DL L U L  L L R R R D D   R R L RR U R U  R U R RU L U R  L R R RR R U L D R R DRR R R L  D L D RR L R R  R R D DR D D R  R L L R R L D  R R L U  U U U DU U U R  L L R DU U U L  U R R R L L D   U U R RL R R U  R R U DR R R R  U U L RR U R R D L D DDL U U R  R R U DL R R R  L R U DU U R R  U U R R L L D   U R U RR U R R  U U U RR L R R  U L L RR U R R D R D LDU R L L  R U L DR R R R  L R R RL L R U  L R U R D D R   D D D RL L L L  D L L DL D D L  L L D RL L L L D L D LD R U L RR U U L  U U U DU L U R  L R L RL L U R R R R DDU R L U  U U R DR R R R  L L R RR L R L  L R R R D D R   U U U RL U R R  U R R RR R R R  L R L RR R R R D D D DDL L L L  L R U RR R R R  L R R RL R U R  L R R R R D D   L U U RL R R R  R R R RU R R R  L R L RR R R R R D D DDR R D D  L L D DL L L L  D L L DD L L D  L L L R D D D  U U L L  U U L RR R U R  L U R RL L U L  R R R R D D R   R R L RR U R R  U U L RU L R R  L U L RR R R L D D R LDL L L L  L R R DR R R R  L R R RR R U R  L R R R D D D   L U U RR R R R  R R U RU R R R  U R L RR R R R R D D DDL L L L  L R R RR R R R  L R R RR R R R  L R R R R D R   L D D RL L D L  L L D RD D L L  D L D RL L D L D D R DRD D D DR D D D  D D D DR D D D  D L L DR D D L  D R R DD L L L  L L L LR L L L  L L L LD L L L  L L L LR D L D  D D L LR D L L  D L L LR L L L  L L L LR L L L  D D R DR D L L  L L L LR L L L  L L L LD L L D  L L L LR R D D  L L D LR L L D  L L D DD L L L  D D L LR L L L  R D D RRD D R D  L L L RL L D L  D L L RR L D D  L L D R D D D  L U R R  R L R DL R U U  L L R DR R U L  R R R D R L D   R U U RL R R R  R U U RR L U U  L U R RU U R R R R R LDL L R R  U U R DU U U R  R R R DR R U L  U R R R R L R   R U U DU L R U  R U U DU L U U  L L R RR U R R D L D LDR U R R  R U R RR R R U  R R R DR R L L  U R U R R R R   D D L RR R D L  R R D DD D L L  D D D RL L R D D L R RR L U U RL L U U  U U R DU L U U  L R L RU U U U R R R LDL U L U  L R R RR R R R  L L R DL U R R  R R U D D D L   U U U DR R R R  L R R DR L R R  L R L RL R U U R D R LDL L L U  L R U DU R R U  L U U DL L R R  R U U D D L L   U U R RR R R U  R R U RR R U U  U U L RU U R L R R D DRD D D L  L L L DD L D L  D L L DD D L D  L L L R L L D  L U L R  L U R RU R U U  L L U DR L U L  U R R D R L D   U L L RR R R U  L R R RR L U R  L U L RU R R U R D D LDL L R U  U R R RR R R R  L R R DR R R R  R R U D D L L   L L U RU R R R  R R U DR L R R  U R L RR R R U D L R LDR U U R  R U U DU R R R  U R U DU U R L  U U U R L L R   D D L RD D L L  L L L DL L D D  R D D RD L L L R L D DR U U R RU U R U  R R U DR L U R  R U L RU R U L R D R DRL U U R  R U U DR R R U  L R U DU U R L  U U L R R L D   R U R RL R R U  R R U DR R R R  U U L RR U R R D L D DRR U L U  U U U RR R R U  L R U RL L R L  L R R R R D D   R R L RR U R R  U U R DR L R R  L U L RR R R L R R D DRD D D D  D L D RL L L L  D L L DD R L D  D L L R D L D   D D L  D D L LD D D D  L L L LD L D D  L L D LR L L D  L L L LR L L L  L L L LD L L L  L L L LR L L L  R L D DR L D L  L L L LD L L L  L L L LD D L L  L L L LR L L D  L L L LR D L D  L L L LR L L D  D D L LR L L L  D D D DR L L L  D L L LR L L L  L L L LD L L L  L L L DR D L R   L L L DR L L L  L L L RL D L L  L L D RD L D R R D D RRU R U UR R U R  U R R RD U U R  R U R UR R R R  D R R DD R R U  U R R RD R R R  R U R RD U U U  U R R UR R L D  U U R UR U U R  U R R RD U U R  R R R UR R R R  D L R LD U R R  U U R UR R R R  R U R RD U U R  U U R UR D D D  U R R RR R R U  R R R RD R R R  R U U UR R R R  R L R RRL D D D  D R L RR L R R  D D L DD D D D  L D D R D L R   U U U  U U R RD R R R  R U R RD U R U  U R R UR R L D  U U U RR U R U  R R R RD U U R  U R R UR R R R  D L D DR R U R  R R R RD U R R  R U R RD U R U  U R R UR L D D  U U U UR U R R  R R U UR U U R  U U R UR R U R  D D D DR R R R  R R R RR R R R  R U R RD R U U  R R U UR D L R   D D L DL L L L  L L L RL D L L  L L D RD L D D D D R RRU U U UR U R U  R R R RD U U R  U U R UR R R R  D R R RR R U U  R R R RR U R R  R U R RD U R U  R R R RR D L D  U U U RR U U R  R R R RD U U R  U R R UR R R R  D L D DR U U R  R R R RD U R R  R U R RD U U U  U R R RR R D R  U R U RR R R U  R R R RD U U R  R R R UR R R R  R L D DDD D D D  D L L RL L L L  D D D RD L L D  D L L D R D L   R R R  U R R UR R R U  R U R RD R U U  R R R UR D L R  U R U RD R R R  R R R RR U U R  R R R UR R U U  R D R DR R U R  U R R RR R R R  R U R RD R R U  R R R RR D D D  R R U UR R R U  R R U RR U U R  U U R UR U R R  R R R DR R R U  R R R UR R R U  R U R RR U R U  U R R UR R R R   L D D RD L D D  L L L RD D D L  D D D RD L D D R D R RRL D L LR D D L  D D D DR L D L  L L L LR L L L  R R R DR L L L  L L L LR L L L  L L L LD L L L  L L L LD R D R  L D L LD D L D  L L L LR L L L  L L L LR L L L  D D R LD L L L  L L L LR L L D  L L L LD L L L  L L L LD R L L  L D L LR D L L  L L L LR L L L  L L L LR L L L  R R D RRD R D D  R L D RL L D D  D L L DD D D D  D L D R R L R  R U L RR U U U  R U L RR L L U  L U U LR U U R  R D R RR U U U  R L U UR U U U  U U U UR U U U  U U L LR D D R  L L L RR L L U  U U L RD L U U  L L U UR U R U  D D D DR U U L  U U U LR U U L  U U U LR U L L  L L U RR R R D  L R R LR U R R  R R R LR R U R  R R U UR R R L  R R R RDD D D D  L L D RD D R L  R R D DD R R R  D D L R D L D   U L U  L L L UR L U U  U L L RR U U R  L U U RR D R R  L L L LD L U U  U U L UD L L R  L U U RD R U R  L L L DR L L L  U U L UD L U R  U L L UD U U U  L U L LR R L R  L L U LR U U U  L U U LR U L L  R U L RD U U R  D R L DR U L R  L U R RR R R R  R R R RD R R U  R R U UR R R R   L D D RL L L D  D D D RL D D L  D D R DL D L L D D D DRU U U LR U U U  L L U UR L L L  U U L LR L L U  R R R RR R U U  L R U UR U U U  U U L LR U L L  U U L LR D R R  L U L RR U L L  L L L RR L U U  L L L LD U R R  D D D DD U L L  U U L UR U U U  L L L LR U U L  L U L LR R R R  R R L LR R U L  R R U UR L L R  L L U LD R U U  R D D RRD D D D  R R D RD D L L  D D D DD L L D  D L L R R D D   U L L  L U L LR U U U  L L U UR L L U  U U U UR R R R  L L L LR R U L  L U L LR L L U  L L U LD R U U  R R L RR L L L  L U U RR U U R  U L L UR R R L  R U L LR R D R  L L U LR L R U  U U L LR U L U  U L L UD U U U  D R D DD R L U  R R U UR R R U  U U U UD U L L  U U L UR R L R   D D R RD D L R  D D R RL R L L  R L D RR L D L D D R DRL L L LR D D L  L L D DR D D D  L D D DR L D L  R R R RD L L L  L L L LR L D L  L L L LD L L D  L L L LR R L D  D L L LR D D L  D D L LR L L L  L D L LR L L L  D D R DR L L D  L L D DR L L D  D D L LR D D D  D L D LR R R D  L L D DR L L D  L L D LR D L L  D L D DD L L D  R D D RRD D R D  D D R RL L D L  R D L RR D L R  L L R R D D R   L L R  L U R LD R U U  U L L UD R L L  R L L RR L L R  L L U LR R U U  U U U LD U L L  U U L LR U L L  R R R DR R L R  L U L UD L L R  L L U UD L U U  R U U RR D L D  U U L RR U L L  L U U LR L U L  L L L UR L U R  D R D LD R R R  R R U LR R U R  R U R UD R U U  R R U LR R R R   D D R RD D R D  D R R DR R L L  R R R RD D L D D D D RDU U L UR U L L  L L L UD L L R  L L L LR L U U  R L R LD L U U  R R U LR U U L  U U R RR L L R  R L U RD D R R  L L L UR U U L  R U R RR L U L  L L U UR L R R  R R D LD U U L  U L U UD R U L  U U U UD L U L  U U U RD R L R  L U L LR U L R  R R R RD L R R  R R U UR L R L  R R D LDD L D D  L D D DD D D D  D D D DD D L L  L D L D L L D   U U L  U L L LR L U U  L L L UD L U L  L U L UR R R D  L R U RR R U U  U U U UD U L U  U U U LR U U R  D L D LD L L L  U U R UD U U R  R R U UD U U L  U U R RD L L L  L L L LR U U L  U U U UD L U U  L U U UR L U U  D L R RD L L U  U U U RD R U U  U U R RD L R L  R R U UD L R L   R D L RR R L D  D R L DL L D D  L D D RD D L L R R D LDL U L LR L L R  U U L UD L U U  R L L LR U U U  R R R LD R U L  R L U RD U L R  U L U RD L R U  R U U UR R R R  L L L LR L U R  U U R RD L U U  R R U UR L R U  R R R RD L U L  R R U UD U R U  U U U UD L U L  U U U UD R L L  L L U LR L R U  R R U UD U R R  U U U RR U U R  R R D LDD D R L  D D R DD D R R  R L L DR R L D  L R D D L R L   L L D  L D D DR L D D  D D D DD D D D  L D L LR D L R  L L L LR L L L  L L D LR L L L  L L D LR L L L  D D D LD D D L  D D L LD L D L  L L L LD L L L  L L L LD L L R  L L D DR L L D  L L D DD D L L  D D L DR D D D  D L D LD L L L  L L D LD L L D  D D L LD L L D  L L D DD L L L   L L D RL L L L  L L R DL L L L  L L R RL L R R D L R RDL L R RR U U R  L U L LR L L R  R R L UR U U U  R R R LD U U L  R U U UR U U L  U L L LD U L U  U L U UR R R R  L L R RR L L R  R L R RD U U R  R R L LR R R U  R L R RD R R L  L R U UR R U L  U U U UD L U U  U L U UD R L L  R R U UR R R U  R R U UD U U U  U U U LR U U L  R R D RRD D R L  D R R RD D R R  R L L DR R R R  L R L R D L D   U L L  L L L UR L U U  L U L LR L U L  L U L LD R R L  U L R LR R U R  U U U UD L R R  R R U UR R R L  D L D DR L L U  U U U UD L U R  U R U UD U R L  U R U LR R R R  L U L LR U R L  R R U UD L U L  L L U UR U U L  D L D DR L L U  U U U LR R R L  U U U UR L L L  U U U LR R R R   D D L RL L L L  D D R RL L L L  D D D RL L L D D D D RDL L L UR L L U  U U L LR L L U  U U L LR U U R  R R D LD R U L  R R U UD U R U  U L U UD L L R  U U R LR R L D  L L U UR L U U  U U R RD U U U  U U R RR U U L  D L D DR U U L  U U R UD U U U  R U R RD U U U  R U U LR L L R  U U L RR U U L  U R U UR U R R  L L R RR R U U  D R D LDR R D D  R L D RL D L L  L D D RL D D L  D L L D D D L   L L U  L U U LD U U U  U L L UD U L L  U U L LR L L D  R L L UR R R L  U U U LR L U U  L U U LR U L U  R R D DR U L R  L U R UR U U L  R R U UR L L L  U L U UR D D R  U U L LR L L L  U U U LD L U U  L L R LR L U U  D R D LD U U L  U U U UR U L U  U L R UD L U L  U R R LR D R R   L L L RR R L R  L D R DL D D L  L R D RR L L L D D D LDL L D LR D D D  D L D DD D L L  D D D DR L D D  R L R DR L L L  L L L LR L L D  L D L LR L L L  L L L LD R D L  D D D LR D D D  L D L LR L L L  L L L LR D L L  D D D LD D L D  D D L DD L L D  L L L DD D D L  D D L LD R L L  D D L DR D L L  L L L LD L L L  L L L DR L L D  D L D LDR L D L  D D L DL L L L  D L L DL D D L  L L R R L L R   R U U  R R R RR U U U  R U R RD U R U  U R R UR R R D  R R U RR R R U  R R R RR U U R  U U R UR U R R  D R R LD R U R  U R R UR U R R  R R R RD U R U  U R R UR D L R  U U U UR U R R  R R R RR U U R  U U R RR R U R  D D D LD U R R  U U R RR R R R  R R R RD R U U  R R R UR D L R   D D L DD R R L  D L D DL R L L  D L R RL L R R L L R RRU U U UR U U U  U R R RD U U R  U R R UR U R R  R L R RD R R R  R R U RD R R R  R R U RD R R U  R R U RD L L D  U R U RR R R R  U R U RD R R R  U R U UR R R R  R L D LD U R U  R R U RD R R R  R U U RD R U U  U R U RD L L D  U R U RR R R R  R R U RD R U R  U R U UR R R R  D L D LDD L D D  L L L DD L L L  D L L DD D L L  L L R R L L R   U R U  U U R UR U R R  R U R RD U U R  R R R UR D L D  U R U RR R R R  U R U RD R U R  U R U RR R R R  R R D LD R U R  U R U RD R R R  R R U RD R R U  R R U RD L L D  U R R UR R R R  R R U RD R U R  U R U RR R R R  D L R LD U R R  R R U RD R R R  R R U RD R R U  U R U RR L L R   R L D RR R L L  L L L DD L D L  L L R RD L L R R L D RRU U U RR U R U  R R R RD U U R  U R R UR R R U  D D R RR U R R  R R U RD R R R  R R U RD R R U  R R U UR D L R  U U U RR R R R  U R U RD R R R  U R U UR R R R  D L D LD U R U  R R U RD R R R  R U U RD R R U  U R U UR L L R  U R U UR R R R  U R U UR R R R  U U U UR R U R  R R D LDD D R R  L L R DL L L D  R L R DR R D D  R L D R R L R   L D L  D D D DR D D L  D D L LD L L D  L L D DD R R L  L L L LR L L L  L L D LD L L L  L L D DR L L D  D L D RR D D D  D D D LD L L L  L L L LD L L D  L L D DR L L R  L L D DR L L D  L L L DD D D D  D D L LR D D L  D L D RR D L D  D L D DR L L L  D L L DR D D D  D D D DR R R R   R D L RR L R R  L L D RL D L R  R D R RR R L R R R D RR R R R  U L L RD R L R  L L L UR R U L  R U L UR D R D  L R U LR R U R  U U U UR U U L  R L L UR R U U  D D R LD L L L  L L U RR L L R  U U U UD U R L  R U U LR R R D  L L L LR R R L  R R L UD L U L  L L U UR L U U  D D D LD L L U  R R U UR R U U  U U U UD U U L  L U L LR D L R   D D L DD D R L  D R R DL R L L  R L D RL L D D L L R RRU L R LR L U R  L U U UD L L R  R U L LR L U U  D L R DD U U L  U U R RR U U U  R L R RR R R L  R R L UD D R L  L L L LR U U L  L U R RR L U U  L L L LD U R U  R R R LD R L L  U R L UR R R L  L L U UR U U U  U U L UD R R L  L L L LR U R U  R R L UD L L U  L U L LD U U L  R L D RRL R D D  L D L DD D R R  D D L DD L D D  L L D R L L R   L L L  L L L LD U U L  L L U UD L U R  U U R UR R L D  L L L LR R R L  R L L UR L L U  L L U LD U U R  R R L LD L L U  L L U UR U U U  U U U UR U U R  U U L RD D R L  L L R LR L U U  U U U UR R L L  R U L LD R U U  D R R RD U L U  U U L UD R U U  U L R RD U R R  L R R UR L L R   R D L DR R L L  L R L DL D D D  L D D DD L L L R L D DRL L L LR L L U  U U L UR L L U  U U L LR U U L  R R R RR R L L  R R L UD U R U  U U U UD U U L  L U L LR R L R  L L L UD L U R  U U L RD L L U  L R L UD U R U  D R L DR U L L  L U L LR U U L  L L U LR L U R  L U L LR R R D  U U L LR U R L  L R R UR L L L  L L L LD L L L  R R D RRR R D D  R L D RL D D L  D D D DD R L D  D D D R R L D   L D D  L D D DR L D D  D D L LD D L L  D L L LR D R R  D D D LR L L L  L L D DD D L L  L D L LD L L L  R L L DR D D L  D D L LD L D L  L L L LD L L L  D L L LR L L D  D D L LR D L L  L L L DR L D D  L D L LD D D L  D R D DR D L L  D D L LR L L L  L L L LR L L L  L L D DR D D R   L D D RD L D D  L L D RD D L L  L L D DD L R D R D L LDR R L UR U U L  R U L UD L U U  L L L LR U U U  R L R DR L L L  U U L UD U R L  L L U UD L L L  U U L LR R L D  L L U UR L U U  U U U RD U U U  U U U UR U U U  D L R DD U U L  L L U UR U U L  U U U UR L L U  L U U LR D D D  U U L UR U U U  U R U LD L U R  L L L LR L R L  R L R RRR L D L  R L L RL L R L  R R L DD D D R  L L D R D L R   L L R  L L R UD U U U  R U L RD R L L  R U U RR L L D  U U R RR R U R  U R R UD R R R  R R R RR R R R  D L R DD R L U  U U U UD R L R  L R U UD L R R  U R R UR R L D  R U L LR U L L  R U R LR L R L  L L L RR U L R  D D R LD U R R  U U R RR R R R  R R R UD R R R  L R R LR D L R   L D D DL L D L  D D D DD D D L  D D D RD L L D L L D RRL L L LR L L L  U U L UD L L U  L U L LR L U U  D L R DR U U U  R L R UR U U U  U L L UR U R R  U U R LR R R D  L L U UR L L U  U L R RR L R U  U U R RR U R R  D R D LD L L L  L L R UR R R U  R R R RD L L L  L R U UR D R R  L L L UR L L U  R R U UD L R R  L U R RR R U U  R L R RRR R D L  R D L DL D L L  R D D DD L D D  D L L R R L D   U U U  U U L UR U U U  U L U UR U U L  L U L LR D R R  R R L UR R R U  U U R UD L R U  L L L LR R U L  R R R RR L L R  L L R RR U R R  R R U UR R U L  L U R UR R D R  L U L LR R U L  U U L LR L R U  L L R RR U U R  D R R DR U L L  L R R LR R R R  R R R RR L U L  L R R LR D R R   L D D RD D D R  D D D RD D D D  R R D RD D D D R D R DRD D L LR D L L  L L D LR D D L  L L L DR D L L  R D R RR L L L  L L L LR L L D  L L L LD L D L  L L L LR D L R  D D L LD D D L  L L L LD D L L  L L L LR L L L  L L R RD L L L  L L L DR L L L  L L L DD D D L  L L L LR R L D  L L L LR L L L  L L L LR L L L  L L L LR L L D  R D R RRD L D D  L L D RL L D D  D D L DD D D D  L L R R D L R   L L U  U U L UR R U U  U L L UD U U L  L U L LD D D D  U L L LR R R U  U U U UD L L U  L L L LR U L L  R D R RR L L U  L L U UR L U R  U U U UR U U U  L U U UR D D D  L U L LD U U L  L L U LD L L L  L L L LR L L U  D L D DD L L L  L U L LR U U L  L L L LR L L L  L L L LR R R R   D D L RR R D D  L L L RL D D D  D D D RD D D D R D R RRL L R LD L U U  U U U UD L L R  L U L LR R U U  D R R DD U U R  R R R RR U R U  R R R RD R R R  L R R RR D D D  L L U LR U U U  R L R RD L R U  L R L LR U R L  R L R DR R L L  U U R RR R U L  R R R RD L L R  R L R RR R L D  L L R UR U R R  L R R LR R R R  R R R RR U R L  R R R RRD D D L  L L D RD D D L  D D D DD D D D  L L L R D D D   U L L  L U L LR U U U  L L L LR L L L  L L L LR R R D  U L U UR R R U  U U L LD L L L  U L L LR U L R  R R D DR L L L  L L R RR U R R  R R R UD U R R  L R R RD D R L  L L L LR L L L  L R U UD L R L  L L U LR L L U  D L R DR L L U  R R L LR R R U  L L R RD L R R  L R L LR R R R   D D L RD D D L  D D L DL L D D  D D D RD D L D R R D RRL U L LR L U L  L U L LD L L U  L L L LR L L L  R L R RR L L U  L R L UD U L L  L L U UR L R R  U U U LR R R R  L L R UR L U R  U U R RD R L U  R R L RR R R R  R R D LD L U L  L L R LR U R L  U R U RD L R R  L U R RR R R D  L R L LR R L U  R R L LR L R R  L U U RR R R L  R R D LDD D D D  D D R RD D D D  D D D DD R L D  D D D R D L R   L L L  D D L LD L L D  L L D LD L D D  L L L LR D L D  D L L LR L L L  L L L LR L D L  L L D DR L L L  R D R LD L D L  D L L LR D D L  L L L LD L L L  L L L LR D L D  L L D LR D L D  L L L DR D L D  D D L LR D L L  D D D DD L L L  L L L LR L L L  L L L LD L L L  L L D DR R L R   L L D RD D D D  L L D RL D L L  D L D RL L L R D D D LDU R U RD R R U  R R R UR U U R  R U R UR R U R  R D R RR R R U  U R R RR R R R  R U R RD R U U  R R U UR D L R  U U R RD R U U  U U R RD R R R  R U R RR R R R  L D D LD R R R  R R U UR R R R  R U U RR U U R  U U R UR R R R  R R U UR R R U  R R R RR U U R  U U R UR U U R  R R R LDD L D D  R L D RL L L D  D D D DD D D L  D D R D R R R   R U R  R R R RR R R R  R U R RD R U U  R R R UR D D D  U U R RD R R R  R R R RD U R R  R U U UR R R R  L L R LD R U U  U R R RD R U R  R U R RD R U U  R R R UR L L D  R R U RR R R U  R R U RR U R R  U U R UR U U R  D R D DD R R R  R R U RR R R R  R U R RD U U U  U R R UR D R R   D D L DL L D L  D L D DD D L L  L D D RL L D D D D R RRR U U UR U U U  R U R RR U U U  U U R UR U U R  R R R RR R R R  R R R RR R R R  R U R RR U U R  R R R UR R R R  U R U UR U U R  U R R RD U R R  R R R RR U R R  R L D LD U U R  R R R UR R R R  R R R RD U U U  U R R UR R L R  U U U UR U R R  R R R RD U R R  U U R RR R R R  R L D LDD D D D  R D D RD D L L  D D D DD D L D  D L L D R L L   R R U  R U R RR R R R  R U R RR U U U  U R R UR R R R  R R U RD R R U  R R U UR U R R  U U R UR R R R  R D R LD U R R  U U U UR R R R  R R R RD R R U  U R R RR D L D  U U U UR U R R  R R R RD U R R  U U R RR R R R  D R D DD U R R  U U R RR R R R  R R R RD U R U  R R R UR D L R   D D D DD D D D  L L L DL D R R  R L D RR R D D R D R RRL L L LR D L L  L L D LR L L L  L L D DR L L D  R R R LD L L L  L L L LR L L L  L D L LD L L L  L L L LR R L R  L L L LD D D L  L L L LD L L L  L L L DR L L D  D L R RD L L D  L L L LR L L D  D L D DD D D L  D D L LD R L L  L L L DR L L L  L L L LD L D L  L L L DR D L D  R R D RRD D D L  D L D DL L D D  D L L DD D L D  R L D R L L D   R R L RR U U U  U U R RU L U R  L R L RR R U R R R R LDU R L U  R R R RR U U R  R L U RL L U L  R R U R D R R   U U U RL U R U  U U R RR R R R  U U L RL R U U D D R RDL L L U  L U U RU U U U  L U U DL L R R  U U R R R L D   L U R RL L R U  R R R RR R U U  R U L RU U U L R R R DRD D D D  D D D RL L D R  D R L DD D R R  L R R R D L D  R L L L  L U R RU U R R  L L U RR R U L  L R R R D R D   R U U RR R L R  R U R RR U L R  R U U DR R L R D D D DRL U L U  R U U RL U R R  U U U RL U U U  U R R R R D R   L U U RR R L U  R R U RU U R U  U U R DR U L R D R D DRL U L U  R R R RR R L R  U U R DL R U U  R R U R D D R   D D D RL D D L  D D D RL D L L  L L R RL L R L D D R LD U U L RL U U U  L U U RU L U U  L U R RU U U U R R R LDL U L U  R R U RU U L R  U U U DL U R U  U U R R R R D   U U U RL U R R  U U R RR U L R  R R R DR R L R D D D DRL U R U  U R R RU U R R  R U U RR U L U  U R R R R R D   R U U DR R L R  R R R RR U L R  R R R DL R L R R D L RRD D D D  R R L RL L L L  D D D RR L L R  D L L D D D L  L L L U  L U R RR R U U  L L R DL U U L  R U U R R R R   U U U RR R L R  R R R RU U L R  R R U DU R L U R D L RRL U L U  U U R RR R L R  U U R RL R U R  R R R R D D R   R U U RU R L U  R R U RU U R U  U U R DL U L U D R R RRL U L U  R R U RU R L U  U U U DL U U U  U U U R R L R   D D R RD D R R  L L R RR R R R  R L D RR R D D R R R LDL L L LR D D L  L D D DD L D L  L L D DR L D D  D R R DR L L L  L L L LR L L L  L L L LR L L D  L L D DD R D L  L L D DR D D L  L D L LR D L L  D L D DD D L D  D D R LD L L D  L L D DR L L D  D D D DR D D L  D D L LD R R L  L D D DR D L D  L L L LD D D L  D L D DD D L D  R L D RRD D R R  D L R DL L D D  R D L DR D L R  R L R R R L R  L U L R  R U U DU R U U  L L R DU U U L  R U R R D L D   U U U RL R U U  R U U RU L U R  U U L RU R L L R R R DRL U R R  U U R RR U R R  R R R DR R R R  R R R R D D D   U R U RU U L L  U U U RU L U R  L L R RL R R L D R D DRR R L L  R R R RU R R U  L R R DL L U U  L L U R D D R   L L L RD D R L  L L L RL R L L  D D R RL L R L D D R LD U U R RU U R R  U U R DR L U R  R U L RR R U L D L R DRR R R R  R R R RR R R R  R R R DR R R R  R R R R D D D   R U U RR U U R  U U R DU L U R  U U L RR R R R R D D DRR U L U  R U U RU U R U  L R U DL L U L  L L R R R R D   R R R RR R R U  R R R RR L R U  R R R RL R R L D R R DRD L D D  L L L RD L D D  D D D DD D R D  L L R R D L D  U U L L  U U L RU U U U  L L U DL L U L  L L R R D D D   R R R RR U R R  R U U RU L U R  R L R RR R R L D R R DRL U R L  U U R RL U R R  L R R RR U R U  L R R R D D D   L L U RU U R L  R R R RU R R U  L L L RL R U L D R R RDL L L U  L U R RR R R R  L U R DL U R R  R R U R D D R   D D L RL D D L  D D L DL D D D  L D R RL D L L D R D DR R R R RR U U R  R R R RU L U R  R U L RL R U U D D R LDR R L R  R U L RR R R R  L U R RL L U L  R R U R D D R   U U R RL L R L  R R R DR R R R  R U L RL R U R D L R RRL L L U  L R U RU R U U  L U U DL L R R  U U U R R R R   R U U RL L R U  R U R RR R R U  U U L RU R U L R R R DRD L R R  L L R RL D D D  R D R DR R D D  R D D R R L R   L L L  L L D LR L L L  L D D LD L L D  L L D DR D R D  D L L LR L L L  L L L LR L L L  L L D LR L L D  D D R LD D L L  L D L LR L L L  L L L LD L L D  L L D LR D L R  L L D DR L L D  L L D LD D L D  D D L LR D L L  D R D DR L L L  L L L LR L L L  L L L DD L L D  D L D DR R L R   L D D RL L D D  L L D RD D L L  D L R RD L L R R D D RR L U R DR U U U  L U R RR L U U  R R L RL U U U R R R LDL U L U  R U L RR R R U  L U U DL L U L  U U U D R L R   U U R DL L R U  U U R DR R R U  R R R RL U R R L L R LDL L L U  R U U DU U L U  L U U DL L U L  U U U D R L R   U U U RL U U U  U U U RU L U U  U U L RU U L L R R D DRD D R L  D L R DL L R L  R L R DR R R R  R L L R R L D  L L L U  L U R RU U U R  L L R DL R U L  R U R D D L L   L U R DR U R U  R U R DR R R R  R R R RL U R R L L D LDL U L U  R U R DR U R R  L U R DL R U L  R U U D L L R   R U U DU R R R  R R U DR R R U  L L R RR U R R D L D LDL U R U  R U R DL R R U  R R R DR R R R  R R U R L L R   R D L RL L L L  D L D DR R R R  D R R RL D D R D L R DR R U U RL R U U  R U L RU L U U  L U L RU U U U R R R LDL U R R  R L R RL R U U  L U U DR L U L  U U U D R L R   U U U DL L R R  U U U DU U R U  L R R RL U R U R L D LDU L L U  L U U DU R R R  L U U DL L U L  U U U D R L R   U U U RL R U U  R R R RU L R U  U U L RU R L L R R D DRR R D D  R L D DR L L L  D D D DD D D D  D L D R R L D  L U L U  U U U DU R U U  L L R DU U U L  U U L R L L R   U U U RL U U U  R U U RU L R U  U U L RR R L L R R R DRL U R R  U U U DR R R R  R U R RR R L R  U R R R R D R   U U U RU U R L  R R U RU L R R  L L R RL R L L D R D DRU R L L  U U L RR R L U  L U R RL L U U  L R U R D D R   L L R RR R D L  R L R RL D D L  R L D RL L D L D D R LDL L D DR L L D  L L D LD D D D  D D D DR D D D  R L R DR D L D  L L D LD L L D  D D D DD D D D  D D L DR R L D  D D L LR D D L  D D L LD L L L  L L L LR L L L  D L R LD D D D  D D L DR D L D  D L D DD D D L  D D L LR D D D  D L D DR L L D  L L L DD D L L  D L L DR L L D  R R R RRR D R L  D L L RL L D L  D D L DD D D D  L L R R D L R   U R R  R R R RD U R R  R U R RD U U U  U R R UR D L D  U R U RR R R U  U R R UR U R R  U U R UR R U R  D D R DR U U R  U U R RR R R R  R R R RR U U U  U R R UR D D D  U U U UR U U R  R R U UR U U R  U U R UR R U R  D D R DR R R R  U U R RD R R R  R U R RD R U U  R R U UR D L R   D D L RL L R R  L L L RL R R R  R L R RR R R R R D R RRU U U RR U U R  R R R RD U U R  U U R UR R R R  D L R DR U U R  R R U RR U R R  R U R RD U U R  U R R UR D D D  U R U RR R U R  U R R RD U U R  U U R UR R R R  D L R RR R R R  U U R RR R U R  R U R RD U U R  U U R UR D D D  U R R RR R R U  U R R RD R U R  R U U UR R R R  R D R RRL D R R  L L L RD L D D  R R R DR R R R  L L R R D L D   U R U  U U R RD R R U  R U R RD U U U  R R R UR L L D  U R U UR R U R  U R R UR U U R  U U R UR R U R  D D R RR U U R  U U R RD R U R  R R R RR U U U  U U R RR R D D  U U U UR U U R  R R R RR U U R  U U R UR R U R  D D R RR R U R  U R R RD R R R  R U R RD R R U  R R U UR R L R   R R D RL L D D  R L L DD D D D  D D D RD D D D R L R RRU R U UR R R U  U R R UR U U R  R U R UR U U U  R D R RR R R U  U U R RR R R R  R U R RD R R U  R R U UR R L R  U U R RR R U R  U R R RD U R R  R R U RR U R R  R L D RR R R R  R R U UR R R R  R U R RR U U U  U R U UR R R R  R R U UR U R U  R R U RR U R R  U U R UR U R R  R R R LDR L R R  D L R RR L D R  R R R DR R D D  R R D R R L D   L L D  L L D DR L L L  D D D DD D D D  D D D DD D R D  L L D DR L L L  L L L LD D D D  D D D DR D D D  R R R RR D D L  D D L LR D D L  L L L LD L L L  L L L LR D L D  D D D DR L D D  D L D DD D D D  D D L LR D D L  D D D DR D D L  L L D DR L L L  L L L DD D D D  D L D DR R R R   R D L RD D R R  L L L RL D L L  D R R RR L L R R D D RR';
    $dirs = ['U' => [0, -1], 'L' => [-1, 0], 'R' => [1, 0], 'D' => [0, 1]];

    $numbers = array_merge($goal[2], $goal[3]);

    $result = [];
    $encnum = encode($board, $numbers);
    while ($encnum != 0) {
        list($x, $y) = locationOf(0, $board);
        step($x, $y, $dirs[$data[$encnum]], $board, $result);
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
    fixRow($goal[$y][3], 3, $y, $board, $result, $bwFun);

    return [$result, $board];
}

function dirRLandPackL($y, $board, $goal)
{
    $bwFun = function ($x, $y) {
        return buildWallsUR($x, $y);
    };

    $result = [];
    moveTo($goal[$y][3], 3, $y, $board, $result, $bwFun);
    moveTo($goal[$y][2], 2, $y, $board, $result, $bwFun);
    moveTo($goal[$y][1], 1, $y, $board, $result, $bwFun);
    fixRow($goal[$y][0], 0, $y, $board, $result, $bwFun);

    return [$result, $board];
}

function fixRow($n, $tx, $ty, &$board, &$result, $buildWallsFun)
{
    $bwFunFix = function ($x, $y) use ($buildWallsFun) {
        return $buildWallsFun($x, $y - 2);
    };

    list($sx, $sy) = locationOf(0, $board);
    if ($sx == $tx && $sy == $ty) {
        step($sx, $sy, [0, 1], $board, $result);
    }
    if ($board[$ty][$tx] != $n) {
        moveTo($n, $tx, $ty + 2, $board, $result, $bwFunFix);
        $walls = $buildWallsFun($tx, $ty);
        $walls[$ty + 2][$tx] = 1;
        if (moveSpaceTo($tx, $ty, $board, $walls, $result) === false) {
            return false;
        }
        list($sx, $sy) = [$tx, $ty];
        $moves = [
            [$tx == 3 ? -1 : 1, 0],
            [0, 1],
            [$tx == 3 ? 1 : -1, 0],
            [0, 1],
            [$tx == 3 ? -1 : 1, 0],
            [0, -1],
            [0, -1],
            [$tx == 3 ? 1 : -1, 0],
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

function dirRL2($y, $board, $goal)
{
    $bwFun1 = function ($x, $y) {
        return buildWallsUR($x, $y);
    };

    $bwFun2 = function ($x, $y) {
        return buildWallsUR($x - 1, $y - 1);
    };

    $bwFun3 = function ($x, $y) {
        $walls = buildWallsUR($x, $y);
        $walls[$y + 1][3] = 1;
        return $walls;
    };

    $result = [];
    moveTo($goal[$y][2], 3, $y, $board, $result, $bwFun1);
    moveTo2($goal[$y][3], 3, $y + 1, $board, $result, $bwFun2);
    if (moveTo($goal[$y][1], 2, $y, $board, $result, $bwFun3) === false) {
        return false;
    }
    if (moveTo($goal[$y][0], 1, $y, $board, $result, $bwFun3) === false) {
        return false;
    }
    if (moveSpaceTo(0, $y, $board, $bwFun3(0, $y), $result) === false) {
        return false;
    }
    list($sx, $sy) = [0, $y];
    $moves = [[1, 0], [1, 0], [1, 0], [0, 1]];
    foreach ($moves as $m) {
        step($sx, $sy, $m, $board, $result);
    }

    return [$result, $board];
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

function buildWallsUR($x, $y)
{
    $walls = emptyWalls();
    for ($i = 0; $i < $y; ++$i) {
        $walls[$i] = [1, 1, 1, 1];
    }
    for ($i = 3; $i > $x; --$i) {
        $walls[$y][$i] = 1;
    }
    // array_splice($walls, 0, $y, array_fill(0, $y, [1, 1, 1, 1]));
    // array_splice($walls[$y], $x + 1, 3 - $x, array_fill(0, 3 - $x, 1));

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
