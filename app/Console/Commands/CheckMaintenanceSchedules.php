<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Notifications\MaintenanceReminderNotification;
use App\Notifications\MaintenanceOverdueNotification;
use Illuminate\Console\Command;

class CheckMaintenanceSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check maintenance schedules and send notifications for upcoming and overdue tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking maintenance schedules...');
        
        $reminderCount = 0;
        $overdueCount = 0;

        // Get all active schedules with notifications enabled
        $schedules = MaintenanceSchedule::where('is_aktif', true)
            ->where('enable_notifikasi', true)
            ->with(['user', 'maintenanceable'])
            ->get();

        $this->info("Found {$schedules->count()} active schedules with notifications enabled.");

        foreach ($schedules as $schedule) {
            $today = now()->startOfDay();
            $dueDate = $schedule->tanggal_berikutnya;
            $notificationDays = $schedule->notifikasi_hari_sebelumnya ?? 3;

            // Calculate the notification date (X days before due date)
            $notificationDate = $dueDate->copy()->subDays($notificationDays)->startOfDay();

            // Check if schedule is overdue
            if ($dueDate->lt($today)) {
                $this->warn("Schedule #{$schedule->id} is overdue: {$schedule->nama_tugas}");
                
                // Check if we already sent an overdue notification today
                if (!$this->hasNotificationToday($schedule, 'MaintenanceOverdueNotification')) {
                    $schedule->user->notify(new MaintenanceOverdueNotification($schedule));
                    $overdueCount++;
                    $this->line("  ✓ Sent overdue notification to {$schedule->user->nama}");
                }
            }
            // Check if today is the notification date (or we've passed it but not yet overdue)
            elseif ($today->gte($notificationDate) && $today->lt($dueDate)) {
                $daysUntil = $today->diffInDays($dueDate, false);
                $this->info("Schedule #{$schedule->id} due in {$daysUntil} days: {$schedule->nama_tugas}");
                
                // Check if we already sent a reminder notification today
                if (!$this->hasNotificationToday($schedule, 'MaintenanceReminderNotification')) {
                    $schedule->user->notify(new MaintenanceReminderNotification($schedule));
                    $reminderCount++;
                    $this->line("  ✓ Sent reminder notification to {$schedule->user->nama}");
                }
            }
        }

        $this->newLine();
        $this->info("✅ Notification Summary:");
        $this->table(
            ['Type', 'Count'],
            [
                ['Reminders Sent', $reminderCount],
                ['Overdue Alerts Sent', $overdueCount],
                ['Total Notifications', $reminderCount + $overdueCount],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Check if a notification was already sent today for this schedule
     */
    private function hasNotificationToday(MaintenanceSchedule $schedule, string $notificationType): bool
    {
        return $schedule->user->notifications()
            ->whereDate('created_at', now()->toDateString())
            ->where('type', 'App\\Notifications\\' . $notificationType)
            ->whereRaw("JSON_EXTRACT(data, '$.schedule_id') = ?", [$schedule->id])
            ->exists();
    }
}
