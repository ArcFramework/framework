<?php

namespace Arc\Cron;

class CronSchedules
{
    public function register()
    {
        add_filter('cron_schedules', function($schedules) {
            $schedules['every_minute'] = array(
                'interval' => 1 * 60, // 1 * 60 seconds
                'display' => __('Every Minute')
            );
            $schedules['every_fifteen_minutes'] = array(
                'interval' => 15 * 60, // 15 * 60 seconds
                'display' => __('Every 15 Minutes')
            );
            return $schedules;
        });
    }
}
