<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Like; // استيراد نموذج Like

class LikeNotification extends Notification
{
    use Queueable;

    protected $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    public function via($notifiable)
    {
        return ['database']; // إرسال الإشعار إلى قاعدة البيانات
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'like',
            'liker_id' => $this->like->user->id,
            'liker_name' => $this->like->user->name,
            'project_id' => $this->like->project->id,
            'project_title' => $this->like->project->title,
        ];
    }
}
