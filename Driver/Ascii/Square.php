<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Driver\Ascii;

use GeorgetteParty\UnicodeTesselationBundle\Driver\Driver;

if (!function_exists(__NAMESPACE__.'\\'.'strpos_recursive')) {
    /**
     * Returns an array of the positions of $nedle in $haystack, starting at $offset
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     * @param array  $results
     * @return array
     */
    function strpos_recursive($haystack, $needle, $offset = 0, &$results = array())
    {
        $offset = strpos($haystack, $needle, $offset);
        if (false === $offset) {
            return $results;
        } else {
            $results[] = $offset;

            return strpos_recursive($haystack, $needle, ($offset + 1), $results);
        }
    }
}

if (!function_exists(__NAMESPACE__.'\\'.'mb_str_pad')) {
    /**
     * Multibyte-friendly str_pad
     *
     * @param $input
     * @param $pad_length
     * @param string $pad_string
     * @param int $pad_type
     * @return string
     */
    function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
    {
        return str_pad($input, $pad_length + strlen($input) - mb_strlen($input), $pad_string, $pad_type);
    }
}

/**
 * Manages the conversion between simple ascii square grids and 2D PHP arrays
 *
 * Please set (if you are using Unicode characters) :
 * mb_internal_encoding('UTF-8');
 *
 * See Gmf\GmfBundle\Tests\Tool\String\AsciiSquareGridTest for documentation on how this behaves fixme
 *
 * Usage example :
 *
 * echo AsciiSquareGrid::toString(array(array('A', 'B'), array(null, 'D')));
 *
 * will print :
 *
 * +---+---+
 * | A | B |
 * +---+---+
 * |   | D |
 * +---+---+
 *
 * @author Goutte
 */
class Square
    implements Driver
{
    const BOX_DRAWING_INTERSECTION    = '+';
    const BOX_DRAWING_HORIZONTAL_LINE = '-';
    const BOX_DRAWING_VERTICAL_LINE   = '|';

    /**
     * @param  string $string
     * @return array
     */
    public function toArray($string)
    {
        $arrayOfLines = explode(PHP_EOL, $string);

        if (0 == count($arrayOfLines)) return array();

        // Get the positions of the vertical separators
        $posOfVerticalSeparators = strpos_recursive($arrayOfLines[0], self::BOX_DRAWING_INTERSECTION);

        $grid = array();
        $row = null;

        foreach ($arrayOfLines as $line) {
            if (self::BOX_DRAWING_INTERSECTION == mb_substr($line, 0, 1)) { // horizontal separator line
                if (null !== $row) $grid[] = $row;
                $row = array();
            } else { // data line
                $startPos = 0;
                foreach ($posOfVerticalSeparators as $k => $endPos) {
                    if ($k > 0) {
                        $data = trim(mb_substr($line, $startPos + 1, $endPos - $startPos - 1));
                        if (isset($row[$k - 1])) {
                            if (mb_strlen($row[$k - 1]) && mb_strlen($data)) {
                                $row[$k - 1] .= ' ' . $data;
                            } else {
                                $row[$k - 1] .= $data;
                            }
                        } else {
                            $row[$k - 1] = $data;
                        }
                    }
                    $startPos = $endPos;
                }
            }
        }


        return $grid;
    }

    /**
     * Converts passed $array to its ascii grid representation
     *
     * @param  array $array
     * @return string
     */
    public function toString($array)
    {
        if (!is_array($array)) $array = array($array);

        // Count the number of cols needed and the size of a cell
        $nbOfCols = 0;
        $cellSize = 1;
        foreach ($array as $k => $row) {
            if (!is_array($row)) $array[$k] = $row = array($row);

            $nbOfCols = max($nbOfCols, count($row));
            foreach ($row as $col) {
                $cellSize = max($cellSize, self::getCellSizeFromContent($col));
            }
        }

        if ($nbOfCols < 1) return '';

        // Compute the horizontal separator between rows
        $gridLine = self::BOX_DRAWING_INTERSECTION;
        for ($i = 0; $i < $nbOfCols; $i++) {
            $gridLine .= str_repeat(self::BOX_DRAWING_HORIZONTAL_LINE, 2 * $cellSize + 1) . self::BOX_DRAWING_INTERSECTION;
        }

        // Prepare the content by exploding it into multiple lines if needed
        foreach ($array as $j => $row) {
            foreach ($row as $k => $col) {
                $array[$j][$k] = self::explodeCellContent($col, $cellSize);
            }
        }

        // For each row ...
        $grid = '';
        foreach ($array as $row) {
            $grid .= $gridLine . PHP_EOL;

            // For each line of the cell ...
            for ($i = 0; $i < $cellSize; $i++) {
                $grid .= self::BOX_DRAWING_VERTICAL_LINE;

                // For each column ...
                foreach ($row as $col) {
                    if (0 == mb_strlen($col[$i])) $col[$i] = str_repeat(' ', 2 * $cellSize - 1);
                    $col[$i] = mb_str_pad($col[$i], 2 * $cellSize - 1, ' ', STR_PAD_BOTH);
                    $grid .= " {$col[$i]} " . self::BOX_DRAWING_VERTICAL_LINE;
                }

                $grid .= PHP_EOL;
            }
        }

        // Add the ending horizontal separator
        $grid .= $gridLine;

        return $grid;
    }


    /**
     * This guesses the size needed for a cell from the content we want to put in it
     * The Size of a Cell is the number of lines that may hold content, starting at 1
     * A square cell of size N will have a width of 2N-1 for content
     * Therefore a square cell of size N can hold a string at most 2N²-N long.
     *
     * @param $string
     * @return int
     */
    static protected function getCellSizeFromContent($string)
    {
        $string = trim($string);
        $stringLength = mb_strlen($string);

        if (1 >= $stringLength) return 1;

        $spacePositions = strpos_recursive($string, ' ');

        if (0 == count($spacePositions)) return ceil(($stringLength + 1) / 2);

        $n = floor((1 + sqrt(1 + 4 * $stringLength)) / 4);
        while (!self::doesStringFitInCell($string, $n)) $n++;

        return $n;
    }

    /**
     * @param $string
     * @param $cellSize
     * @return bool
     */
    static protected function doesStringFitInCell($string, $cellSize)
    {
        try {
            $lines = self::explodeCellContent($string, $cellSize);
        } catch (CellFitnessException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $string
     * @param $cellSize
     * @return array
     * @throws CellFitnessException
     */
    static protected function explodeCellContent($string, $cellSize)
    {
        $stringArray = explode(' ', $string);

        $cellWidth = 2 * $cellSize - 1;
        $cellHeight = $cellSize;

        $lines = array();
        $currentLine = 0;
        foreach ($stringArray as $word) {
            $wordLength = mb_strlen($word);
            if ($cellWidth < $wordLength) throw new CellFitnessException("'{$string}' does not fit in cell of size {$cellSize}");

            if (isset($lines[$currentLine])) {
                $currentLineLength = mb_strlen($lines[$currentLine]);
                if ($cellWidth >= $currentLineLength + $wordLength + 1) {
                    $lines[$currentLine] .= ' ' . $word;
                } else {
                    $currentLine++;
                    $lines[$currentLine] = $word;
                }
            } else {
                $lines[$currentLine] = $word;
            }
        }

        $linesCount = count($lines);

        if ($linesCount > $cellHeight) throw new CellFitnessException("'{$string}' does not fit in cell of size {$cellSize}");

        // center vertically the content if needed by padding the array
        if ($linesCount < $cellHeight) {
            $padTop = floor(($cellHeight - $linesCount) / 2);

            $lines = array_pad($lines, -1 * ($linesCount + $padTop), '');
            $lines = array_pad($lines, $cellSize, '');
        }

        return $lines;
    }
}