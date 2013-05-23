<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Driver;

use GeorgetteParty\UnicodeTesselationBundle\Exception\InvalidCubeDataException;

if (!function_exists(__NAMESPACE__.'\\'.'strpos_recursive')) {
    /**
     * Returns an array of the positions of $needle in $haystack, starting at $offset
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
 * Manages the conversion between ascii cube ~patrons and 2D PHP arrays
 *
 * Please set (if you are using Unicode characters) :
 * mb_internal_encoding('UTF-8');
 *
 * Usage example :
 * fixme
 *
 * @author Goutte
 */
class Cube
    implements Driver
{
//    const BOX_DRAWING_INTERSECTION    = '+';
//    const BOX_DRAWING_HORIZONTAL_LINE = '-';
//    const BOX_DRAWING_VERTICAL_LINE   = '|';


    public $horizontal_line  = '-';
    public $vertical_line    = '|';
    public $empty_tile_value = ' ';




//    public function __construct($options=null)
//    {
//        if (empty($options)) $options = array();
//
//    }

    /**
     * @param  string $string
     * @return array fixme
     */
    public function toArray($string)
    {
        $arrayOfLines = explode(PHP_EOL, $string);

        if (0 == count($arrayOfLines)) return array();
//
//        // Get the positions of the vertical separators
//        $posOfVerticalSeparators = strpos_recursive($arrayOfLines[0], self::BOX_DRAWING_INTERSECTION);
//
//        $grid = array();
//        $row = null;
//
//        foreach ($arrayOfLines as $line) {
//            if (self::BOX_DRAWING_INTERSECTION == mb_substr($line, 0, 1)) { // horizontal separator line
//                if (null !== $row) $grid[] = $row;
//                $row = array();
//            } else { // data line
//                $startPos = 0;
//                foreach ($posOfVerticalSeparators as $k => $endPos) {
//                    if ($k > 0) {
//                        $data = trim(mb_substr($line, $startPos + 1, $endPos - $startPos - 1));
//                        if (isset($row[$k - 1])) {
//                            if (mb_strlen($row[$k - 1]) && mb_strlen($data)) {
//                                $row[$k - 1] .= ' ' . $data;
//                            } else {
//                                $row[$k - 1] .= $data;
//                            }
//                        } else {
//                            $row[$k - 1] = $data;
//                        }
//                    }
//                    $startPos = $endPos;
//                }
//            }
//        }


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

        // Guess the size of the edge of the cube
        // todo: optimize by looking only at first coord
        $edgeSize = 0;
        foreach ($array as $kX => $arrayX) {
            $abs = abs($kX);
            if ($abs > $edgeSize) $edgeSize = $abs;
            foreach ($arrayX as $kY => $arrayY) {
                $abs = abs($kY);
                if ($abs > $edgeSize) $edgeSize = $abs;
                foreach ($arrayY as $kZ => $value) {
                    $abs = abs($kZ);
                    if ($abs > $edgeSize) $edgeSize = $abs;
                }
            }
        }
        if (0 == $edgeSize) return '';

        // Initialize
        $grid = '';
        $coordValues = $this->getPossibleCoordinatesValues($edgeSize);

        // Pass 1 : top cube face ( 0 1 0 )
        for ($i=0;$i<$edgeSize;$i++) {
            $grid .= $this->getLineOfVerticalSeparators($edgeSize) . PHP_EOL;
            $values = $this->getValuesOfTilesValidatingEquality($array, null, $edgeSize, $coordValues[$edgeSize - 1 - $i]);
            $grid .= $this->getLineOfValues($values) . PHP_EOL;
        }

        // Pass 2 : middle cube faces ( 0 0 -1 ) ( 1 0 0 ) ( 0 0 1 ) ( -1 0 0 )
        for ($i=0;$i<$edgeSize;$i++) {
            $grid .= $this->getLineOfVerticalSeparators($edgeSize*4) . PHP_EOL;
            $values = array();
            // ( 0 0 -1 )
            $v = $this->getValuesOfTilesValidatingEquality($array, null, $coordValues[$edgeSize - 1 - $i], -1 * $edgeSize);
            $values = array_merge($values, $v);
            // ( 1 0 0 )
            $v = $this->getValuesOfTilesValidatingEquality($array, $edgeSize, $coordValues[$edgeSize - 1 - $i], null);
            $values = array_merge($values, $v);
            // ( 0 0 1 )
            $v = $this->getValuesOfTilesValidatingEquality($array, null, $coordValues[$edgeSize - 1 - $i], $edgeSize);
            $values = array_merge($values, array_reverse($v));
            // ( -1 0 0 )
            $v = $this->getValuesOfTilesValidatingEquality($array, -1 * $edgeSize, $coordValues[$edgeSize - 1 - $i], null);
            $values = array_merge($values, array_reverse($v));

            $grid .= $this->getLineOfValues($values) . PHP_EOL;
        }
        $grid .= $this->getLineOfVerticalSeparators($edgeSize*4) . PHP_EOL;

        // Pass 3 : bottom cube face ( 0 -1 0 )
        for ($i=0;$i<$edgeSize;$i++) {
            $values = $this->getValuesOfTilesValidatingEquality($array, null, -1 * $edgeSize, $coordValues[$i]);
            $grid .= $this->getLineOfValues($values) . PHP_EOL;
            $grid .= $this->getLineOfVerticalSeparators($edgeSize);
            if ($i < $edgeSize - 1) $grid .= PHP_EOL;
        }


        return $grid;
    }

    /**
     * From min to max
     *
     * @param $edgeSize
     * @return array
     */
    protected function getPossibleCoordinatesValues($edgeSize)
    {
        $r = array();
        $i = $edgeSize * -1 + 1;
        while ($i <= $edgeSize - 1)
        {
            $r[] = $i;
            $i += 2;
        }

        return $r;
    }

    protected function getLineOfVerticalSeparators($howMany)
    {
        $line = str_repeat('   '.$this->vertical_line, $howMany);
        if (strlen($line)) {
            $line = substr($line, 1); // remove initial space
        }

        return $line;
    }

    protected function getLineOfValues($values)
    {
        $line = '';
        $l = count($values);
        for ($i=0;$i<$l;$i++) {
            $line .= str_repeat($this->horizontal_line,3) . $values[$i];
        }
        if (strlen($line)) {
            $line = substr($line, 1); // remove initial horizontal separator
            $line .= str_repeat($this->horizontal_line,2); // add trailing h.s.
        }

        return $line;
    }


    /**
     * Returns a flat array
     * fixme : make sure the order is consistent
     *
     * @param $fromArray
     * @param null $x
     * @param null $y
     * @param null $z
     * @return array
     */
    protected function getValuesOfTilesValidatingEquality($fromArray, $x=null, $y=null, $z=null)
    {
        $values = array();

        foreach ($fromArray as $kX => $arrayX) {

            if (null === $x || $kX === $x) {

                foreach ($arrayX as $kY => $arrayY) {

                    if (null === $y || $kY === $y) {

                        foreach ($arrayY as $kZ => $value) {

                            if (null === $z || $kZ === $z) {

                                $values[] = $value;

                            }

                        }

                    }
                }

            }
        }


        return $values;
    }


}