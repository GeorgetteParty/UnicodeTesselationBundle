<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Driver;

interface Driver {

    public function toArray($string);
    public function toString($array);

}