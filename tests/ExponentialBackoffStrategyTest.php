<?php

namespace Vivait\Backoff\Tests;

use Vivait\Backoff\Strategies\ExponentialBackoffStrategy;

class ExponentialBackoffStrategyTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ExponentialBackoffStrategy */
    private $backoff;

    protected function setUp()
    {
        $this->backoff = new ExponentialBackoffStrategy();
    }

    public function testNominalDelay()
    {
        $this->assertNotNull($this->backoff->getDelay(10));
    }

    public function testPositiveJitter()
    {
        //this may fail ocassionally if rand() comes up with the same value or if the entropy of the jitter is reduced by the min/max bounds of the delay
        $this->backoff->setMaxJitter(0.5);
        $this->assertNotEquals($this->backoff->getDelay(10), $this->backoff->getDelay(10));
    }

    public function testZeroJitter()
    {
        $this->backoff->setMaxJitter(0);
        $this->assertEquals($this->backoff->getDelay(10), $this->backoff->getDelay(10));
    }

    public function testIncrementingDelay()
    {
        $this->backoff->setMinBackoff(0);
        $this->backoff->setMaxJitter(0);


        $last_result = 0;
        //if we test too close to a low retry count, it's possible to get the same result or under (esp if jitter is on)
        for ($retry = 5; $retry < 10; $retry++) {
            $result = $this->backoff->getDelay($retry);
            $this->assertGreaterThan($last_result, $result);
        }
    }

    public function testMaxDelay()
    {
        $max = 1000;
        $this->backoff->setMaxBackoff($max);
        $this->assertNotEquals($this->backoff->getDelay(5, false), $max);
        $this->assertEquals($this->backoff->getDelay(10, false), $max);
    }

    public function testMinDelay()
    {
        $min = 10;
        $this->backoff->setMinBackoff($min);
        $this->assertEquals($min, $this->backoff->getDelay(1, false));
        $this->assertNotEquals($min, $this->backoff->getDelay(10, false));
    }

    public function testIntegerResult()
    {
        for ($retry = 0; $retry < 50; $retry++) {
            $this->assertInternalType('int', $this->backoff->getDelay($retry));
        }
    }

    public function testZeroDelayWithNoJitter()
    {
        $this->assertEquals(0, $this->backoff->getDelay(0, false));
    }

    public function testExceededRetryCount()
    {
        $this->backoff->setMaxRetries(10);
        $this->assertFalse($this->backoff->hasExceededRetries(10));
        $this->assertTrue($this->backoff->hasExceededRetries(11));
    }

    public function testExceededRetryTime()
    {
        $this->backoff->setMaxBackoff(1000);
        $this->assertFalse($this->backoff->hasExceededRetries(3));
        $this->assertTrue($this->backoff->hasExceededRetries(100));
    }

}
