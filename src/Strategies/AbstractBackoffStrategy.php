<?php

namespace Vivait\Backoff\Strategies;


abstract class AbstractBackoffStrategy
{

    protected $jitter_multiplier;
    protected $max_backoff;
    protected $min_backoff;
    private $max_retries;

    /**
     * AbstractBackoffStrategy constructor.
     * @param float $jitter_multiplier
     * @param int $max_backoff
     * @param int $min_backoff
     * @param int $max_retries
     */
    public function __construct($jitter_multiplier = 0.1, $max_backoff = 15552000, $min_backoff = 1, $max_retries = null)
    {
        // 15552000 seconds is 6 months
        $this->jitter_multiplier = $jitter_multiplier;
        $this->max_backoff = $max_backoff;
        $this->min_backoff = $min_backoff;
        $this->max_retries = $max_retries;
    }

    /**
     * Returns the number of seconds to delay
     * @param int $retries The number of retries to calculate the backoff
     * @param bool $with_jitter Whether to include the jitter into the delay
     * @return int
     */
    abstract public function getDelay($retries, $with_jitter = true);

    /**
     * @return int
     */
    public function getMaxJitter()
    {
        return $this->jitter_multiplier;
    }

    /**
     * @param int $jitter_multiplier
     * @throws \Exception
     */
    public function setMaxJitter($jitter_multiplier)
    {
        if ($jitter_multiplier < 0 || $jitter_multiplier > 0.5) {
            throw new \Exception('Invalid Maximum Jitter Multipler');
        }
        $this->jitter_multiplier = $jitter_multiplier;
    }

    /**
     * @return int
     */
    public function getMaxBackoff()
    {
        return $this->max_backoff;
    }

    /**
     * @param int $max_backoff
     * @throws \Exception
     */
    public function setMaxBackoff($max_backoff)
    {
        if ($max_backoff < 0) {
            throw new \Exception('Invalid Maximum Backoff');
        }
        $this->max_backoff = $max_backoff;
    }

    /**
     * @return int
     */
    public function getMinBackoff()
    {
        return $this->min_backoff;
    }

    /**
     * @param int $min_backoff
     * @throws \Exception
     */
    public function setMinBackoff($min_backoff)
    {
        if ($min_backoff < 0) {
            throw new \Exception('Invalid Minimum Backoff');
        }
        $this->min_backoff = $min_backoff;
    }

    /**
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->max_retries;
    }

    /**
     * @param int $max_retries
     */
    public function setMaxRetries($max_retries)
    {
        $this->max_retries = $max_retries;
    }

    /**
     * Return a random number between 0 and jitter_multiplier %
     * @param $retries
     * @return int
     */
    protected function getJitter($retries)
    {
        return rand(0, $this->getDelay($retries, false) * $this->jitter_multiplier);
    }

    /**
     * Returns bool to indicate whether the processes has exceeded the maximum number of retries
     * @param $retries
     * @return bool
     */
    public function hasExceededRetries($retries)
    {
        $delay = $this->getDelay($retries, false);

        return (
            (!$this->max_retries && ($delay >= $this->max_backoff)) ||
            ($this->max_retries && ($retries > $this->max_retries))
        );

    }


    /**
     * Returns an array with the delay/retry profile for investigation/analysis
     * @param $max_retries
     * @return mixed
     */
    public function getDelayProfile($max_retries)
    {
        $profile = [];
        for ($retry = 0; $retry <= $max_retries; $retry++) {
            $profile[$retry] = [
                'delay' => $this->getDelay($retry),
                'retry' => ($this->hasExceededRetries($retry) ? 'FAIL' : 'RETRY')
            ];
        }

        return $profile;
    }

}