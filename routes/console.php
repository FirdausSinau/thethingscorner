<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reservations:release-expired')->everyMinute()->withoutOverlapping(5);
Schedule::command('payments:expire')->everyMinute()->withoutOverlapping(5);
Schedule::command('orders:auto-complete')->daily()->withoutOverlapping(10);
