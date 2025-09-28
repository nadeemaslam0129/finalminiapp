<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TransferCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $transaction;
    public $sender_name;
    public $receiver_name;
    public $message;

    public function __construct(Transaction $transaction, $senderName, $receiverName)
    {
        $this->transaction = $transaction;
        $this->sender_name = $senderName;
        $this->receiver_name = $receiverName;
        $this->message = $transaction->sender_id === auth()->id()
            ? "You sent {$transaction->amount} to {$receiverName}"
            : "You received {$transaction->amount} from {$senderName}";
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->transaction->sender_id);
    }

    public function broadcastWith()
    {
        return [
            'transaction' => $this->transaction,
            'message' => $this->message,
            'sender_name' => $this->sender_name,
            'receiver_name' => $this->receiver_name,
        ];
    }
}
