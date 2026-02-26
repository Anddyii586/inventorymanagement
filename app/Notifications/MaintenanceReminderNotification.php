<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MaintenanceSchedule $schedule
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $asset = $this->schedule->maintenanceable;
        $assetName = $asset?->nama_barang 
            ?? $asset?->jenis_jaringan 
            ?? $asset?->judul_buku 
            ?? $asset?->id 
            ?? '-';

        return (new MailMessage)
            ->subject('Reminder: Pemeliharaan Aset Terjadwal')
            ->greeting('Halo ' . $notifiable->nama . ',')
            ->line('Ini adalah pengingat untuk pemeliharaan aset yang dijadwalkan.')
            ->line('**Detail Pemeliharaan:**')
            ->line('- Tugas: ' . $this->schedule->nama_tugas)
            ->line('- Aset: ' . $assetName . ' (' . $this->schedule->maintenanceable_id . ')')
            ->line('- Frekuensi: ' . $this->schedule->frekuensi)
            ->line('- Tanggal Terjadwal: ' . $this->schedule->tanggal_berikutnya->format('d/m/Y'))
            ->action('Lihat Jadwal', url('/admin/maintenance-schedules/' . $this->schedule->id . '/edit'))
            ->line('Pastikan pemeliharaan dilakukan tepat waktu untuk menjaga kondisi aset.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $asset = $this->schedule->maintenanceable;
        $assetName = $asset?->nama_barang 
            ?? $asset?->jenis_jaringan 
            ?? $asset?->judul_buku 
            ?? $asset?->id 
            ?? '-';

        $daysUntil = now()->diffInDays($this->schedule->tanggal_berikutnya, false);
        
        return [
            'schedule_id' => $this->schedule->id,
            'title' => 'Reminder Pemeliharaan',
            'message' => "Pemeliharaan \"{$this->schedule->nama_tugas}\" untuk aset {$assetName} dijadwalkan dalam {$daysUntil} hari.",
            'asset_id' => $this->schedule->maintenanceable_id,
            'asset_type' => $this->schedule->maintenanceable_type,
            'asset_name' => $assetName,
            'task_name' => $this->schedule->nama_tugas,
            'due_date' => $this->schedule->tanggal_berikutnya->format('Y-m-d'),
            'frequency' => $this->schedule->frekuensi,
            'days_until' => $daysUntil,
            'action_url' => url('/admin/maintenance-schedules/' . $this->schedule->id . '/edit'),
        ];
    }
}
