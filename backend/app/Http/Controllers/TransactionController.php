<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Events\TransferCompleted;

class TransactionController extends Controller
{
     public function notifications(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'transactions' => $transactions,
            'balance' => $user->balance
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $sender = $request->user();
        if (!$sender) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $receiver = User::findOrFail($request->receiver_id);
        $amount = round($request->amount, 2);
        $commission = round($amount * 0.015, 2);
        $totalDebit = $amount + $commission;

        try {
            DB::transaction(function () use ($sender, $receiver, $amount, $commission, $totalDebit) {
                // Lock for concurrency
                $sender = User::lockForUpdate()->find($sender->id);
                $receiver = User::lockForUpdate()->find($receiver->id);

                if ($sender->balance < $totalDebit) {
                    throw new \Exception('Insufficient balance');
                }

                // Update balances
                $sender->balance -= $totalDebit;
                $receiver->balance += $amount;
                $sender->save();
                $receiver->save();

                // Create transaction
                $transaction = Transaction::create([
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiver->id,
                    'amount' => $amount,
                    'commission_fee' => $commission,
                ]);

                // Create notifications
                Notification::create([
                    'user_id' => $sender->id,
                    'message' => "You sent $amount to User {$receiver->id}",
                ]);

                Notification::create([
                    'user_id' => $receiver->id,
                    'message' => "You received $amount from User {$sender->id}",
                ]);

                // Broadcast event
                broadcast(new TransferCompleted($transaction, $sender, $receiver))->toOthers();
            });

            return response()->json(['message' => 'Transfer successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
