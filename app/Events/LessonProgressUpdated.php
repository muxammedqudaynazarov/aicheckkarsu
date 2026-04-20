<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LessonProgressUpdated implements ShouldBroadcastNow
{
    // ShouldBroadcastNow
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lessonId;

    public function __construct($lessonId)
    {
        $this->lessonId = $lessonId;
    }

    // Xabarlar ushbu kanalga yuboriladi
    public function broadcastOn()
    {
        return new Channel('lessons');
    }

    // Event nomi
    public function broadcastAs()
    {
        return 'LessonProgressUpdatedEvent';
    }
}
