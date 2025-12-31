<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\PrizeTicket;
use App\Models\PrizeWinner;
use App\Models\User;
use App\Models\Wallet;
use App\Services\NotificationService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PrizeManagerController extends BaseController
{
    protected TransactionService $transactionService;
    protected NotificationService $notificationService;

    public function __construct(
        TransactionService $transactionService,
        NotificationService $notificationService
    ) {
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display prizes available for today.
     */
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');

        // Show all tickets for today (active, completed, but not cancelled)
        $tickets = PrizeTicket::with(['prize', 'winners'])
            ->where('date', $today)
            ->where('status', '!=', 'cancelled')
            ->whereHas('prize', function ($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('PrizeManager/Index', [
            'tickets' => $tickets,
            'today' => $today,
        ]);
    }

    /**
     * Display the specified prize ticket.
     */
    public function show(string $id)
    {
        $ticket = PrizeTicket::with([
            'prize',
            'winners.user',
            'winners.transaction'
        ])->findOrFail($id);

        // Calculate statistics
        $totalAwarded = $ticket->winners()
            ->where('status', 'success')
            ->sum('prize_amount');

        $remainingAmount = $ticket->remaining_amount;
        $currentWinnersCount = $ticket->current_winners_count;
        $remainingWinners = $ticket->total_winners - $currentWinnersCount;

        return Inertia::render('PrizeManager/Show', [
            'ticket' => $ticket,
            'statistics' => [
                'total_awarded' => (float) $totalAwarded,
                'remaining_amount' => (float) $remainingAmount,
                'current_winners_count' => $currentWinnersCount,
                'remaining_winners' => $remainingWinners,
            ],
        ]);
    }

    /**
     * Add a winner to a prize ticket.
     */
    public function addWinner(Request $request, string $ticketId)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'prize_amount' => 'required|numeric|min:0.01',
        ]);

        $ticket = PrizeTicket::with('prize')->findOrFail($ticketId);

        // Check if ticket can accept more winners
        if (!$ticket->canAcceptMoreWinners()) {
            return back()->withErrors([
                'error' => 'لا يمكن إضافة المزيد من الفائزين لهذه القسيمة'
            ]);
        }

        // Check if prize amount exceeds remaining amount
        if ($validated['prize_amount'] > $ticket->remaining_amount) {
            return back()->withErrors([
                'prize_amount' => 'قيمة الجائزة تتجاوز المبلغ المتبقي: ' . $ticket->remaining_amount
            ]);
        }

        try {
            DB::beginTransaction();

            // Find user by phone
            $user = User::where('phone', $validated['phone'])->first();

            if (!$user) {
                return back()->withErrors([
                    'phone' => 'المستخدم غير موجود في النظام'
                ]);
            }

            // Ensure user is not admin or store
            if ($user->isAdmin() || $user->isStore()) {
                return back()->withErrors([
                    'phone' => 'لا يمكن توزيع الجائزة على مستخدم إداري أو متجر'
                ]);
            }

            // Get or create wallet
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'status' => 'active',
                ]);
            }

            // Calculate current balance
            $currentBalance = $wallet->calculateBalanceFromTransactions();
            $newBalance = $currentBalance + $validated['prize_amount'];

            // Create transaction
            $referenceId = 'prize-ticket-' . $ticket->id . '-' . time();
            $transaction = $this->transactionService->create([
                'from_wallet_id' => null,
                'to_wallet_id' => $wallet->id,
                'amount' => $validated['prize_amount'],
                'type' => 'credit',
                'status' => 'success',
                'reference_id' => $referenceId,
                'meta' => [
                    'prize_ticket_id' => $ticket->id,
                    'prize_id' => $ticket->prize_id,
                    'prize_name' => $ticket->prize->name,
                    'prize_date' => $ticket->date->format('Y-m-d'),
                    'previous_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                ],
            ]);

            // Update wallet balance
            $wallet->update(['balance' => $newBalance]);

            // Create winner record
            $winner = PrizeWinner::create([
                'prize_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'prize_amount' => $validated['prize_amount'],
                'transaction_id' => $transaction->id,
                'status' => 'success',
                'added_by' => auth()->id(), // Store who added this winner
            ]);

            // Update ticket
            $ticket->update([
                'remaining_amount' => $ticket->remaining_amount - $validated['prize_amount'],
                'current_winners_count' => $ticket->current_winners_count + 1,
                'status' => ($ticket->current_winners_count + 1 >= $ticket->total_winners || 
                            ($ticket->remaining_amount - $validated['prize_amount']) <= 0) 
                            ? 'completed' : 'active',
            ]);

            // Send notification
            try {
                $this->notificationService->send(
                    $user,
                    'prize_received',
                    'تهانينا! لقد فزت بجائزة',
                    "تهانينا! لقد فزت بجائزة {$ticket->prize->name} وحصلت على {$validated['prize_amount']} نقطة. رصيدك الجديد: {$newBalance} نقطة",
                    [
                        'prize_ticket_id' => $ticket->id,
                        'prize_id' => $ticket->prize_id,
                        'prize_name' => $ticket->prize->name,
                        'prize_amount' => $validated['prize_amount'],
                        'previous_balance' => $currentBalance,
                        'new_balance' => $newBalance,
                        'transaction_id' => $transaction->id,
                    ]
                );
            } catch (\Exception $e) {
                // Log but don't fail
                \Log::warning("Failed to send prize notification", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'تم إضافة الفائز بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
}
