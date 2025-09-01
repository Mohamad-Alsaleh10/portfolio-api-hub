<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Comment; // استيراد نموذج Comment

class CommentNotification extends Notification
{
    use Queueable;

    protected $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database']; // إرسال الإشعار إلى قاعدة البيانات
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'comment',
            'commenter_id' => $this->comment->user->id,
            'commenter_name' => $this->comment->user->name,
            'project_id' => $this->comment->project->id,
            'project_title' => $this->comment->project->title,
            'comment_content' => $this->comment->content,
        ];
    }
}
