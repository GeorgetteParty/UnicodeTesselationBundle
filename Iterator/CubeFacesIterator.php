<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Iterator;

use GeorgetteParty\UnicodeTesselationBundle\Iterator\RecursiveIteratorIterator;

/**
 * This is an Iterator for the cubes faces' lattice multidimansional array.
 *
 * Usage :
 * $iterator = new CubeFacesIterator($myCubeFacesLatticeArray);
 * foreach( $iterator as $current ) {
 *     $keys = $iterator->keys();
 *     ...
 * }
 *
 * Class CubeFacesIterator
 * @package Goutte\SweetnessBundle\Iterator (would be)
 * @package GeorgetteParty\UnicodeTesselationBundle\Iterator
 */
class CubeFacesIterator extends RecursiveIteratorIterator
{

    /**
     * @param array $array3d
     * @param int $mode
     * @param int $flags
     * @param int $arrayIteratorFlags
     */
    public function __construct(
        $array3d,
        $mode = RecursiveIteratorIterator::LEAVES_ONLY,
        $flags = 0,
        $arrayIteratorFlags = 0
    )
    {
        $arrayIterator = new RecursiveArrayOnlyIterator($array3d, $arrayIteratorFlags);

        parent::__construct($arrayIterator, $mode, $flags);

        // hmmm...
        //$this->setMaxDepth(2);
    }

}
