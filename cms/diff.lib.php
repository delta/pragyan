<?php
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

/**
 * Diff and patch functions created using the documentation from the Wikipedia
 * article at http://en.wikipedia.org/wiki/Longest_common_subsequence_problem
 *
 * Copyright (c) 2007, Joshua Thompson <http://www.schmalls.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Joshua Thompson nor the names of its
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright  2007 Joshua Thompson <http://www.schmalls.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @author     Joshua Thompson <spam.goes.in.here@gmail.com>
 * @version    1.0.0
 * @link       http://www.schmalls.com
 */

/**
 * Finds the Least Common Subsequence of the two inputs, then uses it to
 * generate the diff, and returns the diff as a string.
 *
 * @param	string $original
 * @param	string $updated
 * @return	array
 */
function diff( $original, $updated )
{
    /**
     * Returns the diff as a string.
     *
     * @param	array $c the LCS
     * @param	array $x the original
     * @param	array $y the updated
     * @param	int $i
     * @param	int $j
     * @param	string $last
     * @param	boolean $change
     * @param	int $end_i
     * @param	int $end_j
     * @param	int $add_i
     * @param	int $add_j
     * @return	string
     */
    function print_diff( $c, $x, $y, $i, $j, $last = '', $change = false, $end_i = 0, $end_j = 0, $add_i = 0, $add_j = 0 )
    {
        $patch = '';
        if ( ( $i >= 0 ) && ( $j >= 0 ) && ( $x[$i] == $y[$j] ) ) {
            $patch .= print_diff( $c, $x, $y, $i - 1, $j - 1 );
            if ( $last != '' ) {
                $i_text = ( ( $i + $add_i ) == ( $end_i + 1 ) ) ? ( $i + $add_i ) : ( $i + $add_i ) . ',' . ( $end_i + 1 );
                $j_text = ( ( $j + $add_j ) == ( $end_j + 1 ) ) ? ( $j + $add_j ) : ( $j + $add_j ) . ',' . ( $end_j + 1 );
                $last = ( $change ) ? 'c' : $last;
                $patch .= $i_text . $last . $j_text . "\n";
            }
        } elseif ( ( $i == -1) && ( $j == -1 ) ) {
            $i_text = ( ( $i + $add_i ) == ( $end_i + 1 ) ) ? ( $i + $add_i ) : ( $i + $add_i ) . ',' . ( $end_i + 1 );
            $j_text = ( ( $j + $add_j ) == ( $end_j + 1 ) ) ? ( $j + $add_j ) : ( $j + $add_j ) . ',' . ( $end_j + 1 );
            $last = ( $change ) ? 'c' : $last;
            $patch .= $i_text . $last . $j_text . "\n";
        } else {
            $end_i = ( $end_i == 0 ) ? $i : $end_i;
            $end_j = ( $end_j == 0 ) ? $j : $end_j;
            if ( ($j >= 0 ) && ( ( $i == -1 ) || ( $c[$i][$j - 1] >= $c[$i - 1][$j] ) ) ) {
                $change = ( ( $last == 'd' ) || ( $change ) );
                $patch .= print_diff( $c, $x, $y, $i, $j - 1, 'a', $change, $end_i, $end_j, 1, 2 );
                $patch .= '> ' . $y[$j] . "\n";
            } elseif ( ( $i >= 0 ) && ( ( $j == -1 ) || ( $c[$i][$j - 1] < $c[$i - 1][$j] ) ) ) {
                $change = ( ( $last == 'a' ) || ( $change ) );
                $patch .= print_diff( $c, $x, $y, $i - 1, $j, 'd', $change, $end_i, $end_j, 2, 2 );
            }
        }

        return $patch;
    }

    $x = explode( "\n", str_replace( "\r\n", "\n", $original ) );
    $y = explode( "\n", str_replace( "\r\n", "\n", $updated ) );

    $m_start = 0;
    $m_end = count( $x ) - 1;
    $n_start = 0;
    $n_end = count( $y ) - 1;

    // trim off matching items at the beginning
    while ( ( $m_start < $m_end ) && ( $n_start < $n_end ) && ( $x[$m_start] == $y[$n_start] ) ) {
        $m_start++;
        $n_start++;
    }
    // trim off matching items at the end
    while ( ( $m_start < $m_end ) && ( $n_start < $n_end ) && ( $x[$m_end] == $y[$n_end] ) ) {
        $m_end--;
        $n_end--;
    }
    // now the LCS magic
    $c = array();
    for ( $a = -1; $a <= $m_end; $a++ ) {
        $c[$a] = array();
        for ( $b = -1; $b <= $n_end; $b++ ) {
            $c[$a][$b] = 0;
        }
    }
    for ( $i = $m_start; $i <= $m_end; $i++ ) {
        for ( $j = $n_start; $j <= $n_end; $j++ ) {
            if ( $x[$i] == $y[$j] ) {
                $c[$i][$j] = $c[$i - 1][$j - 1] + 1;
            } else {
                $c[$i][$j] = max( $c[$i][$j - 1], $c[$i - 1][$j] );
            }
        }
    }

    return print_diff( $c, $x, $y, count( $x ) - 1, count( $y ) - 1 );
}

/**
 * Applies the patch to the original document and returns the patched document.
 *
 * @param	string $original
 * @param	string $patch
 * @return	string
 */
function patch( $original, $patch )
{
    $new = array();
    $original_array = explode( "\n", str_replace( "\r\n", "\n", $original ) );
    $patch_array = explode( "\n", $patch );
    $i = 0;
    foreach ( $patch_array as $line ) {
        if ( ( !empty( $line ) ) && ( $line[0] == '>' ) ) {
            $new[] = substr( $line, 2 );
        } elseif ( !empty( $line ) ) {
            $pos = ( strpos( $line, 'a' ) !== false ) ? strpos( $line, 'a' ) : ( ( strpos( $line, 'c' ) !== false ) ? strpos( $line, 'c' ) : ( ( strpos( $line, 'd' ) !== false ) ? strpos( $line, 'd' ) : false ) );
            $type = $line[$pos];
            list( $i_half, $j_half ) = explode( $type, $line );
            list( $i_start, $i_end ) = explode( ',', $i_half . ',' . $i_half );
            $sub = ( $type == 'a' ) ? 0 : 1;
            for ( $a = $i; $a < ( $i_start - $sub ); $a++ ) {
                $new[] = $original_array[$a];
            }
            $i = $i_end;
        }
    }
    for ( $a = $i; $a < count( $original_array ); $a++ ) {
        $new[] = $original_array[$a];
    }

    return implode( "\n", $new );
}
/*
$original = 'This part of the
document has stayed the
same from version to
version.  It shouldn\'t
be shown if it doesn\'t
change.  Otherwise, that
would not be helping to
compress the size of the
changes.

This paragraph contains
text that is outdated.
It will be deleted in the
near future.

It is important to spell
check this dokument. On
the other hand, a
misspelled word isn\'t
the end of the world.
Nothing in the rest of
this paragraph needs to
be changed. Things can
be added after it.';

$updated = 'This is an important
notice! It should
therefore be located at
the beginning of this
document!

This part of the
document has stayed the
same from version to
version.  It shouldn\'t
be shown if it doesn\'t
change.  Otherwise, that
would not be helping to
compress anything.

It is important to spell
check this document. On
the other hand, a
misspelled word isn\'t
the end of the world.
Nothing in the rest of
this paragraph needs to
be changed. Things can
be added after it.

This paragraph contains
important new additions
to this document.';

$patch = diff( $original, $updated );
$new = patch( $original, $patch );
echo $patch , "\n", $new;

*/
