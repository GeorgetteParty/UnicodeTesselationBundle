<?php

namespace GeorgetteParty\UnicodeTesselationBundle\Tests\Driver;

use GeorgetteParty\UnicodeTesselationBundle\Driver\Driver;

/**
 * @author Goutte
 */
abstract class DriverTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Driver
     */
    protected $driver;

    abstract public function createDriver();

    public function setUp()
    {
        $this->driver = $this->createDriver();
    }

    public function tearDown()
    {
        unset($this->driver);
    }

    /**
     * @param $message
     * @param $array
     * @param $string
     *
     * @return void
     * @dataProvider arrayToStringProvider
     */
    public function testToString($message, $array, $string)
    {
        $actual = $this->driver->toString($array);

        $this->assertEquals($string, $actual, $message);
    }

    /**
     * @param $message
     * @param $array
     * @param $string
     *
     * @return void
     * @dataProvider stringToArrayProvider
     */
    public function testToArray($message, $array, $string)
    {
        $actual = $this->driver->toArray($string);

        $this->assertEquals($array, $actual, $message);
    }


    // Override the methods below

    public function arrayToStringProvider()
    {
        $r = array();

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function stringToArrayProvider()
    {
        $r = array();

        return array_merge($this->reciprocalTransformationProvider(), $r);
    }


    public function reciprocalTransformationProvider()
    {
        return array();
    }

}