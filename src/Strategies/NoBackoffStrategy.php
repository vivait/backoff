<?php

namespace Vivait\Backoff\Strategies;

class NoBackoffStrategy extends AbstractBackoffStrategy
{
    /**
     * Returns the number of seconds to delay
     *
     * @param int  $retries     The number of retries to calculate the backoff
     * @param bool $with_jitter Whether to include the jitter into the delay
     *
     * @return int
     */
    public function getDelay($retries, $with_jitter = true)
    {
        return 0;
    }
}
