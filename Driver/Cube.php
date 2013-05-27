<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Driver;

use GeorgetteParty\UnicodeTesselationBundle\Exception\InvalidCubeDataException;

// FIXME : DRY THESE FUNCTIONS UP (other tests/drivers use them too)

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
 * do we wanna use arrays or a class ? both is good. interface required for class then. opt-in, defaults to imbricated arrays
 *
 * @author Goutte
 */
class Cube
    implements Driver
{

    public $horizontal_line  = '-';
    public $vertical_line    = '|';
    public $empty_tile_value = ' ';



    /**
     * @param  string $string
     * @return array
     */
    public function toArray($string)
    {

//        mb_internal_encoding('UTF-8');

        $arrayOfLines = explode(PHP_EOL, $string);

        if (0 == count($arrayOfLines)) return array();

        // Measure edge size (by counting the | on the first line)
        $posOfVerticalSeparators = strpos_recursive($arrayOfLines[0], $this->vertical_line);
        $edgeSize = count($posOfVerticalSeparators);

        if (0 == $edgeSize) return array();

        $array = array();

        // Pass 1 : Top ( 0 1 0 )
        for ($i = 0 ; $i < $edgeSize ; $i++) {
            $line = substr($arrayOfLines[2*$i+1],2);
            for ($j = 0 ; $j < $edgeSize ; $j++) {
                $value = mb_substr($line, 4 * $j, 1);
                $this->pushIntoArray($array, $value, -1 * $edgeSize + 1 + 2*$j, $edgeSize, $edgeSize - 1 - 2*$i);
            }
        }

        // Pass 2 : Middle line
        for ($i = 0 ; $i < $edgeSize ; $i++) {
            $line = substr($arrayOfLines[2*$i+1+2*$edgeSize],2);
            // ( 0 0 -1 )
            for ($j = 0 ; $j < $edgeSize ; $j++) {
                $value = mb_substr($line, ($j+$edgeSize*0) * 4, 1);
                $this->pushIntoArray($array, $value, -1 * $edgeSize + 1 + 2*$j, $edgeSize - 1 - 2*$i, -1 * $edgeSize);
            }
            // ( 1 0 0 )
            for ($j = 0 ; $j < $edgeSize ; $j++) {
                $value = mb_substr($line, ($j+$edgeSize*1) * 4, 1);
                $this->pushIntoArray($array, $value, $edgeSize, $edgeSize - 1 - 2*$i, -1 * $edgeSize + 1 + 2*$j);
            }
            // ( 0 0 1 )
            for ($j = 0 ; $j < $edgeSize ; $j++) {
                $value = mb_substr($line, ($j+$edgeSize*2) * 4, 1);
                $this->pushIntoArray($array, $value, $edgeSize - 1 - 2*$j, $edgeSize - 1 - 2*$i, $edgeSize);
            }
            // ( -1 0 0 )
            for ($j = 0 ; $j < $edgeSize ; $j++) {
                $value = mb_substr($line, ($j+$edgeSize*3) * 4, 1);
                $this->pushIntoArray($array, $value, -1 * $edgeSize, $edgeSize - 1 - 2*$i, $edgeSize - 1 - 2*$j);
            }
        }

        // Pass 3 : Bottom ( 0 -1 0 )
        for ($i = 0 ; $i < $edgeSize ; $i++) {
            $line = substr($arrayOfLines[2*$i+1+4*$edgeSize],2);
            for ($j = 0 ; $j < $edgeSize ; $j++) {
                $value = mb_substr($line, 4*$j, 1);
                $this->pushIntoArray($array, $value, -1 * $edgeSize + 1 + 2*$j, -1 * $edgeSize, -1 * $edgeSize + 1 + 2*$i);
            }
        }

        $this->sortMultiArrayByKeys($array);

        return $array;
    }


    /**
     * This is used to build an imbricated array holding the coordinates as keys
     *
     * @param $array
     * @param $value
     * @param $x
     * @param $y
     * @param $z
     *
     * @return array
     */
    public function pushIntoArray(&$array, $value, $x, $y, $z)
    {
        if (empty($array[$x])) $array[$x] = array();
        if (empty($array[$x][$y])) $array[$x][$y] = array();
        $array[$x][$y][$z] = $value;

        return $array;
    }

    /**
     * Sorts the keys by ascending order on each coordinate level
     *
     * @param $array
     * @return array
     */
    public function sortMultiArrayByKeys(&$array)
    {
        ksort($array, SORT_NUMERIC);
        foreach ($array as $kX=>$rX) {
            ksort($array[$kX], SORT_NUMERIC);
        }
        foreach ($array as $kX=>$rX) {
            foreach ($rX as $kY=>$rY) {
                ksort($array[$kX][$kY], SORT_NUMERIC);
            }
        }

        return $array;
    }




    /**
     * Converts passed $array to its ascii cube grid representation
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
     * Sorted by increasing x then y then z.
     *
     * => dry this with iterator
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
                                if (null === $value || '' === $value) $value = $this->empty_tile_value;
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