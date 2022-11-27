<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;



use App\Models\Reminder;
use App\Models\User;
use App\Jobs\ProcessSendNotification;
use App\Models\EmailLog;
use Carbon\Carbon;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        function is_same_time($date)
        {
            $today =  Carbon::now(config('app.timezone'));
            $isSameDay = $today->isSameDay($date);
            $isSameHour = $today->isSameHour($date);
            $isSameMinute = $today->isSameMinute($date);
            return $isSameDay && $isSameHour && $isSameMinute;
        }

        $schedule->call(function () {

            $reminders = Reminder::where('send_notification', 1)
                ->join('users', 'users.id', '=', 'reminders.user_id')
                ->select(
                    'reminders.id as reminder_id',
                    'reminders.user_id',
                    'users.name',
                    'users.email',
                    'reminders.title',
                    'reminders.description',
                    'reminders.send_time',
                    'reminders.send_notification',
                )
                ->get();


            $reminders->each(function ($item, $key) {
                $date = $item->send_time;
                if (is_same_time($date)) {
                    $description = $item->description ? $item->description : '無備註';

                    $details = [
                        'email' => $item->email,
                        'title' => $item->title,
                        'description' => $description,

                    ];
                    ProcessSendNotification::dispatch($details);

                    $email_logs = new EmailLog();
                    $email_logs->create(
                        [
                            'user_id' => $item->user_id,
                            'reminder_id' => $item->reminder_id,
                            'title' => $item->title,
                            'description' => $item->description,
                            'sent_at' => Carbon::now(config('app.timezone')),
                        ]
                        );
                };
            });
        })->everyMinute();
        // ->runInBackground();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
