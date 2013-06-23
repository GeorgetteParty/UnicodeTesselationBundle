<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Iterator;

/**
 * Useful when you have objects as values indexed in a multidimensional array,
 * and you use RecursiveIteratorIterator::LEAVES_ONLY
 *
 * Our iterator will not try to iterate through our objects.
 *
 * Fun fact : this was _not_ copied from the php doc,
 * even if there's a verbatim copy there.
 *
 * Class RecursiveArrayOnlyIterator
 * @package GeorgetteParty\UnicodeTesselationBundle\Iterator
 */
class RecursiveArrayOnlyIterator extends \RecursiveArrayIterator {

    /**
     * Be recursive only on vanilla arrays
     * Returns false for ArrayObjects, which is what we want
     *
     * @return bool
     */
    public function hasChildren()
    {
        return is_array($this->current());
    }

}