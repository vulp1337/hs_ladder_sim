<?php
/**
 * Vulp's Hearthstone Ladder Simulation
 *
 * This program simulates every permutation of wins and losses for a set number
 * of games and maps the results to their corresponding Hearthstone ladder rank.
 * The algorithm works by looking at the previous game, slicing every value in 
 * half, and mapping it to two new values (one for a win, one for a loss) 
 * according to Hearthstone ladder rules.  The values from game to game are 100%
 * precise by using the BCMath library.
 *
 * PHP version 5.5.12
 *
 * LICENSE: Copyright (c) 2014 vulp (vulp1337@gmail.com)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Change these if you like 
$number_of_games = 1205;
$output_file = './results.json';

// Debug settings
$time_start = microtime(true);
//ini_set('memory_limit','2G');
//ini_set('max_execution_time', 300);
//ini_set('display_errors', '1');
//ini_set('error_reporting', E_ALL);

// Usage of BCMath library:
// For arbitrary precision mathematics PHP offers the Binary Calculator which 
// supports numbers of any size and precision, represented as strings.  Since we
// will be dividing by 2 every game, the values need to be stored as strings and
// the precision needs to be set to the number of games.
bcscale($number_of_games);

// The "values" array - 4 dimensions, all string
// $values[game][rank][star][streak]
$streaks = array_fill(0, 3, '0.0');
$stars = array_fill(0, 6, $streaks);
$ranks = array_fill(0, 26, $stars);
$values = array_fill(0, $number_of_games + 1, $ranks);

// The "results" array - 2 dimensions - all float
// $results[game][rank]
$groups = array_fill(0, 26, 0.0);
$results = array_fill(1, $number_of_games, $groups);

// Initialize Game 0:
// 100% of players are Rank 25, Star 0, Streak 0
$values[0][25][0][0] = '1.0';

// The "slicer" loop
// bcdiv(x, 2) means x/2
// bcadd(x, y) means x+y
for ($g = 1; $g <= $number_of_games; $g++) {
    // Rank 0 - Legend
    // Note: $values[$g-1][0][0][0] is the only value that doesn't get sliced in 2 because 
    //       a Legend player will stay Legend with a win OR a loss.
    $values[$g][0][0][0] = bcadd($values[$g-1][0][0][0], bcdiv($values[$g-1][1][5][0], 2));
    
    $results[$g][0] = floatval($values[$g][0][0][0]);
    
    // Rank 1 - Innkeeper
    $values[$g][1][5][0] = bcdiv($values[$g-1][1][4][0], 2);
    $values[$g][1][4][0] = bcadd(bcdiv($values[$g-1][1][5][0], 2), bcdiv($values[$g-1][1][3][0], 2));
    $values[$g][1][3][0] = bcadd(bcdiv($values[$g-1][1][4][0], 2), bcdiv($values[$g-1][1][2][0], 2));
    $values[$g][1][2][0] = bcadd(bcdiv($values[$g-1][1][3][0], 2), bcdiv($values[$g-1][1][1][0], 2));
    $values[$g][1][1][0] = bcadd(bcdiv($values[$g-1][1][2][0], 2), bcadd(bcdiv($values[$g-1][1][0][0], 2), bcdiv($values[$g-1][2][5][0], 2)));
    $values[$g][1][0][0] = bcdiv($values[$g-1][1][1][0], 2);
    
    $results[$g][1] = floatval(
        bcadd($values[$g][1][5][0],
        bcadd($values[$g][1][4][0],
        bcadd($values[$g][1][3][0],
        bcadd($values[$g][1][2][0],
        bcadd($values[$g][1][1][0],
              $values[$g][1][0][0]))))));
    
    // Rank 2 - The Black Knight
    $values[$g][2][5][0] = bcdiv($values[$g-1][2][4][0], 2);
    $values[$g][2][4][0] = bcadd(bcdiv($values[$g-1][2][5][0], 2), bcadd(bcdiv($values[$g-1][2][3][0], 2), bcdiv($values[$g-1][1][0][0], 2)));
    $values[$g][2][3][0] = bcadd(bcdiv($values[$g-1][2][4][0], 2), bcdiv($values[$g-1][2][2][0], 2));
    $values[$g][2][2][0] = bcadd(bcdiv($values[$g-1][2][3][0], 2), bcdiv($values[$g-1][2][1][0], 2));
    $values[$g][2][1][0] = bcadd(bcdiv($values[$g-1][2][2][0], 2), bcadd(bcdiv($values[$g-1][2][0][0], 2), bcdiv($values[$g-1][3][5][0], 2)));
    $values[$g][2][0][0] = bcdiv($values[$g-1][2][1][0], 2);
    
    $results[$g][2] = floatval(
        bcadd($values[$g][2][5][0],
        bcadd($values[$g][2][4][0],
        bcadd($values[$g][2][3][0],
        bcadd($values[$g][2][2][0],
        bcadd($values[$g][2][1][0],
              $values[$g][2][0][0]))))));
    
    // Rank 3 - Molten Giant
    $values[$g][3][5][0] = bcdiv($values[$g-1][3][4][0], 2);
    $values[$g][3][4][0] = bcadd(bcdiv($values[$g-1][3][5][0], 2), bcadd(bcdiv($values[$g-1][3][3][0], 2), bcdiv($values[$g-1][2][0][0], 2)));
    $values[$g][3][3][0] = bcadd(bcdiv($values[$g-1][3][4][0], 2), bcdiv($values[$g-1][3][2][0], 2));
    $values[$g][3][2][0] = bcadd(bcdiv($values[$g-1][3][3][0], 2), bcdiv($values[$g-1][3][1][0], 2));
    $values[$g][3][1][0] = bcadd(bcdiv($values[$g-1][3][2][0], 2), bcadd(bcdiv($values[$g-1][3][0][0], 2), bcdiv($values[$g-1][4][5][0], 2)));
    $values[$g][3][0][0] = bcdiv($values[$g-1][3][1][0], 2);
    
    $results[$g][3] = floatval(
        bcadd($values[$g][3][5][0],
        bcadd($values[$g][3][4][0],
        bcadd($values[$g][3][3][0],
        bcadd($values[$g][3][2][0],
        bcadd($values[$g][3][1][0],
              $values[$g][3][0][0]))))));
    
    
    // Rank 4 - Mountain Giant
    $values[$g][4][5][0] = bcdiv($values[$g-1][4][4][0], 2);
    $values[$g][4][4][0] = bcadd(bcdiv($values[$g-1][4][5][0], 2), bcadd(bcdiv($values[$g-1][4][3][0], 2), bcdiv($values[$g-1][3][0][0], 2)));
    $values[$g][4][3][0] = bcadd(bcdiv($values[$g-1][4][4][0], 2), bcdiv($values[$g-1][4][2][0], 2));
    $values[$g][4][2][0] = bcadd(bcdiv($values[$g-1][4][3][0], 2), bcdiv($values[$g-1][4][1][0], 2));
    $values[$g][4][1][0] = bcadd(bcdiv($values[$g-1][4][2][0], 2), bcadd(bcdiv($values[$g-1][4][0][0], 2), bcdiv($values[$g-1][5][5][0], 2)));
    $values[$g][4][0][0] = bcdiv($values[$g-1][4][1][0], 2);
    
    $results[$g][4] = floatval(
        bcadd($values[$g][4][5][0],
        bcadd($values[$g][4][4][0],
        bcadd($values[$g][4][3][0],
        bcadd($values[$g][4][2][0],
        bcadd($values[$g][4][1][0],
              $values[$g][4][0][0]))))));
    
    // Rank 5 - Sea Giant
    $values[$g][5][5][0] = bcdiv($values[$g-1][5][4][0], 2);
    $values[$g][5][4][0] = bcadd(bcdiv($values[$g-1][5][5][0], 2), bcadd(bcdiv($values[$g-1][5][3][0], 2), bcdiv($values[$g-1][4][0][0], 2)));
    $values[$g][5][3][0] = bcadd(bcdiv($values[$g-1][5][4][0], 2), bcdiv($values[$g-1][5][2][0], 2));
    $values[$g][5][2][0] = bcadd(bcdiv($values[$g-1][5][3][0], 2), bcdiv($values[$g-1][5][1][0], 2));
    $values[$g][5][1][0] = bcadd(bcdiv($values[$g-1][5][2][0], 2), bcadd(bcdiv($values[$g-1][5][0][0], 2), bcadd(bcdiv($values[$g-1][6][5][2], 2), bcadd(bcdiv($values[$g-1][6][5][1], 2), bcdiv($values[$g-1][6][4][2], 2)))));
    $values[$g][5][0][0] = bcdiv($values[$g-1][5][1][0], 2);
    
    $results[$g][5] = floatval(
        bcadd($values[$g][5][5][0],
        bcadd($values[$g][5][4][0],
        bcadd($values[$g][5][3][0],
        bcadd($values[$g][5][2][0],
        bcadd($values[$g][5][1][0],
              $values[$g][5][0][0]))))));
    
    // Rank 6 - Ancient of War
    $values[$g][6][5][2] = bcadd(bcdiv($values[$g-1][6][4][1], 2), bcdiv($values[$g-1][6][3][2], 2));
    $values[$g][6][5][1] = bcdiv($values[$g-1][6][4][0], 2);
    $values[$g][6][4][2] = bcadd(bcdiv($values[$g-1][6][3][1], 2), bcdiv($values[$g-1][6][2][2], 2));
    $values[$g][6][4][1] = bcdiv($values[$g-1][6][3][0], 2);
    $values[$g][6][4][0] = bcadd(bcdiv($values[$g-1][6][5][2], 2), bcadd(bcdiv($values[$g-1][6][5][1], 2), bcdiv($values[$g-1][5][0][0], 2)));
    $values[$g][6][3][2] = bcadd(bcdiv($values[$g-1][6][2][1], 2), bcdiv($values[$g-1][6][1][2], 2));
    $values[$g][6][3][1] = bcdiv($values[$g-1][6][2][0], 2);
    $values[$g][6][3][0] = bcadd(bcdiv($values[$g-1][6][4][2], 2), bcadd(bcdiv($values[$g-1][6][4][1], 2), bcdiv($values[$g-1][6][4][0], 2)));
    $values[$g][6][2][2] = bcadd(bcdiv($values[$g-1][6][1][1], 2), bcdiv($values[$g-1][7][5][2], 2));
    $values[$g][6][2][1] = bcdiv($values[$g-1][6][1][0], 2);
    $values[$g][6][2][0] = bcadd(bcdiv($values[$g-1][6][3][2], 2), bcadd(bcdiv($values[$g-1][6][3][1], 2), bcdiv($values[$g-1][6][3][0], 2)));
    $values[$g][6][1][2] = bcadd(bcdiv($values[$g-1][7][5][1], 2), bcdiv($values[$g-1][7][4][2], 2));
    $values[$g][6][1][1] = bcdiv($values[$g-1][6][0][0], 2);
    $values[$g][6][1][0] = bcadd(bcdiv($values[$g-1][6][2][2], 2), bcadd(bcdiv($values[$g-1][6][2][1], 2), bcdiv($values[$g-1][6][2][0], 2)));
    $values[$g][6][0][0] = bcadd(bcdiv($values[$g-1][6][1][2], 2), bcadd(bcdiv($values[$g-1][6][1][1], 2), bcdiv($values[$g-1][6][1][0], 2)));
    
    $results[$g][6] = floatval(
        bcadd($values[$g][6][5][2],
        bcadd($values[$g][6][5][1],
        bcadd($values[$g][6][4][2],
        bcadd($values[$g][6][4][1],
        bcadd($values[$g][6][4][0],
        bcadd($values[$g][6][3][2],
        bcadd($values[$g][6][3][1],
        bcadd($values[$g][6][3][0],
        bcadd($values[$g][6][2][2],
        bcadd($values[$g][6][2][1],
        bcadd($values[$g][6][2][0],
        bcadd($values[$g][6][1][2],
        bcadd($values[$g][6][1][1],
        bcadd($values[$g][6][1][0],
              $values[$g][6][0][0])))))))))))))));
    
    // Rank 7 - Sunwalker
    $values[$g][7][5][2] = bcadd(bcdiv($values[$g-1][7][4][1], 2), bcdiv($values[$g-1][7][3][2], 2));
    $values[$g][7][5][1] = bcdiv($values[$g-1][7][4][0], 2);
    $values[$g][7][4][2] = bcadd(bcdiv($values[$g-1][7][3][1], 2), bcdiv($values[$g-1][7][2][2], 2));
    $values[$g][7][4][1] = bcdiv($values[$g-1][7][3][0], 2);
    $values[$g][7][4][0] = bcadd(bcdiv($values[$g-1][7][5][2], 2), bcadd(bcdiv($values[$g-1][7][5][1], 2), bcdiv($values[$g-1][6][0][0], 2)));
    $values[$g][7][3][2] = bcadd(bcdiv($values[$g-1][7][2][1], 2), bcdiv($values[$g-1][7][1][2], 2));
    $values[$g][7][3][1] = bcdiv($values[$g-1][7][2][0], 2);
    $values[$g][7][3][0] = bcadd(bcdiv($values[$g-1][7][4][2], 2), bcadd(bcdiv($values[$g-1][7][4][1], 2), bcdiv($values[$g-1][7][4][0], 2)));
    $values[$g][7][2][2] = bcadd(bcdiv($values[$g-1][7][1][1], 2), bcdiv($values[$g-1][8][5][2], 2));
    $values[$g][7][2][1] = bcdiv($values[$g-1][7][1][0], 2);
    $values[$g][7][2][0] = bcadd(bcdiv($values[$g-1][7][3][2], 2), bcadd(bcdiv($values[$g-1][7][3][1], 2), bcdiv($values[$g-1][7][3][0], 2)));
    $values[$g][7][1][2] = bcadd(bcdiv($values[$g-1][8][5][1], 2), bcdiv($values[$g-1][8][4][2], 2));
    $values[$g][7][1][1] = bcdiv($values[$g-1][7][0][0], 2);
    $values[$g][7][1][0] = bcadd(bcdiv($values[$g-1][7][2][2], 2), bcadd(bcdiv($values[$g-1][7][2][1], 2), bcdiv($values[$g-1][7][2][0], 2)));
    $values[$g][7][0][0] = bcadd(bcdiv($values[$g-1][7][1][2], 2), bcadd(bcdiv($values[$g-1][7][1][1], 2), bcdiv($values[$g-1][7][1][0], 2)));
    
    $results[$g][7] = floatval(
        bcadd($values[$g][7][5][2],
        bcadd($values[$g][7][5][1],
        bcadd($values[$g][7][4][2],
        bcadd($values[$g][7][4][1],
        bcadd($values[$g][7][4][0],
        bcadd($values[$g][7][3][2],
        bcadd($values[$g][7][3][1],
        bcadd($values[$g][7][3][0],
        bcadd($values[$g][7][2][2],
        bcadd($values[$g][7][2][1],
        bcadd($values[$g][7][2][0],
        bcadd($values[$g][7][1][2],
        bcadd($values[$g][7][1][1],
        bcadd($values[$g][7][1][0],
              $values[$g][7][0][0])))))))))))))));
    
    // Rank 8 - Frostwolf Warlord
    $values[$g][8][5][2] = bcadd(bcdiv($values[$g-1][8][4][1], 2), bcdiv($values[$g-1][8][3][2], 2));
    $values[$g][8][5][1] = bcdiv($values[$g-1][8][4][0], 2);
    $values[$g][8][4][2] = bcadd(bcdiv($values[$g-1][8][3][1], 2), bcdiv($values[$g-1][8][2][2], 2));
    $values[$g][8][4][1] = bcdiv($values[$g-1][8][3][0], 2);
    $values[$g][8][4][0] = bcadd(bcdiv($values[$g-1][8][5][2], 2), bcadd(bcdiv($values[$g-1][8][5][1], 2), bcdiv($values[$g-1][7][0][0], 2)));
    $values[$g][8][3][2] = bcadd(bcdiv($values[$g-1][8][2][1], 2), bcdiv($values[$g-1][8][1][2], 2));
    $values[$g][8][3][1] = bcdiv($values[$g-1][8][2][0], 2);
    $values[$g][8][3][0] = bcadd(bcdiv($values[$g-1][8][4][2], 2), bcadd(bcdiv($values[$g-1][8][4][1], 2), bcdiv($values[$g-1][8][4][0], 2)));
    $values[$g][8][2][2] = bcadd(bcdiv($values[$g-1][8][1][1], 2), bcdiv($values[$g-1][9][5][2], 2));
    $values[$g][8][2][1] = bcdiv($values[$g-1][8][1][0], 2);
    $values[$g][8][2][0] = bcadd(bcdiv($values[$g-1][8][3][2], 2), bcadd(bcdiv($values[$g-1][8][3][1], 2), bcdiv($values[$g-1][8][3][0], 2)));
    $values[$g][8][1][2] = bcadd(bcdiv($values[$g-1][9][5][1], 2), bcdiv($values[$g-1][9][4][2], 2));
    $values[$g][8][1][1] = bcdiv($values[$g-1][8][0][0], 2);
    $values[$g][8][1][0] = bcadd(bcdiv($values[$g-1][8][2][2], 2), bcadd(bcdiv($values[$g-1][8][2][1], 2), bcdiv($values[$g-1][8][2][0], 2)));
    $values[$g][8][0][0] = bcadd(bcdiv($values[$g-1][8][1][2], 2), bcadd(bcdiv($values[$g-1][8][1][1], 2), bcdiv($values[$g-1][8][1][0], 2)));
    
    $results[$g][8] = floatval(
        bcadd($values[$g][8][5][2],
        bcadd($values[$g][8][5][1],
        bcadd($values[$g][8][4][2],
        bcadd($values[$g][8][4][1],
        bcadd($values[$g][8][4][0],
        bcadd($values[$g][8][3][2],
        bcadd($values[$g][8][3][1],
        bcadd($values[$g][8][3][0],
        bcadd($values[$g][8][2][2],
        bcadd($values[$g][8][2][1],
        bcadd($values[$g][8][2][0],
        bcadd($values[$g][8][1][2],
        bcadd($values[$g][8][1][1],
        bcadd($values[$g][8][1][0],
              $values[$g][8][0][0])))))))))))))));
    
    // Rank 9 - Silver Hand Knight
    $values[$g][9][5][2] = bcadd(bcdiv($values[$g-1][9][4][1], 2), bcdiv($values[$g-1][9][3][2], 2));
    $values[$g][9][5][1] = bcdiv($values[$g-1][9][4][0], 2);
    $values[$g][9][4][2] = bcadd(bcdiv($values[$g-1][9][3][1], 2), bcdiv($values[$g-1][9][2][2], 2));
    $values[$g][9][4][1] = bcdiv($values[$g-1][9][3][0], 2);
    $values[$g][9][4][0] = bcadd(bcdiv($values[$g-1][9][5][2], 2), bcadd(bcdiv($values[$g-1][9][5][1], 2), bcdiv($values[$g-1][8][0][0], 2)));
    $values[$g][9][3][2] = bcadd(bcdiv($values[$g-1][9][2][1], 2), bcdiv($values[$g-1][9][1][2], 2));
    $values[$g][9][3][1] = bcdiv($values[$g-1][9][2][0], 2);
    $values[$g][9][3][0] = bcadd(bcdiv($values[$g-1][9][4][2], 2), bcadd(bcdiv($values[$g-1][9][4][1], 2), bcdiv($values[$g-1][9][4][0], 2)));
    $values[$g][9][2][2] = bcadd(bcdiv($values[$g-1][9][1][1], 2), bcdiv($values[$g-1][10][5][2], 2));
    $values[$g][9][2][1] = bcdiv($values[$g-1][9][1][0], 2);
    $values[$g][9][2][0] = bcadd(bcdiv($values[$g-1][9][3][2], 2), bcadd(bcdiv($values[$g-1][9][3][1], 2), bcdiv($values[$g-1][9][3][0], 2)));
    $values[$g][9][1][2] = bcadd(bcdiv($values[$g-1][10][5][1], 2), bcdiv($values[$g-1][10][4][2], 2));
    $values[$g][9][1][1] = bcdiv($values[$g-1][9][0][0], 2);
    $values[$g][9][1][0] = bcadd(bcdiv($values[$g-1][9][2][2], 2), bcadd(bcdiv($values[$g-1][9][2][1], 2), bcdiv($values[$g-1][9][2][0], 2)));
    $values[$g][9][0][0] = bcadd(bcdiv($values[$g-1][9][1][2], 2), bcadd(bcdiv($values[$g-1][9][1][1], 2), bcdiv($values[$g-1][9][1][0], 2)));
    
    $results[$g][9] = floatval(
        bcadd($values[$g][9][5][2],
        bcadd($values[$g][9][5][1],
        bcadd($values[$g][9][4][2],
        bcadd($values[$g][9][4][1],
        bcadd($values[$g][9][4][0],
        bcadd($values[$g][9][3][2],
        bcadd($values[$g][9][3][1],
        bcadd($values[$g][9][3][0],
        bcadd($values[$g][9][2][2],
        bcadd($values[$g][9][2][1],
        bcadd($values[$g][9][2][0],
        bcadd($values[$g][9][1][2],
        bcadd($values[$g][9][1][1],
        bcadd($values[$g][9][1][0],
              $values[$g][9][0][0])))))))))))))));
    
    // Rank 10 - Ogre Magi
    $values[$g][10][5][2] = bcadd(bcdiv($values[$g-1][10][4][1], 2), bcdiv($values[$g-1][10][3][2], 2));
    $values[$g][10][5][1] = bcdiv($values[$g-1][10][4][0], 2);
    $values[$g][10][4][2] = bcadd(bcdiv($values[$g-1][10][3][1], 2), bcdiv($values[$g-1][10][2][2], 2));
    $values[$g][10][4][1] = bcdiv($values[$g-1][10][3][0], 2);
    $values[$g][10][4][0] = bcadd(bcdiv($values[$g-1][10][5][2], 2), bcadd(bcdiv($values[$g-1][10][5][1], 2), bcdiv($values[$g-1][9][0][0], 2)));
    $values[$g][10][3][2] = bcadd(bcdiv($values[$g-1][10][2][1], 2), bcdiv($values[$g-1][10][1][2], 2));
    $values[$g][10][3][1] = bcdiv($values[$g-1][10][2][0], 2);
    $values[$g][10][3][0] = bcadd(bcdiv($values[$g-1][10][4][2], 2), bcadd(bcdiv($values[$g-1][10][4][1], 2), bcdiv($values[$g-1][10][4][0], 2)));
    $values[$g][10][2][2] = bcadd(bcdiv($values[$g-1][10][1][1], 2), bcdiv($values[$g-1][11][4][2], 2));
    $values[$g][10][2][1] = bcdiv($values[$g-1][10][1][0], 2);
    $values[$g][10][2][0] = bcadd(bcdiv($values[$g-1][10][3][2], 2), bcadd(bcdiv($values[$g-1][10][3][1], 2), bcdiv($values[$g-1][10][3][0], 2)));
    $values[$g][10][1][2] = bcadd(bcdiv($values[$g-1][11][4][1], 2), bcdiv($values[$g-1][11][3][2], 2));
    $values[$g][10][1][1] = bcdiv($values[$g-1][10][0][0], 2);
    $values[$g][10][1][0] = bcadd(bcdiv($values[$g-1][10][2][2], 2), bcadd(bcdiv($values[$g-1][10][2][1], 2), bcdiv($values[$g-1][10][2][0], 2)));
    $values[$g][10][0][0] = bcadd(bcdiv($values[$g-1][10][1][2], 2), bcadd(bcdiv($values[$g-1][10][1][1], 2), bcdiv($values[$g-1][10][1][0], 2)));
    
    $results[$g][10] = floatval(
        bcadd($values[$g][10][5][2],
        bcadd($values[$g][10][5][1],
        bcadd($values[$g][10][4][2],
        bcadd($values[$g][10][4][1],
        bcadd($values[$g][10][4][0],
        bcadd($values[$g][10][3][2],
        bcadd($values[$g][10][3][1],
        bcadd($values[$g][10][3][0],
        bcadd($values[$g][10][2][2],
        bcadd($values[$g][10][2][1],
        bcadd($values[$g][10][2][0],
        bcadd($values[$g][10][1][2],
        bcadd($values[$g][10][1][1],
        bcadd($values[$g][10][1][0],
              $values[$g][10][0][0])))))))))))))));
    
    // Rank 11 - Big Game Hunter
    $values[$g][11][4][2] = bcadd(bcdiv($values[$g-1][11][3][1], 2), bcdiv($values[$g-1][11][2][2], 2));
    $values[$g][11][4][1] = bcdiv($values[$g-1][11][3][0], 2);
    $values[$g][11][3][2] = bcadd(bcdiv($values[$g-1][11][2][1], 2), bcdiv($values[$g-1][11][1][2], 2));
    $values[$g][11][3][1] = bcdiv($values[$g-1][11][2][0], 2);
    $values[$g][11][3][0] = bcadd(bcdiv($values[$g-1][11][4][2], 2), bcadd(bcdiv($values[$g-1][11][4][1], 2), bcdiv($values[$g-1][10][0][0], 2)));
    $values[$g][11][2][2] = bcadd(bcdiv($values[$g-1][11][1][1], 2), bcdiv($values[$g-1][12][4][2], 2));
    $values[$g][11][2][1] = bcdiv($values[$g-1][11][1][0], 2);
    $values[$g][11][2][0] = bcadd(bcdiv($values[$g-1][11][3][2], 2), bcadd(bcdiv($values[$g-1][11][3][1], 2), bcdiv($values[$g-1][11][3][0], 2)));
    $values[$g][11][1][2] = bcadd(bcdiv($values[$g-1][12][4][1], 2), bcdiv($values[$g-1][12][3][2], 2));
    $values[$g][11][1][1] = bcdiv($values[$g-1][11][0][0], 2);
    $values[$g][11][1][0] = bcadd(bcdiv($values[$g-1][11][2][2], 2), bcadd(bcdiv($values[$g-1][11][2][1], 2), bcdiv($values[$g-1][11][2][0], 2)));
    $values[$g][11][0][0] = bcadd(bcdiv($values[$g-1][11][1][2], 2), bcadd(bcdiv($values[$g-1][11][1][1], 2), bcdiv($values[$g-1][11][1][0], 2)));
    
    $results[$g][11] = floatval(
        bcadd($values[$g][11][4][2],
        bcadd($values[$g][11][4][1],
        bcadd($values[$g][11][3][2],
        bcadd($values[$g][11][3][1],
        bcadd($values[$g][11][3][0],
        bcadd($values[$g][11][2][2],
        bcadd($values[$g][11][2][1],
        bcadd($values[$g][11][2][0],
        bcadd($values[$g][11][1][2],
        bcadd($values[$g][11][1][1],
        bcadd($values[$g][11][1][0],
              $values[$g][11][0][0]))))))))))));
    
    // Rank 12 - Warsong Commander
    $values[$g][12][4][2] = bcadd(bcdiv($values[$g-1][12][3][1], 2), bcdiv($values[$g-1][12][2][2], 2));
    $values[$g][12][4][1] = bcdiv($values[$g-1][12][3][0], 2);
    $values[$g][12][3][2] = bcadd(bcdiv($values[$g-1][12][2][1], 2), bcdiv($values[$g-1][12][1][2], 2));
    $values[$g][12][3][1] = bcdiv($values[$g-1][12][2][0], 2);
    $values[$g][12][3][0] = bcadd(bcdiv($values[$g-1][12][4][2], 2), bcadd(bcdiv($values[$g-1][12][4][1], 2), bcdiv($values[$g-1][11][0][0], 2)));
    $values[$g][12][2][2] = bcadd(bcdiv($values[$g-1][12][1][1], 2), bcdiv($values[$g-1][13][4][2], 2));
    $values[$g][12][2][1] = bcdiv($values[$g-1][12][1][0], 2);
    $values[$g][12][2][0] = bcadd(bcdiv($values[$g-1][12][3][2], 2), bcadd(bcdiv($values[$g-1][12][3][1], 2), bcdiv($values[$g-1][12][3][0], 2)));
    $values[$g][12][1][2] = bcadd(bcdiv($values[$g-1][13][4][1], 2), bcdiv($values[$g-1][13][3][2], 2));
    $values[$g][12][1][1] = bcdiv($values[$g-1][12][0][0], 2);
    $values[$g][12][1][0] = bcadd(bcdiv($values[$g-1][12][2][2], 2), bcadd(bcdiv($values[$g-1][12][2][1], 2), bcdiv($values[$g-1][12][2][0], 2)));
    $values[$g][12][0][0] = bcadd(bcdiv($values[$g-1][12][1][2], 2), bcadd(bcdiv($values[$g-1][12][1][1], 2), bcdiv($values[$g-1][12][1][0], 2)));
    
    $results[$g][12] = floatval(
        bcadd($values[$g][12][4][2],
        bcadd($values[$g][12][4][1],
        bcadd($values[$g][12][3][2],
        bcadd($values[$g][12][3][1],
        bcadd($values[$g][12][3][0],
        bcadd($values[$g][12][2][2],
        bcadd($values[$g][12][2][1],
        bcadd($values[$g][12][2][0],
        bcadd($values[$g][12][1][2],
        bcadd($values[$g][12][1][1],
        bcadd($values[$g][12][1][0],
              $values[$g][12][0][0]))))))))))));
    
    // Rank 13 -Dread Corsair
    $values[$g][13][4][2] = bcadd(bcdiv($values[$g-1][13][3][1], 2), bcdiv($values[$g-1][13][2][2], 2));
    $values[$g][13][4][1] = bcdiv($values[$g-1][13][3][0], 2);
    $values[$g][13][3][2] = bcadd(bcdiv($values[$g-1][13][2][1], 2), bcdiv($values[$g-1][13][1][2], 2));
    $values[$g][13][3][1] = bcdiv($values[$g-1][13][2][0], 2);
    $values[$g][13][3][0] = bcadd(bcdiv($values[$g-1][13][4][2], 2), bcadd(bcdiv($values[$g-1][13][4][1], 2), bcdiv($values[$g-1][12][0][0], 2)));
    $values[$g][13][2][2] = bcadd(bcdiv($values[$g-1][13][1][1], 2), bcdiv($values[$g-1][14][4][2], 2));
    $values[$g][13][2][1] = bcdiv($values[$g-1][13][1][0], 2);
    $values[$g][13][2][0] = bcadd(bcdiv($values[$g-1][13][3][2], 2), bcadd(bcdiv($values[$g-1][13][3][1], 2), bcdiv($values[$g-1][13][3][0], 2)));
    $values[$g][13][1][2] = bcadd(bcdiv($values[$g-1][14][4][1], 2), bcdiv($values[$g-1][14][3][2], 2));
    $values[$g][13][1][1] = bcdiv($values[$g-1][13][0][0], 2);
    $values[$g][13][1][0] = bcadd(bcdiv($values[$g-1][13][2][2], 2), bcadd(bcdiv($values[$g-1][13][2][1], 2), bcdiv($values[$g-1][13][2][0], 2)));
    $values[$g][13][0][0] = bcadd(bcdiv($values[$g-1][13][1][2], 2), bcadd(bcdiv($values[$g-1][13][1][1], 2), bcdiv($values[$g-1][13][1][0], 2)));
    
    $results[$g][13] = floatval(
        bcadd($values[$g][13][4][2],
        bcadd($values[$g][13][4][1],
        bcadd($values[$g][13][3][2],
        bcadd($values[$g][13][3][1],
        bcadd($values[$g][13][3][0],
        bcadd($values[$g][13][2][2],
        bcadd($values[$g][13][2][1],
        bcadd($values[$g][13][2][0],
        bcadd($values[$g][13][1][2],
        bcadd($values[$g][13][1][1],
        bcadd($values[$g][13][1][0],
              $values[$g][13][0][0]))))))))))));
    
    // Rank 14 - Raid Leader
    $values[$g][14][4][2] = bcadd(bcdiv($values[$g-1][14][3][1], 2), bcdiv($values[$g-1][14][2][2], 2));
    $values[$g][14][4][1] = bcdiv($values[$g-1][14][3][0], 2);
    $values[$g][14][3][2] = bcadd(bcdiv($values[$g-1][14][2][1], 2), bcdiv($values[$g-1][14][1][2], 2));
    $values[$g][14][3][1] = bcdiv($values[$g-1][14][2][0], 2);
    $values[$g][14][3][0] = bcadd(bcdiv($values[$g-1][14][4][2], 2), bcadd(bcdiv($values[$g-1][14][4][1], 2), bcdiv($values[$g-1][13][0][0], 2)));
    $values[$g][14][2][2] = bcadd(bcdiv($values[$g-1][14][1][1], 2), bcdiv($values[$g-1][15][4][2], 2));
    $values[$g][14][2][1] = bcdiv($values[$g-1][14][1][0], 2);
    $values[$g][14][2][0] = bcadd(bcdiv($values[$g-1][14][3][2], 2), bcadd(bcdiv($values[$g-1][14][3][1], 2), bcdiv($values[$g-1][14][3][0], 2)));
    $values[$g][14][1][2] = bcadd(bcdiv($values[$g-1][15][4][1], 2), bcdiv($values[$g-1][15][3][2], 2));
    $values[$g][14][1][1] = bcdiv($values[$g-1][14][0][0], 2);
    $values[$g][14][1][0] = bcadd(bcdiv($values[$g-1][14][2][2], 2), bcadd(bcdiv($values[$g-1][14][2][1], 2), bcdiv($values[$g-1][14][2][0], 2)));
    $values[$g][14][0][0] = bcadd(bcdiv($values[$g-1][14][1][2], 2), bcadd(bcdiv($values[$g-1][14][1][1], 2), bcdiv($values[$g-1][14][1][0], 2)));
    
    $results[$g][14] = floatval(
        bcadd($values[$g][14][4][2],
        bcadd($values[$g][14][4][1],
        bcadd($values[$g][14][3][2],
        bcadd($values[$g][14][3][1],
        bcadd($values[$g][14][3][0],
        bcadd($values[$g][14][2][2],
        bcadd($values[$g][14][2][1],
        bcadd($values[$g][14][2][0],
        bcadd($values[$g][14][1][2],
        bcadd($values[$g][14][1][1],
        bcadd($values[$g][14][1][0],
              $values[$g][14][0][0]))))))))))));
    
    // Rank 15 - Silvermoon Guardian
    $values[$g][15][4][2] = bcadd(bcdiv($values[$g-1][15][3][1], 2), bcdiv($values[$g-1][15][2][2], 2));
    $values[$g][15][4][1] = bcdiv($values[$g-1][15][3][0], 2);
    $values[$g][15][3][2] = bcadd(bcdiv($values[$g-1][15][2][1], 2), bcdiv($values[$g-1][15][1][2], 2));
    $values[$g][15][3][1] = bcdiv($values[$g-1][15][2][0], 2);
    $values[$g][15][3][0] = bcadd(bcdiv($values[$g-1][15][4][2], 2), bcadd(bcdiv($values[$g-1][15][4][1], 2), bcdiv($values[$g-1][14][0][0], 2)));
    $values[$g][15][2][2] = bcadd(bcdiv($values[$g-1][15][1][1], 2), bcdiv($values[$g-1][16][3][2], 2));
    $values[$g][15][2][1] = bcdiv($values[$g-1][15][1][0], 2);
    $values[$g][15][2][0] = bcadd(bcdiv($values[$g-1][15][3][2], 2), bcadd(bcdiv($values[$g-1][15][3][1], 2), bcdiv($values[$g-1][15][3][0], 2)));
    $values[$g][15][1][2] = bcadd(bcdiv($values[$g-1][16][3][1], 2), bcdiv($values[$g-1][16][2][2], 2));
    $values[$g][15][1][1] = bcdiv($values[$g-1][15][0][0], 2);
    $values[$g][15][1][0] = bcadd(bcdiv($values[$g-1][15][2][2], 2), bcadd(bcdiv($values[$g-1][15][2][1], 2), bcdiv($values[$g-1][15][2][0], 2)));
    $values[$g][15][0][0] = bcadd(bcdiv($values[$g-1][15][1][2], 2), bcadd(bcdiv($values[$g-1][15][1][1], 2), bcdiv($values[$g-1][15][1][0], 2)));
    
    $results[$g][15] = floatval(
        bcadd($values[$g][15][4][2],
        bcadd($values[$g][15][4][1],
        bcadd($values[$g][15][3][2],
        bcadd($values[$g][15][3][1],
        bcadd($values[$g][15][3][0],
        bcadd($values[$g][15][2][2],
        bcadd($values[$g][15][2][1],
        bcadd($values[$g][15][2][0],
        bcadd($values[$g][15][1][2],
        bcadd($values[$g][15][1][1],
        bcadd($values[$g][15][1][0],
              $values[$g][15][0][0]))))))))))));

    // Rank 16 - Questing Adventurer
    $values[$g][16][3][2] = bcadd(bcdiv($values[$g-1][16][2][1], 2), bcdiv($values[$g-1][16][1][2], 2));
    $values[$g][16][3][1] = bcdiv($values[$g-1][16][2][0], 2);
    $values[$g][16][2][2] = bcadd(bcdiv($values[$g-1][16][1][1], 2), bcdiv($values[$g-1][17][3][2], 2));
    $values[$g][16][2][1] = bcdiv($values[$g-1][16][1][0], 2);
    $values[$g][16][2][0] = bcadd(bcdiv($values[$g-1][16][3][2], 2), bcadd(bcdiv($values[$g-1][16][3][1], 2), bcdiv($values[$g-1][15][0][0], 2)));
    $values[$g][16][1][2] = bcadd(bcdiv($values[$g-1][17][3][1], 2), bcdiv($values[$g-1][17][2][2], 2));
    $values[$g][16][1][1] = bcdiv($values[$g-1][16][0][0], 2);
    $values[$g][16][1][0] = bcadd(bcdiv($values[$g-1][16][2][2], 2), bcadd(bcdiv($values[$g-1][16][2][1], 2), bcdiv($values[$g-1][16][2][0], 2)));
    $values[$g][16][0][0] = bcadd(bcdiv($values[$g-1][16][1][2], 2), bcadd(bcdiv($values[$g-1][16][1][1], 2), bcdiv($values[$g-1][16][1][0], 2)));
    
    $results[$g][16] = floatval(
        bcadd($values[$g][16][3][2],
        bcadd($values[$g][16][3][1],
        bcadd($values[$g][16][2][2],
        bcadd($values[$g][16][2][1],
        bcadd($values[$g][16][2][0],
        bcadd($values[$g][16][1][2],
        bcadd($values[$g][16][1][1],
        bcadd($values[$g][16][1][0],
              $values[$g][16][0][0])))))))));
    
    // Rank 17 - Tauren Warrior
    $values[$g][17][3][2] = bcadd(bcdiv($values[$g-1][17][2][1], 2), bcdiv($values[$g-1][17][1][2], 2));
    $values[$g][17][3][1] = bcdiv($values[$g-1][17][2][0], 2);
    $values[$g][17][2][2] = bcadd(bcdiv($values[$g-1][17][1][1], 2), bcdiv($values[$g-1][18][3][2], 2));
    $values[$g][17][2][1] = bcdiv($values[$g-1][17][1][0], 2);
    $values[$g][17][2][0] = bcadd(bcdiv($values[$g-1][17][3][2], 2), bcadd(bcdiv($values[$g-1][17][3][1], 2), bcdiv($values[$g-1][16][0][0], 2)));
    $values[$g][17][1][2] = bcadd(bcdiv($values[$g-1][18][3][1], 2), bcdiv($values[$g-1][18][2][2], 2));
    $values[$g][17][1][1] = bcdiv($values[$g-1][17][0][0], 2);
    $values[$g][17][1][0] = bcadd(bcdiv($values[$g-1][17][2][2], 2), bcadd(bcdiv($values[$g-1][17][2][1], 2), bcdiv($values[$g-1][17][2][0], 2)));
    $values[$g][17][0][0] = bcadd(bcdiv($values[$g-1][17][1][2], 2), bcadd(bcdiv($values[$g-1][17][1][1], 2), bcdiv($values[$g-1][17][1][0], 2)));
    
    $results[$g][17] = floatval(
        bcadd($values[$g][17][3][2],
        bcadd($values[$g][17][3][1],
        bcadd($values[$g][17][2][2],
        bcadd($values[$g][17][2][1],
        bcadd($values[$g][17][2][0],
        bcadd($values[$g][17][1][2],
        bcadd($values[$g][17][1][1],
        bcadd($values[$g][17][1][0],
              $values[$g][17][0][0])))))))));
    
    // Rank 18 - Sorcerer's Apprentice
    $values[$g][18][3][2] = bcadd(bcdiv($values[$g-1][18][2][1], 2), bcdiv($values[$g-1][18][1][2], 2));
    $values[$g][18][3][1] = bcdiv($values[$g-1][18][2][0], 2);
    $values[$g][18][2][2] = bcadd(bcdiv($values[$g-1][18][1][1], 2), bcdiv($values[$g-1][19][3][2], 2));
    $values[$g][18][2][1] = bcdiv($values[$g-1][18][1][0], 2);
    $values[$g][18][2][0] = bcadd(bcdiv($values[$g-1][18][3][2], 2), bcadd(bcdiv($values[$g-1][18][3][1], 2), bcdiv($values[$g-1][17][0][0], 2)));
    $values[$g][18][1][2] = bcadd(bcdiv($values[$g-1][19][3][1], 2), bcdiv($values[$g-1][19][2][2], 2));
    $values[$g][18][1][1] = bcdiv($values[$g-1][18][0][0], 2);
    $values[$g][18][1][0] = bcadd(bcdiv($values[$g-1][18][2][2], 2), bcadd(bcdiv($values[$g-1][18][2][1], 2), bcdiv($values[$g-1][18][2][0], 2)));
    $values[$g][18][0][0] = bcadd(bcdiv($values[$g-1][18][1][2], 2), bcadd(bcdiv($values[$g-1][18][1][1], 2), bcdiv($values[$g-1][18][1][0], 2)));
    
    $results[$g][18] = floatval(
        bcadd($values[$g][18][3][2],
        bcadd($values[$g][18][3][1],
        bcadd($values[$g][18][2][2],
        bcadd($values[$g][18][2][1],
        bcadd($values[$g][18][2][0],
        bcadd($values[$g][18][1][2],
        bcadd($values[$g][18][1][1],
        bcadd($values[$g][18][1][0],
              $values[$g][18][0][0])))))))));
    
    // Rank 19 - Novice Engineer
    $values[$g][19][3][2] = bcadd(bcdiv($values[$g-1][19][2][1], 2), bcdiv($values[$g-1][19][1][2], 2));
    $values[$g][19][3][1] = bcdiv($values[$g-1][19][2][0], 2);
    $values[$g][19][2][2] = bcadd(bcdiv($values[$g-1][19][1][1], 2), bcdiv($values[$g-1][20][3][2], 2));
    $values[$g][19][2][1] = bcdiv($values[$g-1][19][1][0], 2);
    $values[$g][19][2][0] = bcadd(bcdiv($values[$g-1][19][3][2], 2), bcadd(bcdiv($values[$g-1][19][3][1], 2), bcdiv($values[$g-1][18][0][0], 2)));
    $values[$g][19][1][2] = bcadd(bcdiv($values[$g-1][20][3][1], 2), bcdiv($values[$g-1][20][2][2], 2));
    $values[$g][19][1][1] = bcdiv($values[$g-1][19][0][0], 2);
    $values[$g][19][1][0] = bcadd(bcdiv($values[$g-1][19][2][2], 2), bcadd(bcdiv($values[$g-1][19][2][1], 2), bcdiv($values[$g-1][19][2][0], 2)));
    $values[$g][19][0][0] = bcadd(bcdiv($values[$g-1][19][1][2], 2), bcadd(bcdiv($values[$g-1][19][1][1], 2), bcdiv($values[$g-1][19][1][0], 2)));
    
    $results[$g][19] = floatval(
        bcadd($values[$g][19][3][2],
        bcadd($values[$g][19][3][1],
        bcadd($values[$g][19][2][2],
        bcadd($values[$g][19][2][1],
        bcadd($values[$g][19][2][0],
        bcadd($values[$g][19][1][2],
        bcadd($values[$g][19][1][1],
        bcadd($values[$g][19][1][0],
              $values[$g][19][0][0])))))))));
    
    // Rank 20 - Shieldbearer
    $values[$g][20][3][2] = bcadd(bcdiv($values[$g-1][20][2][1], 2), bcdiv($values[$g-1][20][1][2], 2));
    $values[$g][20][3][1] = bcdiv($values[$g-1][20][2][0], 2);
    $values[$g][20][2][2] = bcadd(bcdiv($values[$g-1][20][1][1], 2), bcdiv($values[$g-1][21][2][2], 2));
    $values[$g][20][2][1] = bcdiv($values[$g-1][20][1][0], 2);
    $values[$g][20][2][0] = bcadd(bcdiv($values[$g-1][20][3][2], 2), bcadd(bcdiv($values[$g-1][20][3][1], 2), bcdiv($values[$g-1][19][0][0], 2)));
    $values[$g][20][1][2] = bcadd(bcdiv($values[$g-1][21][2][1], 2), bcdiv($values[$g-1][21][1][2], 2));
    $values[$g][20][1][1] = bcadd(bcdiv($values[$g-1][20][0][0], 2), bcdiv($values[$g-1][21][2][0], 2));
    $values[$g][20][1][0] = bcadd(bcdiv($values[$g-1][20][2][2], 2), bcadd(bcdiv($values[$g-1][20][2][1], 2), bcdiv($values[$g-1][20][2][0], 2)));
    $values[$g][20][0][0] = bcadd(bcdiv($values[$g-1][20][1][2], 2), bcadd(bcdiv($values[$g-1][20][1][1], 2), bcadd(bcdiv($values[$g-1][20][1][0], 2), bcdiv($values[$g-1][20][0][0], 2))));
    
    $results[$g][20] = floatval(
        bcadd($values[$g][20][3][2],
        bcadd($values[$g][20][3][1],
        bcadd($values[$g][20][2][2],
        bcadd($values[$g][20][2][1],
        bcadd($values[$g][20][2][0],
        bcadd($values[$g][20][1][2],
        bcadd($values[$g][20][1][1],
        bcadd($values[$g][20][1][0],
              $values[$g][20][0][0])))))))));
    
    // Rank 21 - Southsea Deckhand
    $values[$g][21][2][2] = bcadd(bcdiv($values[$g-1][21][1][1], 2), bcdiv($values[$g-1][22][2][2], 2));
    $values[$g][21][2][1] = bcdiv($values[$g-1][21][1][0], 2);
    $values[$g][21][2][0] = bcadd(bcdiv($values[$g-1][21][2][2], 2), bcadd(bcdiv($values[$g-1][21][2][1], 2), bcdiv($values[$g-1][21][2][0], 2)));
    $values[$g][21][1][2] = bcadd(bcdiv($values[$g-1][22][2][1], 2), bcdiv($values[$g-1][22][1][2], 2));
    $values[$g][21][1][1] = bcdiv($values[$g-1][22][2][0], 2);
    $values[$g][21][1][0] = bcadd(bcdiv($values[$g-1][21][1][2], 2), bcadd(bcdiv($values[$g-1][21][1][1], 2), bcdiv($values[$g-1][21][1][0], 2)));
    
    $results[$g][21] = floatval(
        bcadd($values[$g][21][2][2],
        bcadd($values[$g][21][2][1],
        bcadd($values[$g][21][2][0],
        bcadd($values[$g][21][1][2],
        bcadd($values[$g][21][1][1],
              $values[$g][21][1][0]))))));
    
    // Rank 22 - Murloc Raider
    $values[$g][22][2][2] = bcadd(bcdiv($values[$g-1][22][1][1], 2), bcdiv($values[$g-1][23][2][2], 2));
    $values[$g][22][2][1] = bcdiv($values[$g-1][22][1][0], 2);
    $values[$g][22][2][0] = bcadd(bcdiv($values[$g-1][22][2][2], 2), bcadd(bcdiv($values[$g-1][22][2][1], 2), bcdiv($values[$g-1][22][2][0], 2)));
    $values[$g][22][1][2] = bcadd(bcdiv($values[$g-1][23][2][1], 2), bcdiv($values[$g-1][23][1][2], 2));
    $values[$g][22][1][1] = bcdiv($values[$g-1][23][2][0], 2);
    $values[$g][22][1][0] = bcadd(bcdiv($values[$g-1][22][1][2], 2), bcadd(bcdiv($values[$g-1][22][1][1], 2), bcdiv($values[$g-1][22][1][0], 2)));
    
    $results[$g][22] = floatval(
        bcadd($values[$g][22][2][2],
        bcadd($values[$g][22][2][1],
        bcadd($values[$g][22][2][0],
        bcadd($values[$g][22][1][2],
        bcadd($values[$g][22][1][1],
              $values[$g][22][1][0]))))));
    
    // Rank 23 - Argent Squire
    $values[$g][23][2][2] = bcadd(bcdiv($values[$g-1][23][1][1], 2), bcdiv($values[$g-1][24][2][2], 2));
    $values[$g][23][2][1] = bcdiv($values[$g-1][23][1][0], 2);
    $values[$g][23][2][0] = bcadd(bcdiv($values[$g-1][23][2][2], 2), bcadd(bcdiv($values[$g-1][23][2][1], 2), bcdiv($values[$g-1][23][2][0], 2)));
    $values[$g][23][1][2] = bcadd(bcdiv($values[$g-1][24][2][1], 2), bcdiv($values[$g-1][24][1][2], 2));
    $values[$g][23][1][1] = bcdiv($values[$g-1][24][2][0], 2);
    $values[$g][23][1][0] = bcadd(bcdiv($values[$g-1][23][1][2], 2), bcadd(bcdiv($values[$g-1][23][1][1], 2), bcdiv($values[$g-1][23][1][0], 2)));
    
    $results[$g][23] = floatval(
        bcadd($values[$g][23][2][2],
        bcadd($values[$g][23][2][1],
        bcadd($values[$g][23][2][0],
        bcadd($values[$g][23][1][2],
        bcadd($values[$g][23][1][1],
              $values[$g][23][1][0]))))));
    
    // Rank 24 - Leper Gnome       
    $values[$g][24][2][2] = bcadd(bcdiv($values[$g-1][24][1][1], 2), bcdiv($values[$g-1][25][2][2], 2));
    $values[$g][24][2][1] = bcdiv($values[$g-1][24][1][0], 2);
    $values[$g][24][2][0] = bcadd(bcdiv($values[$g-1][24][2][2], 2), bcadd(bcdiv($values[$g-1][24][2][1], 2), bcdiv($values[$g-1][24][2][0], 2)));
    $values[$g][24][1][2] = bcdiv($values[$g-1][25][2][1], 2);
    $values[$g][24][1][1] = bcdiv($values[$g-1][25][2][0], 2);
    $values[$g][24][1][0] = bcadd(bcdiv($values[$g-1][24][1][2], 2), bcadd(bcdiv($values[$g-1][24][1][1], 2), bcdiv($values[$g-1][24][1][0], 2)));
    
    $results[$g][24] = floatval(
        bcadd($values[$g][24][2][2],
        bcadd($values[$g][24][2][1],
        bcadd($values[$g][24][2][0],
        bcadd($values[$g][24][1][2],
        bcadd($values[$g][24][1][1],
              $values[$g][24][1][0]))))));
    
    // Rank 25 - Angry Chicken
    $values[$g][25][2][2] = bcdiv($values[$g-1][25][1][1], 2);
    $values[$g][25][2][1] = bcdiv($values[$g-1][25][1][0], 2);
    $values[$g][25][2][0] = bcadd(bcdiv($values[$g-1][25][2][2], 2), bcadd(bcdiv($values[$g-1][25][2][1], 2), bcdiv($values[$g-1][25][2][0], 2)));
    $values[$g][25][1][1] = bcdiv($values[$g-1][25][0][0], 2);
    $values[$g][25][1][0] = bcadd(bcdiv($values[$g-1][25][1][1], 2), bcdiv($values[$g-1][25][1][0], 2));
    $values[$g][25][0][0] = bcdiv($values[$g-1][25][0][0], 2);
    
    $results[$g][25] = floatval(
        bcadd($values[$g][25][2][2],
        bcadd($values[$g][25][2][1],
        bcadd($values[$g][25][2][0],
        bcadd($values[$g][25][1][1],
        bcadd($values[$g][25][1][0],
              $values[$g][25][0][0]))))));
}

file_put_contents($output_file, json_encode($results, JSON_NUMERIC_CHECK));

$time_end = microtime(true);
echo "Time: " . ($time_end - $time_start) . " seconds";
