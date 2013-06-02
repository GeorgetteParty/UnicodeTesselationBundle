<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Iterator;

use RecursiveIteratorIterator as OriginalRecursiveIteratorIterator;

/**
 * This is a sweetened RecursiveIteratorIterator.
 *
 * Provides :
 * - keys() a handy getter for all the keys from up in the parent tree
 *
 * Usage :
 * $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($myCubeSurfaceLatticeArray));
 * foreach( $iterator as $current ) {
 *     $keys = $iterator->keys();
 *     ...
 * }
 *
 * Class RecursiveIteratorIterator
 * @package Goutte\SweetnessBundle\Iterator (would be)
 * @package GeorgetteParty\UnicodeTesselationBundle\Iterator
 */
class RecursiveIteratorIterator extends OriginalRecursiveIteratorIterator {

    /**
     * Array of the keys in the parent tree, from root to current.
     * Example of the order :
     * [
     *   a => [
     *      b => [
     *        c => 0, # a b c
     *        d => 0, # a b d
     *      ],
     *      e => [
     *        f => 1, # a e f
     *      ],
     *   ],
     *   g => [
     *     h => 7, # g h
     *   ],
     * ]
     * @return array
     */
    public function keys() {
        $keys = array();
        for ($i = 0; $i < $this->getDepth(); $i++) {
            $keys[] = $this->getSubIterator($i)->key();
        }
        $keys[] = $this->key();

        return $keys;
    }

    // UNUSED / NOT WORKING
    public function setCurrent($value)
    {
        $keys = $this->keys();

        // fixme : find out if we can access by reference the multiarray passed to the innerIterator

        for ($i =& $array; $key = array_shift($keys); $i =& $i[$key]) {
            if (!isset($i[$key])) $i[$key] = array();
        }
        $i = $value;

    }

}
