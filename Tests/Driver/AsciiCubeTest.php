<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Tests\Driver;

/**
 * These are the tests for a quadsphere's faces' lattice text representation.
 * We make a ~patron of a cube, looking like a T laying with the head on the left : ⊢
 *
 * We can either draw lines and vertices around our tiles (drawing the edges and vertices of the quadsphere),
 * or draw the quadsphere's dual, ie. lines connecting our tiles.
 *
 * Dual modelisation is
 * - closer to go æsthetically
 * - isomorphic with the tiles' one (and sharing the same PHP modelisation)
 * - smaller in size
 * but
 * - requires an effort to understand the folding of the "patron"
 * - accepts only a single character per tile (otherwise it'll be messy I expect)
 *
 *
 *
 *
 *
 * @author Goutte
 */
 // fixme 9h15 - 10h01
class AsciiCubeTest extends DriverTestCase
{

    public function createDriver()
    {
        return new \GeorgetteParty\UnicodeTesselationBundle\Driver\Ascii\Cube();
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
    public function pushToArray(&$array, $value, $x, $y, $z)
    {
        if (empty($array[$x])) $array[$x] = array();
        if (empty($array[$x][$y])) $array[$x][$y] = array();
        $array[$x][$y][$z] = $value;

        return $array;
    }


    public function buildArrayFromFlat($array)
    {
        $r = array();
        foreach ($array as $v) {
            $this->pushToArray($r, $v[0], $v[1], $v[2], $v[3]);
        }
        return $r;
    }



    public function arrayToStringProvider()
    {
        $r = array(
//            array(
//                "It should make coffee",
//                array('A', 'B', 'C', 'D', 'E', 'F'),
//                <<<EOF
//+---+
//| A |
//+---+---+---+---+
//| B | D | E | F |
//+---+---+---+---+
//| C |
//+---+
//EOF
//            ),
        );

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function stringToArrayProvider()
    {
        $r = array(

        );

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function reciprocalTransformationProvider()
    {
        return array(

            array(
                "FAIL",
                array(array(null)),
                <<<EOF
+---+
|   |
+---+
EOF
            ),
            array(
                "fig 1.0.0",
                array(array('A')),
                <<<EOF
+---+
| A |
+---+---+---+---+
| B | D | E | F |
+---+---+---+---+
| C |
+---+
EOF
            ),
            array(
                "fig 2.0.0",
                array(array('A')),
                <<<EOF
A
|
B---D---E---F
|
C
EOF
            ),
            array(
                "TODO",
                array(array('Default coords')), # fixme
                <<<EOF
+-------+
|       |
| 0 1 0 |
|       |
+-------+-------+-------+-------+
|       |       |       |       |
| 0 0 -1| 1 0 0 | 0 0 1 |-1 0 0 |
|       |       |       |       |
+-------+-------+-------+-------+
|       |
| 0 -1 0|
|       |
+-------+
EOF
            ),
            array(
                "TODO △▽ ◁▷",
                array(array('A')), # fixme
                <<<EOF
+-------+
|   △   |
|   + ▷ |
|       |
+-------+-------+-------+-------+
|   △   |   △   |   △   |   △   |
|   + ▷ |   + ▷ | ◁ +   | ◁ +   |
|       |       |       |       |
+-------+-------+-------+-------+
|       |
|   + ▷ |
|   ▽   |
+-------+
EOF
            ),
            array(
                "TODO",
                array(array('A')), # fixme
                <<<EOF
+---+---+
|   |   |
+---+---+
|   |   |
+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+
|   |   |
+---+---+
|   |   |
+---+---+
EOF
            ),
            array(
                "TODO",
                array(array('A')), # fixme
                <<<EOF
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
EOF
            ),
            array(
                "accepts any character at the center of each face",
                array(array('A')), # fixme
                <<<EOF
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---o---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---§---+---+---+---0---+---+---+---O---+---+---+--- ---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---0---+---+
|   |   |   |   |
+---+---+---+---+
|   |   |   |   |
+---+---+---+---+
EOF
            ),
            array(
                "trying to look more like a go game, fig 2.0.1",
                array(array('A')), # fixme
                <<<EOF
+---+---+---+
|   |   |   |
+---+---+---+
|   |   |   |
+---+---+---+
|   |   |   | '
+---+---+---+   '
|   |   |   | '   '
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
|   |   |   | ,   ,
+---+---+---+   ,
|   |   |   | ,
+---+---+---+
|   |   |   |
+---+---+---+
|   |   |   |
+---+---+---+
EOF
            ),
            array(
                "trying to look more like a go game, fig 2.1.1",
                array(array('A')), # fixme
                <<<EOF
  |   |   |   |
--+---+---+---+--
  |   |   |   |
--+---+---+---+--
  |   |   |   |
--+---+---+---+--
  |   |   |   |
--+---+---+---+--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--+---+---+---+--
  |   |   |   |
--+---+---+---+--
  |   |   |   |
--+---+---+---+--
  |   |   |   |
--+---+---+---+--
  |   |   |   |
EOF
            ),
            array(
                "with stones",
                array(array('A')), # fixme
                <<<EOF
  |   |   |   |
--▒---.---.---▒--
  |   |   |   |
--.---.---.---.--
  |   | . |   |
--.---▒---.---.--
  |   |   |   |
--▒---█---.---▒--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--█---.---█---.---.---.---.---.---.---.---.---.---.---.---.---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---█---.---.---.---.---#---#---.---.---.---.---▒---.--
  |   | . |   |   |   | . |   |   |   | . |   |   |   | . |   |
--.---.---.---.---.---.---.---#---#---#---#---#---.---.---█---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---█---.---.---.---.---#---@---@---▒---.---.---.---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---.---.---.--
  |   | . |   |
--.---.---.---.--
  |   |   |   |
--█---▒---█---.--
  |   |   |   |
EOF
            ),

        );
    }
}


// 黑 Black
// 白 White
