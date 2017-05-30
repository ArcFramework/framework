<?php

namespace Arc\Cron;

use Arc\Hooks\Filters;

class CronSchedules
{
    protected $filters;

    public function __construct(Filters $filters)
    {
        $this->filters = $filters;
    }

    public function register()
    {
        $this->filters->add('cron_schedules', function ($schedules) {
            $schedules['every_minute'] = [
                'interval' => 1 * 60, // 1 * 60 seconds
                'display'  => __('Every Minute'),
            ];
            $schedules['every_5_minutes'] = [
                'interval' => 5 * 60, // 5 * 60 seconds
                'display'  => __('Every 5 Minutes'),
            ];
            $schedules['every_10_minutes'] = [
                'interval' => 10 * 60, // 10 * 60 seconds
                'display'  => __('Every 10 Minutes'),
            ];
            $schedules['every_15_minutes'] = [
                'interval' => 15 * 60, // 15 * 60 seconds
                'display'  => __('Every 15 Minutes'),
            ];

            return $schedules;
        });
    }
}
