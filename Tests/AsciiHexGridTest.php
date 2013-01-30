<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Tests;

//use Gmf\GmfBundle\Tool\String\AsciiHexGrid;

if (!function_exists(__NAMESPACE__.'\\'.'array_merge_recursive_with_strict_keys')) {
    /**
     * Merges passed arrays into one, but keeps keys
     *
     * @return array|bool
     */
    function array_merge_recursive_with_strict_keys()
    {
        if (func_num_args() < 2) {
            trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
            return false;
        }

        $arrays = func_get_args();
        $merged = array();

        while ($arrays) {
            $array = array_shift($arrays);

            if (!is_array($array)) {
                trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
                return false;
            }

            if (!$array) continue;

            foreach ($array as $key => $value) {
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
                    $merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
                else
                    $merged[$key] = $value;
            }
        }

        return $merged;
    }
}


/**
 * Isometric Cube Coordinate System : http://www-cs-students.stanford.edu/~amitp/Articles/Hexagon2.html
 *
 * @author Goutte
 */
class AsciiHexGridTest extends \PHPUnit_Framework_TestCase
{

    public function setUp() {}

    public function tearDown() {}

    /**
     * @param $message
     * @param $array
     * @param $expected
     *
     * @return void
     * @dataProvider arrayToStringProvider
     */
    public function testToString($message, $array, $expected)
    {
        $actual = AsciiHexGrid::toString($array);

        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * @param $message
     * @param $expected
     * @param $string
     *
     * @return void
     * @dataProvider stringToArrayProvider
     */
    public function testToArray($message, $expected, $string)
    {
        $actual = AsciiHexGrid::toArray($string);

        $this->assertEquals($expected, $actual, $message);
    }


    public function arrayToStringProvider()
    {
        $r = array(
            array(
                "It should convert an non-array into a single cell",
                'A',
                <<<EOF
  _____
 /     \
/   A   \
\       /
 \_____/
EOF
            ),
            array(
                "It should interpret NULL as an empty cell",
                array(0 => array(0 => array(0 => null))),
                <<<EOF
  _____
 /     \
/       \
\       /
 \_____/
EOF
            ),
        );

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function stringToArrayProvider()
    {
        $r = array();

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function reciprocalTransformationProvider()
    {
        return array(

            array(
                "It should interpret an empty string as an empty cell",
                array(
                    0 => array(
                        0 => array(
                            0 => '',
                        ),
                    ),
                ),
                <<<EOF
  _____
 /     \
/       \
\       /
 \_____/
EOF
            ),
            array(
                "It should convert single-cell grids",
                array(
                    0 => array(
                        0 => array(
                            0 => 'A',
                        ),
                    ),
                ),
                <<<EOF
  _____
 /     \
/   A   \
\       /
 \_____/
EOF
            ),
            array(
                "It should work with integers",
                array(
                    0 => array(
                        0 => array(
                            0 => '7',
                        ),
                    ),
                ),
                <<<EOF
  _____
 /     \
/   7   \
\       /
 \_____/
EOF
            ),
            array(
                "It should work with the underscore symbol (_)",
                array(
                    0 => array(
                        0 => array(
                            0 => '_____',
                        ),
                    ),
                ),
                <<<EOF
  _____
 /     \
/ _____ \
\       /
 \_____/
EOF
            ),
            array(
                "It should work with the slash symbol (/)",
                array(
                    0 => array(
                        0 => array(
                            0 => '/',
                        ),
                    ),
                ),
                <<<EOF
  _____
 /     \
/   /   \
\       /
 \_____/
EOF
            ),
            array(
                "It should work with the antislash symbol (\\)",
                array(
                    0 => array(
                        0 => array(
                            0 => '\\',
                        ),
                    ),
                ),
                <<<EOF
  _____
 /     \
/   \   \
\       /
 \_____/
EOF
            ),
            array(
                "It should work with any unicode character, if using mb_internal_encoding('UTF-8')",
                array(
                    0 => array(
                        0 => array(
                            0 => '☯',
                        ),
                    ),
                ),

                <<<EOF
  _____
 /     \
/   ☯   \
\       /
 \_____/
EOF
            ),
            array(
                "It should position the origin on the topmost/leftmost cell the closest of the median",

                array_merge_recursive_with_strict_keys(
                    $this->buildArray('A',  0,  0,  0),
                    $this->buildArray('B', -1,  0,  1),
                    $this->buildArray('C',  1, -1,  0)
                ),

                <<<EOF
         _____
        /     \
  _____/   A   \_____
 /     \       /     \
/   B   \_____/   C   \
\       /     \       /
 \_____/       \_____/
EOF
            ),
            array(
                "It should position the origin on the topmost/leftmost cell the closest of the median cell",

                array_merge_recursive_with_strict_keys(
                    $this->buildArray('A',  0,  0,  0),
                    $this->buildArray('B', -1,  0,  1),
                    $this->buildArray('C',  1, -1,  0),
                    $this->buildArray('D',  0, -1,  1)
                ),

                <<<EOF
         _____
        /     \
  _____/   A   \_____
 /     \       /     \
/   B   \_____/   C   \
\       /     \       /
 \_____/   D   \_____/
       \       /
        \_____/
EOF
            ),
            array(
                "It should position the origin on the median cell",

                array_merge_recursive_with_strict_keys(
                    $this->buildArray('O',  0,  0,  0),
                    $this->buildArray('A',  0,  1, -1),
                    $this->buildArray('B', -1,  1,  0),
                    $this->buildArray('C', -1,  0,  1),
                    $this->buildArray('D',  0, -1,  1),
                    $this->buildArray('E',  1, -1,  0),
                    $this->buildArray('F',  1,  0, -1)
                ),

                <<<EOF
         _____
        /     \
  _____/   A   \_____
 /     \       /     \
/   B   \_____/   F   \
\       /     \       /
 \_____/   O   \_____/
 /     \       /     \
/   C   \_____/   E   \
\       /     \       /
 \_____/   D   \_____/
       \       /
        \_____/
EOF
            ),

        );
    }




    public function buildArray($value, $x, $y, $z)
    {
        return array($x => array($y => array($z => $value)));
    }

}






// DUMP ////////////////////////////////////////////////////////////////////////////////////////////////////////////////



$asciiHexGrid = <<<EOF
  ____
 /    \
/      \
\      /
 \____/

EOF;

$asciiHexGrid = <<<EOF
  _____
 /     \
/       \
\       /
 \_____/

EOF;

$asciiHexGrid = <<<EOF
        ____
       /    \
  ____/      \____
 /    \      /    \
/      \____/      \
\      /    \      /
 \____/      \____/
 /    \      /    \
/      \____/      \
\      /    \      /
 \____/      \____/
      \      /
       \____/

EOF;

$asciiHexGrid = <<<EOF
         _____
        /     \
  _____/       \_____
 /     \       /     \
/       \_____/       \
\       /     \       /
 \_____/       \_____/
 /     \       /     \
/       \_____/       \
\       /     \       /
 \_____/       \_____/
       \       /
        \_____/

EOF;

$asciiHexGrid = <<<EOF
        ____        ____
       /    \      /    \
  ____/      \____/      \
 /    \      /    \      /
/      \____/      \____/
\      /    \      /    \
 \____/      \____/      \
 /    \      /    \      /
/      \____/      \____/
\      /    \      /    \
 \____/      \____/      \
      \      /    \      /
       \____/      \____/

EOF;



$unicodeHexGrid = <<<EOF
        ____
       ╱    ╲
  ____╱      ╲____
 ╱    ╲      ╱    ╲
╱      ╲____╱      ╲
╲      ╱    ╲      ╱
 ╲____╱      ╲____╱
 ╱    ╲      ╱    ╲
╱      ╲____╱      ╲
╲      ╱    ╲      ╱
 ╲____╱      ╲____╱
      ╲      ╱
       ╲____╱

EOF;

$unicodeHexGrid = <<<EOF
         _____
        ╱     ╲
  _____╱       ╲_____
 ╱     ╲       ╱     ╲
╱       ╲_____╱       ╲
╲       ╱     ╲       ╱
 ╲_____╱       ╲_____╱
 ╱     ╲       ╱     ╲
╱       ╲_____╱       ╲
╲       ╱     ╲       ╱
 ╲_____╱       ╲_____╱
       ╲       ╱
        ╲_____╱

EOF;


// TwoPerpendicularAxisCoordinateSystem

$unicodeHexGrid = <<<EOF
           y
         _____
        ╱     ╲
  _____╱  0  2 ╲_____
 ╱     ╲       ╱     ╲
╱ -1  1 ╲_____╱  1  1 ╲
╲       ╱     ╲       ╱
 ╲_____╱  0  0 ╲_____╱   x
 ╱     ╲       ╱     ╲
╱ -1 -1 ╲_____╱  1 -1 ╲
╲       ╱     ╲       ╱
 ╲_____╱  0 -2 ╲_____╱
       ╲       ╱
        ╲_____╱

EOF;


// TwoAxisCoordinateSystem

$unicodeHexGrid = <<<EOF
           y
         _____
        ╱     ╲
  _____╱  0  1 ╲_____
 ╱     ╲       ╱     ╲ x
╱ -1  1 ╲_____╱  1  0 ╲
╲       ╱     ╲       ╱
 ╲_____╱  0  0 ╲_____╱
 ╱     ╲       ╱     ╲
╱ -1  0 ╲_____╱  1 -1 ╲
╲       ╱     ╲       ╱
 ╲_____╱  0 -1 ╲_____╱
       ╲       ╱
        ╲_____╱

EOF;


// IsometricCubeCoordinateSystem
// => the most elegant !
// x+y+z = 0

$unicodeHexGrid = <<<EOF
         _____
     y  ╱     ╲
  _____╱ 0 1-1 ╲_____
 ╱     ╲       ╱     ╲
╱-1 1 0 ╲_____╱ 1 0-1 ╲
╲       ╱     ╲       ╱
 ╲_____╱ 0 0 0 ╲_____╱  x
 ╱     ╲       ╱     ╲
╱-1 0 1 ╲_____╱ 1-1 0 ╲
╲       ╱     ╲       ╱
 ╲_____╱ 0-1 1 ╲_____╱
       ╲       ╱
    z   ╲_____╱

EOF;


$unicodeHexGrid = <<<EOF
                _____
       y       ╱     ╲
         _____╱ 0 2-2 ╲_____
        ╱     ╲       ╱     ╲
  _____╱-1 2-1 ╲_____╱ 1 1-2 ╲_____
 ╱     ╲       ╱     ╲       ╱     ╲
╱-2 2 0 ╲_____╱ 0 1-1 ╲_____╱ 2 0-2 ╲
╲       ╱     ╲       ╱     ╲       ╱
 ╲_____╱-1 1 0 ╲_____╱ 1 0-1 ╲_____╱
 ╱     ╲       ╱     ╲       ╱     ╲
╱-2 1 1 ╲_____╱ 0 0 0 ╲_____╱ 2-1-1 ╲  x
╲       ╱     ╲       ╱     ╲       ╱
 ╲_____╱-1 0 1 ╲_____╱ 1-1 0 ╲_____╱
 ╱     ╲       ╱     ╲       ╱     ╲
╱-2 0 2 ╲_____╱ 0-1 1 ╲_____╱ 2-2 0 ╲
╲       ╱     ╲       ╱     ╲       ╱
 ╲_____╱-1-1 2 ╲_____╱ 1-2 1 ╲_____╱
       ╲       ╱     ╲       ╱
        ╲_____╱ 0-2 2 ╲_____╱
              ╲       ╱
       z       ╲_____╱
EOF;





?>