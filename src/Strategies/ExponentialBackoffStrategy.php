<?php

namespace Vivait\Backoff\Strategies;

class ExponentialBackoffStrategy extends AbstractBackoffStrategy
{

    //using the true exponential constant (e) M_E (2.73xxx)
    private $base_number = M_E;

    /**
     * @param int $retries The number of retries to calculate the backoff
     * @param bool $with_jitter Whether to include the jitter into the delay
     * @return int
     */
    public function getDelay($retries, $with_jitter = true)
    {
        if ($retries <= 0) {
            return 0;
        }

        $delay = pow($this->base_number, intval($retries));
        $jitter = 0;

        if ($with_jitter) {
            $jitter = $this->getJitter($retries);
        }

        // return max if above, jitter should be subtracted if at max to avoid thrashing
        if ($delay > $this->max_backoff) {
            return $this->max_backoff - $jitter;
        }

        // return min if below, jitter should be added if at max to avoid thrashing
        if ($delay < $this->min_backoff) {
            return $this->min_backoff + $jitter;
        }

        return (int)floor($delay + $jitter);

    }

    /**
     * @return float
     */
    public function getBaseNumber()
    {
        return $this->base_number;
    }

    /**
     * @param float $base_number
     * @throws \Exception
     */
    public function setBaseNumber($base_number)
    {
        if ($base_number <= 0) {
            throw new \Exception('Invalid Base Number Jitter');
        }
        $this->base_number = $base_number;
    }
}