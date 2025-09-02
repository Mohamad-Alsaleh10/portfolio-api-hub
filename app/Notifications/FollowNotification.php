<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Follow; // استيراد نموذج Follow

class FollowNotification extends Notification
{
    use Queueable;

    protected $follow;

    /**
     * Create a new notification instance.
     */
    public function __construct(Follow $follow)
    {
        $this->follow = $follow;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database']; // إرسال الإشعار إلى قاعدة البيانات
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'follow',
            'follower_id' => $this->follow->follower->id,
            'follower_name' => $this->follow->follower->name,
        ];
    }
}