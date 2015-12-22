<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Util;

use PhpBench\Util\TimeUnit;

class TimeUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should convertTo one time unit to another.
     *
     * @dataProvider provideConvert
     */
    public function testConvert($time, $unit, $destUnit, $expectedTime)
    {
        $unit = new TimeUnit($unit, $destUnit);
        $result = $unit->toDestUnit($time);
        $this->assertEquals($expectedTime, $result);
    }

    public function provideConvert()
    {
        return array(
            array(
                60,
                TimeUnit::SECONDS,
                TimeUnit::MINUTES,
                1,
            ),
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MICROSECONDS,
                1000000,
            ),
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MILLISECONDS,
                1000,
            ),
            array(
                24,
                TimeUnit::HOURS,
                TimeUnit::DAYS,
                1,
            ),
            array(
                2.592e+8,
                TimeUnit::MILLISECONDS,
                TimeUnit::DAYS,
                3,
            ),
            array(
                24,
                TimeUnit::HOURS,
                TimeUnit::DAYS,
                1,
            ),
        );
    }

    /**
     * It should convert one time unit to another in throughput mode
     *
     * @dataProvider provideConvertThroughput
     */
    public function testConvertThroughput($time, $unit, $destUnit, $expectedThroughput)
    {
        $unit = new TimeUnit($unit, $destUnit);
        $result = $unit->toDestUnit($time, null, TimeUnit::MODE_THROUGHPUT);
        $this->assertEquals($expectedThroughput, $result);
    }

    public function provideConvertThroughput()
    {
        return array(
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MINUTES,
                60,
            ),
            array(
                60,
                TimeUnit::SECONDS,
                TimeUnit::MINUTES,
                1,
            ),
            array(
                1,
                TimeUnit::SECONDS,
                TimeUnit::MILLISECONDS,
                0.001,
            ),
            array(
                2,
                TimeUnit::MILLISECONDS,
                TimeUnit::SECONDS,
                500,
            ),
        );
    }

    /**
     * It should use the given values for getDestUnit and getMode
     */
    public function testGivenValuesModeAndDestUnit()
    {
        $unit = new TimeUnit(TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_TIME);
        $this->assertEquals(TimeUnit::SECONDS, $unit->getDestUnit(TimeUnit::SECONDS));
        $this->assertEquals(TimeUnit::MODE_THROUGHPUT, $unit->getMode(TimeUnit::MODE_THROUGHPUT));
    }

    /**
     * It should use the default values for mode and dest unit if null values are given.
     */
    public function testDefaultValuesModeAndDestUnit()
    {
        $unit = new TimeUnit(TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_THROUGHPUT);
        $this->assertEquals(TimeUnit::MINUTES, $unit->getDestUnit());
        $this->assertEquals(TimeUnit::MODE_THROUGHPUT, $unit->getMode());
    }

    /**
     * It should resolve given values to the overridden values in the case that
     * the values are overridden (dest unit and mode)
     */
    public function testResolveDestUnitAndMode()
    {
        $unit = new TimeUnit(TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_THROUGHPUT);
        $this->assertEquals(TimeUnit::MILLISECONDS, $unit->resolveDestUnit(TimeUnit::MILLISECONDS));
        $this->assertEquals(TimeUnit::MODE_TIME, $unit->getMode(TimeUnit::MODE_TIME));

        $unit->overrideDestUnit(TimeUnit::DAYS);
        $unit->overrideMode(TimeUnit::MODE_TIME);

        $this->assertEquals(TimeUnit::DAYS, $unit->resolveDestUnit(TimeUnit::MINUTES));
        $this->assertEquals(TimeUnit::MODE_TIME, $unit->resolveMode(TimeUnit::MODE_THROUGHPUT));
    }

    /**
     * It should return the destination suffix for default state.
     */
    public function testDestSuffixDefaultState()
    {
        $unit = new TimeUnit(TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_THROUGHPUT);
        $this->assertEquals('ops/m', $unit->getDestSuffix());

        $unit = new TimeUnit(TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_TIME);
        $this->assertEquals('m', $unit->getDestSuffix());
    }

    /**
     * It should return the destination suffix for a given state
     */
    public function testDestSuffixGivenState()
    {
        $unit = new TimeUnit(
            TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_THROUGHPUT
        );
        $this->assertEquals('s', $unit->getDestSuffix(
            TimeUnit::SECONDS, TimeUnit::MODE_TIME
        ));

        $unit = new TimeUnit(
            TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_TIME
        );
        $this->assertEquals('ops/ms', $unit->getDestSuffix(
            TimeUnit::MILLISECONDS, TimeUnit::MODE_THROUGHPUT
        ));
    }

    /**
     * It should format a time into a human readable string.
     */
    public function testFormat()
    {
        $unit = new TimeUnit(
            TimeUnit::SECONDS, TimeUnit::MINUTES, TimeUnit::MODE_THROUGHPUT
        );
        $result = $unit->format(30);
        $this->assertEquals(
            '2.000ops/m',
            $result
        );

        $result = $unit->format(1800, TimeUnit::HOURS, TimeUnit::MODE_TIME);
        $this->assertEquals(
            '0.500h',
            $result
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid time unit "arf"
     */
    public function testInvalidSourceFormat()
    {
        TimeUnit::convertTo(1000, 'arf', TimeUnit::MICROSECONDS);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid time unit "arf"
     */
    public function testInvalidDestFormat()
    {
        TimeUnit::convertTo(1000, TimeUnit::MICROSECONDS, 'arf');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected string value
     */
    public function testInvalidUnitType()
    {
        TimeUnit::convertTo(100, new \stdClass(), TimeUnit::MINUTES);
    }
}
