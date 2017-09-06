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
        $this->fromTime = strtotime('now');
    }

    /**
     * Set the time from which the action will be scheduled.
     **/
    public function after($timestamp)
    {
        $this->fromTime = $timestamp;

        return $this;
    }

    /**
     * Clear the given scheduled hook.
     **/
    public function deleteHook($hook)
    {
        wp_clear_scheduled_hook($hook);
    }

    /**
     * Register the event to be run every minute.
     *
     * Note: This method terminates the fluent API
     **/
    public function everyMinute()
    {
        $this->schedule = 'every_minute';
        $this->schedule();
    }

    /**
     * Register the event to be run every minute.
     *
     * Note: This method terminates the fluent API
     **/
    public function every5Minutes()
    {
        $this->schedule = 'every_5_minutes';
        $this->schedule();
    }

    /**
     * Register the event to be run every ten minutes.
     *
     * Note: This method terminates the fluent API
     **/
    public function every10Minutes()
    {
        $this->schedule = 'every_10_minutes';
        $this->schedule();
    }

    /**
     * Register the event to be run every 15 minutes.
     *
     * Note: This method terminates the fluent API
     **/
    public function every15Minutes()
    {
        $this->schedule = 'every_15_minutes';
        $this->schedule();
    }

    /**
     * Register the event to be run every hour.
     *
     * Note: This method terminates the fluent API
     **/
    public function everyHour()
    {
        $this->schedule = 'hourly';
        $this->schedule();
    }

    /**
     * Register the event to be run every hour.
     *
     * Note: This method terminates the fluent API
     **/
    public function everyWeek()
    {
        $this->schedule = 'weekly';
        $this->schedule();
    }

    /**
     * Set the Action to be run.
     **/
    public function runAction($action)
    {
        $this->action = $action;

        return $this;
    }

    public function schedule()
    {
        // If it's already scheduled at the given frequency we don't need to do anything
        if (wp_get_schedule($this->action) == $this->schedule) {
            return;
        }

        wp_schedule_event($this->fromTime, $this->schedule, $this->action);
    }
}
