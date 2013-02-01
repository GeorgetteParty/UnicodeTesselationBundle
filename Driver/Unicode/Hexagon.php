<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Driver\Unicode;

use GeorgetteParty\UnicodeTesselationBundle\Driver\Hexagon as HexagonBase;

/**
 * @author Goutte
 */
class Hexagon extends HexagonBase
{
    public function getBoxDrawingHorizontalLine()
    {
        return '_';
    }

    public function getBoxDrawingDiagonalBL2UR()
    {
        return '╱';
    }

    public function getBoxDrawingDiagonalBR2UL()
    {
        return '╲';
    }
}