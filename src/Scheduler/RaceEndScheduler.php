<?php

namespace App\Scheduler;

use App\Message\RaceEndMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule(name: 'default')]
class RaceEndScheduler implements ScheduleProviderInterface
{
    public function __construct()
    {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())->with(
            RecurringMessage::every('1 second', new RaceEndMessage())
        );
    }
}
