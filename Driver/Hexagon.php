<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Driver;

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}

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
 * Manages the conversion between simple ascii/unicode hex grids and PHP arrays
 *
 * Please set (if you are using Unicode characters) :
 * mb_internal_encoding('UTF-8');
 *
 * Array representation uses the Isometric Cube Coordinate System
 * See the tests for explanatory drawings.
 *
 * See Gmf\GmfBundle\Tests\Tool\String\AsciiHexGridTest for documentation on how this behaves
 *
 * @author Goutte
 */
abstract class Hexagon implements Driver
{
//    const BOX_DRAWING_HORIZONTAL_LINE = '_';
//    const BOX_DRAWING_DIAGONAL_BL2UR  = '/';
//    const BOX_DRAWING_DIAGONAL_BR2UL  = '\\';

    const SIZE = 5;

    abstract public function getBoxDrawingHorizontalLine();
    abstract public function getBoxDrawingDiagonalBL2UR();
    abstract public function getBoxDrawingDiagonalBR2UL();

    /**
     * @param  string $string
     * @return array
     */
    public function toArray($string)
    {
        $arrayOfLines = explode(PHP_EOL, $string);

        if (0 == count($arrayOfLines)) return array();

        $size = self::SIZE;
        $grid = array();
        $horizontalSeparator = str_repeat($this->getBoxDrawingHorizontalLine(), $size);

        // List all cells positions
        $cellPositions = array(); // of [row, col]
        $row = $sumOfRows = $sumOfCols = 0;
        while (isset($arrayOfLines[$row])) {
            $positions = strpos_recursive($arrayOfLines[$row], $horizontalSeparator);
            foreach ($positions as $col) {
                if (isset($arrayOfLines[$row+4]) && mb_substr($arrayOfLines[$row+4], $col, $size) == $horizontalSeparator) {
                    $cellPositions[] = array($row, $col);
                    $sumOfRows += $row;
                    $sumOfCols += $col;
                }
            }
            $row += 2;
        }

        $nbOfCells = count($cellPositions);
        if (0 === $nbOfCells) return array();

        // Detect the origin (closest of the median, topmost and then leftmost)
        $medianRow = $sumOfRows / $nbOfCells;
        $medianCol = $sumOfCols / $nbOfCells;

        $originRow = $cellPositions[0][0];
        $originCol = $cellPositions[0][1];

        $cellPositions = array_reverse($cellPositions);

        foreach ($cellPositions as $n => $cellPosition) {
            if (0 === $n) continue;

            if (abs($cellPosition[0]-$medianRow) <= abs($originRow-$medianRow)) {
                if (abs($cellPosition[1]-$medianCol) <= abs($originCol-$medianCol)) {
                    $originRow = $cellPosition[0];
                    $originCol = $cellPosition[1];
                }
            }
        }

        // Extract the cells' data
        foreach ($cellPositions as $n => $cellPosition) {
            $row = $cellPosition[0];
            $col = $cellPosition[1];

            $content = self::extractContentOfCellWhoseTopLeftIs($row, $col, $arrayOfLines);

            $x = ($col - $originCol) / ($size + 2);
            $y = -1 * (2 * $x + $row - $originRow) / 4;
            $z = -1 * ($x+$y);

            self::addCellTo3DArray($grid, $x, $y, $z, $content);
        }

        return $grid;
    }

    /**
     * Converts passed $array to its ascii grid representation
     * Array must have a structure similar to array(0=>array(0=>array(0=>'A')))
     *
     * @param  array $array
     * @throws \InvalidArgumentException
     * @return string
     */
    public function toString($array)
    {
        if (!is_array($array)) $array = array(0=>array(0=>array(0=>$array)));

        // input validation
        foreach ($array as $x => $xArray) {
            if (!is_numeric($x) || !is_array($xArray)) throw new \InvalidArgumentException();
            foreach ($xArray as $y => $yArray) {
                if (!is_numeric($y) || !is_array($yArray)) throw new \InvalidArgumentException();
                foreach ($yArray as $z => $value) {
                    if (!is_numeric($z)) throw new \InvalidArgumentException();
                }
            }
        }

        $grid = array();

        foreach ($array as $x => $xArray) {
            foreach ($xArray as $y => $yArray) {
                foreach ($yArray as $z => $value) {
                    $a = $x * 7;
                    $b = -4 * $y - 2 * $x;
                    $this->writeHexagonIntoArray($grid, $a, $b, $value);
                }
            }
        }

        $xMin = $yMin = PHP_INT_MAX;
        $xMax = $yMax = PHP_INT_MIN;
        foreach ($grid as $x => $row) {
            $xMin = min($x, $xMin);
            $xMax = max($x, $xMax);
            foreach ($row as $y => $col) {
                $yMin = min($y, $yMin);
                $yMax = max($y, $yMax);
            }
        }

        $string = '';

        for ($y=$yMin; $y<=$yMax; $y++) {
            for ($x=$xMin; $x<=$xMax; $x++) {
                if (isset($grid[$x][$y])) {
                    $string .= $grid[$x][$y];
                } else {
                    $string .= ' ';
                }
            }
            $string = rtrim($string);

            if ($y < $yMax) $string .= PHP_EOL;
        }

        return $string;
    }

    static protected function extractContentOfCellWhoseTopLeftIs($top, $left, $arrayOfLines)
    {
        $line1 = trim(mb_substr($arrayOfLines[$top+2], $left, self::SIZE));
        $line2 = trim(mb_substr($arrayOfLines[$top+3], $left, self::SIZE));

        $content = $line1;

        if (mb_strlen($line2)) $content .= ' ' . $line2;

        return $content;
    }

    protected function writeHexagonIntoArray(&$array, $x, $y, $value)
    {
        $size = self::SIZE;
        $boxDrawingHorizontalLine = $this->getBoxDrawingHorizontalLine();
        $boxDrawingDiagonalBL2UR = $this->getBoxDrawingDiagonalBL2UR();
        $boxDrawingDiagonalBR2UL = $this->getBoxDrawingDiagonalBR2UL();

        // top & bottom sides
        for ($i=0; $i<$size; $i++) {
            self::addCellTo2DArray($array, $x+$i, $y, $boxDrawingHorizontalLine);
            self::addCellTo2DArray($array, $x+$i, $y+4, $boxDrawingHorizontalLine);
        }

        // left side
        self::addCellTo2DArray($array, $x-1, $y+1, $boxDrawingDiagonalBL2UR);
        self::addCellTo2DArray($array, $x-2, $y+2, $boxDrawingDiagonalBL2UR);
        self::addCellTo2DArray($array, $x-2, $y+3, $boxDrawingDiagonalBR2UL);
        self::addCellTo2DArray($array, $x-1, $y+4, $boxDrawingDiagonalBR2UL);

        // right side
        self::addCellTo2DArray($array, $x+$size+0, $y+1, $boxDrawingDiagonalBR2UL);
        self::addCellTo2DArray($array, $x+$size+1, $y+2, $boxDrawingDiagonalBR2UL);
        self::addCellTo2DArray($array, $x+$size+1, $y+3, $boxDrawingDiagonalBL2UR);
        self::addCellTo2DArray($array, $x+$size+0, $y+4, $boxDrawingDiagonalBL2UR);

        // value
        if (mb_strlen($value) > $size) {
            // todo: properly separate content and throw if too long for two lines
            $value1 = mb_str_pad(mb_substr($value, 0,     $size), $size, ' ', STR_PAD_BOTH);
            $value2 = mb_str_pad(mb_substr($value, $size, $size), $size, ' ', STR_PAD_BOTH);
        } else {
            $value1 = mb_str_pad($value, $size, ' ', STR_PAD_BOTH);
            $value2 = mb_str_pad('',     $size, ' ', STR_PAD_BOTH);
        }
        for ($i=0; $i<$size; $i++) {
            self::addCellTo2DArray($array, $x+$i, $y+2, mb_substr($value1, $i, 1));
            self::addCellTo2DArray($array, $x+$i, $y+3, mb_substr($value2, $i, 1));
        }
    }


    static protected function addCellTo2DArray(&$array, $x, $y, $value)
    {
        if (!isset($array[$x])) $array[$x] = array();
        if (isset($array[$x][$y]) && $array[$x][$y] !== $value) {
            throw new \InvalidArgumentException("There already is a different value at {$x}/{$y}.");
        }

        $array[$x][$y] = $value;
    }

    static protected function addCellTo3DArray(&$array, $x, $y, $z, $value)
    {
        if (!isset($array[$x]))        $array[$x] = array();
        if (!isset($array[$x][$y]))    $array[$x][$y] = array();
        if (isset($array[$x][$y][$z]) && $array[$x][$y][$z] !== $value) {
            throw new \InvalidArgumentException("There already is a different value at {$x}/{$y}/{$z}.");
        }

        $array[$x][$y][$z] = $value;
    }

}