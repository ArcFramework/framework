<?php

namespace Arc\Cron;

class Scheduler
{
    private $action;
    private $fromTime;
    private $schedule = 'yearly';

    public function __construct()
    {
        // Set a sensible default for the from time so we don't have to set it explicitly each time
        $this->fromTime = current_time('timestamp');
    }

    /**
     * Set the time from which the action will be scheduled
     **/
    public function after($timestamp)
    {
        $this->fromTime = $timestamp;
        return $this;
    }

    /**
     * Clear the given scheduled hook
     **/
    public function deleteHook($hook)
    {
        wp_clear_scheduled_hook($hook);
    }

    /**
     * Register the event to be run every hour
     *
     * Note: This method terminates the fluid API
     **/
    public function everyHour()
    {
        $this->schedule = 'hourly';
        $this->schedule();
    }

    /**
     * Set the Action to be run
     **/
    public function runAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function schedule()
    {
        wp_schedule_event($this->fromTime, $this->schedule, $this->action);
    }
}
