<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Tests\Driver;

// fixme 9h15 - 10h01
use GeorgetteParty\UnicodeTesselationBundle\Iterator\CubeFacesIterator;
use RecursiveArrayIterator;
use GeorgetteParty\UnicodeTesselationBundle\Iterator\RecursiveIteratorIterator;




/**
 * These are the tests for a quadsphere's faces' lattice text representation.
 * We use the net of the cube shaped like a T laying with the head on the left : ⊢
 *
 * We can either draw lines and vertices around our tiles (drawing the edges and vertices of the quadsphere),
 * or draw the quadsphere's dual, ie. lines connecting our tiles.
 *
 * Dual modelisation is
 * - closer to go æsthetically
 * - drawing the connections
 * - isomorphic with the tiles' one (and sharing the same PHP modelisation)
 * but
 * - requires an effort to understand the folding of the "patron"
 *   this is mitigated by drawing 'hairs', outwarding segments
 *
 * Let's roll with dual, then, for starters, accepting only a single character per tile
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
 * tiles[2][1][-1]
 *
 * @author Goutte
 */
class AsciiCubeTest extends DriverTestCase
{

    public function createDriver()
    {
        return new \GeorgetteParty\UnicodeTesselationBundle\Driver\Cube();
    }


    protected function getDumpedMap(){
        return $this->driver->toArray(<<<EOF
  |   |   |   |
--▒---.---.---▒--
  |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---▒---.---.--
  |   |   |   |
--▒---█---.---▒--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--█---.---█---.---.---.---.---.---.---.---.---.---.---.---.---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---█---.---.---.---.---!---!---.---.---.---.---▒---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---.---.---.---.---!---!---!---!---!---.---.---█---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---█---■---□---.---.---!---!---!---▒---.---.---.---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---.---.---.--
  |   |   |   |
--█---▒---█---.--
  |   |   |   |
EOF
        );
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
     * Used to build the imbricated array[x][y][z] = value
     * Arrays will be sorted with increasing keys (min negative to max positive)
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

        $this->sortMultiArrayByKeys($r);

        return $r;
    }


    /**
     * eg :
     * array('X',  1,  2,  1),\n
     * ad nauseam
     */
    public function notestDumpTestArrayInput()
    {
        $tiles = $this->getDumpedMap();

        // iterator over tiles, intsort by x then y then z.
        $iterator = new CubeFacesIterator($tiles);

        foreach( $iterator as $current ) {
            $keys = $iterator->keys();
            echo "array('{$current}',  {$keys[0]},  {$keys[1]},  {$keys[2]})," . PHP_EOL;
        }
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
                "0 subdivisions, using weird chars (█)",
                $this->buildArrayFromFlat(array(
                    array('█',  0,  1,  0),
                    array('B',  0,  0, -1),
                    array('C',  0, -1,  0),
                    array('D',  1,  0,  0),
                    array('E',  0,  0,  1),
                    array('F', -1,  0,  0),
                )),
                <<<EOF
  |
--█--
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


            array(
               "Using sample stones",
                $this->buildArrayFromFlat(array(
                    array('.',  -4,  -3,  -3),
                    array('.',  -4,  -3,  -1),
                    array('.',  -4,  -3,  1),
                    array('.',  -4,  -3,  3),
                    array('.',  -4,  -1,  -3),
                    array('█',  -4,  -1,  -1),
                    array('.',  -4,  -1,  1),
                    array('.',  -4,  -1,  3),
                    array('.',  -4,  1,  -3),
                    array('▒',  -4,  1,  -1),
                    array('.',  -4,  1,  1),
                    array('.',  -4,  1,  3),
                    array('.',  -4,  3,  -3),
                    array('.',  -4,  3,  -1),
                    array('.',  -4,  3,  1),
                    array('.',  -4,  3,  3),
                    array('.',  -3,  -4,  -3),
                    array('.',  -3,  -4,  -1),
                    array('.',  -3,  -4,  1),
                    array('█',  -3,  -4,  3),
                    array('.',  -3,  -3,  -4),
                    array('▒',  -3,  -3,  4),
                    array('.',  -3,  -1,  -4),
                    array('!',  -3,  -1,  4),
                    array('.',  -3,  1,  -4),
                    array('.',  -3,  1,  4),
                    array('█',  -3,  3,  -4),
                    array('.',  -3,  3,  4),
                    array('▒',  -3,  4,  -3),
                    array('.',  -3,  4,  -1),
                    array('.',  -3,  4,  1),
                    array('▒',  -3,  4,  3),
                    array('.',  -1,  -4,  -3),
                    array('.',  -1,  -4,  -1),
                    array('.',  -1,  -4,  1),
                    array('▒',  -1,  -4,  3),
                    array('.',  -1,  -3,  -4),
                    array('!',  -1,  -3,  4),
                    array('.',  -1,  -1,  -4),
                    array('!',  -1,  -1,  4),
                    array('.',  -1,  1,  -4),
                    array('.',  -1,  1,  4),
                    array('.',  -1,  3,  -4),
                    array('.',  -1,  3,  4),
                    array('█',  -1,  4,  -3),
                    array('▒',  -1,  4,  -1),
                    array('.',  -1,  4,  1),
                    array('.',  -1,  4,  3),
                    array('.',  1,  -4,  -3),
                    array('.',  1,  -4,  -1),
                    array('.',  1,  -4,  1),
                    array('█',  1,  -4,  3),
                    array('.',  1,  -3,  -4),
                    array('!',  1,  -3,  4),
                    array('.',  1,  -1,  -4),
                    array('!',  1,  -1,  4),
                    array('.',  1,  1,  -4),
                    array('!',  1,  1,  4),
                    array('█',  1,  3,  -4),
                    array('.',  1,  3,  4),
                    array('.',  1,  4,  -3),
                    array('.',  1,  4,  -1),
                    array('.',  1,  4,  1),
                    array('.',  1,  4,  3),
                    array('.',  3,  -4,  -3),
                    array('.',  3,  -4,  -1),
                    array('.',  3,  -4,  1),
                    array('.',  3,  -4,  3),
                    array('█',  3,  -3,  -4),
                    array('!',  3,  -3,  4),
                    array('.',  3,  -1,  -4),
                    array('!',  3,  -1,  4),
                    array('█',  3,  1,  -4),
                    array('!',  3,  1,  4),
                    array('.',  3,  3,  -4),
                    array('.',  3,  3,  4),
                    array('▒',  3,  4,  -3),
                    array('.',  3,  4,  -1),
                    array('.',  3,  4,  1),
                    array('▒',  3,  4,  3),
                    array('■',  4,  -3,  -3),
                    array('□',  4,  -3,  -1),
                    array('.',  4,  -3,  1),
                    array('.',  4,  -3,  3),
                    array('.',  4,  -1,  -3),
                    array('.',  4,  -1,  -1),
                    array('.',  4,  -1,  1),
                    array('!',  4,  -1,  3),
                    array('.',  4,  1,  -3),
                    array('.',  4,  1,  -1),
                    array('.',  4,  1,  1),
                    array('.',  4,  1,  3),
                    array('.',  4,  3,  -3),
                    array('.',  4,  3,  -1),
                    array('.',  4,  3,  1),
                    array('.',  4,  3,  3),
                )),
                <<<EOF
  |   |   |   |
--▒---.---.---▒--
  |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---▒---.---.--
  |   |   |   |
--▒---█---.---▒--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--█---.---█---.---.---.---.---.---.---.---.---.---.---.---.---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---█---.---.---.---.---!---!---.---.---.---.---▒---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---.---.---.---.---!---!---!---!---!---.---.---█---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---█---■---□---.---.---!---!---!---▒---.---.---.---.--
  |   |   |   |   |   |   |   |   |   |   |   |   |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---.---.---.--
  |   |   |   |
--.---.---.---.--
  |   |   |   |
--█---▒---█---.--
  |   |   |   |
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
//                array(array('A')),
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
//                array(array('A')),
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
//                "trying to look more like a go game, fig 2.1.1",
//                array(array('A')),
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
//--+---+---+---+---+---+---+---!---+---+---+---+---+---+---+---+--
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


        );
    }
}


// 黑 Black
// 白 White

