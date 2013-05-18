<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Tests\Driver;

/**
 * @author Goutte
 */
class AsciiCubeTest extends DriverTestCase
{

    public function createDriver()
    {
        return new \GeorgetteParty\UnicodeTesselationBundle\Driver\Ascii\Cube();
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
                "It should interpret NULL as an empty cell",
                array(array(null)),
                <<<EOF
+---+
|   |
+---+
EOF
            ),
            array(
                "TODO",
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

        );
    }
}



