<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing notification system...\n\n";

// Get first user
$user = App\Models\User::first();

if (!$user) {
    echo "❌ No user found in database\n";
    exit(1);
}

echo "✓ Found user: {$user->nama}\n";

// Get first maintenance schedule
$schedule = App\Models\MaintenanceSchedule::first();

if (!$schedule) {
    echo "❌ No maintenance schedule found in database\n";
    exit(1);
}

echo "✓ Found schedule: {$schedule->nama_tugas}\n\n";

// Send test notification
try {
    $user->notify(new App\Notifications\MaintenanceReminderNotification($schedule));
    echo "✅ Notification sent successfully!\n\n";
    
    // Check notification count
    $count = $user->notifications()->count();
    $unread = $user->unreadNotifications()->count();
    
    echo "📊 Notification Statistics:\n";
    echo "   - Total notifications: {$count}\n";
    echo "   - Unread notifications: {$unread}\n\n";
    
    // Show latest notification
    $latest = $user->notifications()->latest()->first();
    if ($latest) {
        echo "📬 Latest Notification:\n";
        echo "   - Type: " . class_basename($latest->type) . "\n";
        echo "   - Title: {$latest->data['title']}\n";
        echo "   - Message: {$latest->data['message']}\n";
        echo "   - Created: {$latest->created_at->diffForHumans()}\n";
    }
    
    echo "\n✅ Test completed successfully!\n";
} catch (\Exception $e) {
    echo "❌ Error sending notification: " . $e->getMessage() . "\n";
    exit(1);
}
