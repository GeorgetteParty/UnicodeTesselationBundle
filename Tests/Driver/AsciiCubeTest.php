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
 * but
 * - requires an effort to understand the folding of the "patron"
 * - accepts only a single character per tile (otherwise it'll be messy I expect)
 *
 *
 * About the coordinate system
 * ( 0, 0, 0 ) is the center of the cube.
 * On the center of each face the coordinates would be :
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
 * oriented as such (for when there are subdivisions) :
    +-------+
    |   △   |
    | x + ▷ |
    |   z   |
    +-------+-------+-------+-------+
    |   △   |   △   |   △   |   △   |
    | x + ▷ | z + ▷ | ◁ + x | ◁ + z |
    |   y   |   y   |   y   |   y   |
    +-------+-------+-------+-------+
    |   z   |
    | x + ▷ |
    |   ▽   |
    +-------+
 *
 * Characters
 * - white : W ▒ □ ◯
 * - black : B █ ■ ●
 *
 * @author Goutte
 */
 // fixme 9h15 - 10h01
class AsciiCubeTest extends DriverTestCase
{

    public function createDriver()
    {
        return new \GeorgetteParty\UnicodeTesselationBundle\Driver\Cube();
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

    /**
     * Used to build the imbricated array[x][y][z] = value
     *
     * @param $array
     * @return array
     */
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
        );

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function stringToArrayProvider()
    {
        $r = array(

        );

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function failuresProvider()
    {
        return array(
            array(
                "",
            ),
        );
    }


    public function reciprocalTransformationProvider()
    {
        return array(

            array(
                "0 subdivisions",
                $this->buildArrayFromFlat(array(
                    array('A',  0,  1,  0),
                    array('B',  0,  0, -1),
                    array('C',  0, -1,  0),
                    array('D',  1,  0,  0),
                    array('E',  0,  0,  1),
                    array('F', -1,  0,  0),
                )),
                <<<EOF
  |
--A--
  |   |   |   |
--B---D---E---F--
  |   |   |   |
--C--
  |
EOF
            ),
            array(
                "1 subdivision",
                $this->buildArrayFromFlat(array(
                    array('P', -1,  2,  1),
                    array('A', -1,  2, -1),
                    array('G',  1,  2, -1),
                    array('Q',  1,  2,  1),

                    array('B', -1,  1, -2),
                    array('C', -1, -1, -2),
                    array('H',  1, -1, -2),
                    array('D',  1,  1, -2),

                    array('I', -1, -2, -1),
                    array('M', -1, -2,  1),
                    array('N',  1, -2,  1),
                    array('J',  1, -2, -1),

                    array('E',  2,  1, -1),
                    array('K',  2, -1, -1),
                    array('L',  2, -1,  1),
                    array('F',  2,  1,  1),

                    array('R',  1,  1,  2),
                    array('S',  1, -1,  2),
                    array('U', -1, -1,  2),
                    array('T', -1,  1,  2),

                    array('V', -2,  1,  1),
                    array('W', -2, -1,  1),
                    array('Y', -2, -1, -1),
                    array('X', -2,  1, -1),
                )),
                <<<EOF
  |   |
--P---Q--
  |   |
--A---G--
  |   |   |   |   |   |   |   |
--B---D---E---F---R---T---V---X--
  |   |   |   |   |   |   |   |
--C---H---K---L---S---U---W---Y--
  |   |   |   |   |   |   |   |
--I---J--
  |   |
--M---N--
  |   |
EOF
            ),
//            array(
//                "fig 1.0.0",
//                array(array('A')),
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
//            array(
//                "0 subdivisions",
//                array(array('A')),
//                <<<EOF
//  |
//--A--
//  |   |   |   |
//--B---D---E---F--
//  |   |   |   |
//--C--
//  |
//EOF
//            ),
//            array(
//                "TODO",
//                array(array('A')), # fixme
//                <<<EOF
//+---+---+
//|   |   |
//+---+---+
//|   |   |
//+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+
//|   |   |
//+---+---+
//|   |   |
//+---+---+
//EOF
//            ),
//            array(
//                "TODO",
//                array(array('A')), # fixme
//                <<<EOF
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//EOF
//            ),
//            array(
//                "accepts any character at the center of each face",
//                array(array('A')), # fixme
//                <<<EOF
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---o---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---§---+---+---+---0---+---+---+---O---+---+---+--- ---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---0---+---+
//|   |   |   |   |
//+---+---+---+---+
//|   |   |   |   |
//+---+---+---+---+
//EOF
//            ),
//            array(
//                "trying to look more like a go game, fig 2.0.1",
//                array(array('A')), # fixme
//                <<<EOF
//+---+---+---+
//|   |   |   |
//+---+---+---+
//|   |   |   |
//+---+---+---+
//|   |   |   | '
//+---+---+---+   '
//|   |   |   | '   '
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+
//|   |   |   | ,   ,
//+---+---+---+   ,
//|   |   |   | ,
//+---+---+---+
//|   |   |   |
//+---+---+---+
//|   |   |   |
//+---+---+---+
//EOF
//            ),
//            array(
//                "trying to look more like a go game, fig 2.1.1",
//                array(array('A')), # fixme
//                <<<EOF
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--+---+---+---+---+---+---+---+---+---+---+---+---+---+---+---+--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//--+---+---+---+--
//  |   |   |   |
//EOF
//            ),
//            array(
//                "with stones",
//                array(array('A')), # fixme
//                <<<EOF
//  |   |   |   |
//--▒---.---.---▒--
//  |   |   |   |
//--.---.---.---.--
//  |   | + |   |
//--.---▒---.---.--
//  |   |   |   |
//--▒---█---.---▒--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--█---.---█---.---.---.---.---.---.---.---.---.---.---.---.---.--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--.---.---.---█---.---.---.---.---#---#---.---.---.---.---▒---.--
//  |   | + |   |   |   | + |   |   |   | + |   |   |   | + |   |
//--.---.---.---.---.---.---.---#---#---#---#---#---.---.---█---.--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--.---.---.---█---■---□---.---.---#---@---@---▒---.---.---.---.--
//  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
//--.---.---.---.--
//  |   |   |   |
//--.---.---.---.--
//  |   | + |   |
//--.---.---.---.--
//  |   |   |   |
//--█---▒---█---.--
//  |   |   |   |
//EOF
//            ),

        );
    }
}


// 黑 Black
// 白 White
