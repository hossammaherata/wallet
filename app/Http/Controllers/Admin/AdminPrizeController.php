<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Prize;
use App\Models\PrizeTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminPrizeController extends BaseController
{
    /**
     * Display a listing of prizes.
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword', null);
        $status = $request->get('status');

        $query = Prize::with(['creator', 'tickets'])
            ->withCount('tickets');

        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        $prizes = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Prizes/Index', [
            'prizes' => $prizes,
            'filters' => [
                'keyword' => $keyword,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new prize.
     */
    public function create()
    {
        return Inertia::render('Admin/Prizes/Create');
    }

    /**
     * Store a newly created prize in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
            'total_winners' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create prize
            $prize = Prize::create([
                'name' => $validated['name'],
                'dates' => $validated['dates'],
                'total_winners' => $validated['total_winners'],
                'total_amount' => $validated['total_amount'],
                'created_by' => auth()->id(),
                'status' => 'active',
            ]);

            // Create tickets for each date
            foreach ($validated['dates'] as $date) {
                PrizeTicket::create([
                    'prize_id' => $prize->id,
                    'date' => $date,
                    'total_winners' => $validated['total_winners'],
                    'total_amount' => $validated['total_amount'],
                    'remaining_amount' => $validated['total_amount'],
                    'current_winners_count' => 0,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.prizes.index')
                ->with('success', 'تم إنشاء الجائزة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء إنشاء الجائزة: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified prize.
     */
    public function show(string $id)
    {
        $prize = Prize::with([
            'creator', 
            'tickets' => function($query) {
                $query->orderBy('date', 'asc');
            },
            'tickets.winners' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'tickets.winners.user',
            'tickets.winners.addedBy',
            'tickets.winners.transaction'
        ])->findOrFail($id);

        return Inertia::render('Admin/Prizes/Show', [
            'prize' => $prize,
        ]);
    }

    /**
     * Show the form for editing the specified prize.
     */
    public function edit(string $id)
    {
        $prize = Prize::findOrFail($id);
        return Inertia::render('Admin/Prizes/Edit', [
            'prize' => $prize,
        ]);
    }

    /**
     * Update the specified prize in storage.
     */
    public function update(Request $request, string $id)
    {
        $prize = Prize::with('tickets.winners')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,completed,cancelled',
            'total_winners' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Get current dates from existing tickets
            $currentDates = $prize->tickets->pluck('date')->map(function($date) {
                return $date->format('Y-m-d');
            })->toArray();

            // Get new dates
            $newDates = $validated['dates'];

            // Update prize
            $prize->update([
                'name' => $validated['name'],
                'status' => $validated['status'],
                'total_winners' => $validated['total_winners'],
                'total_amount' => $validated['total_amount'],
                'dates' => $newDates,
            ]);

            // Delete tickets for removed dates (only if no winners)
            $datesToDelete = array_diff($currentDates, $newDates);
            foreach ($datesToDelete as $dateToDelete) {
                $ticket = $prize->tickets()->whereDate('date', $dateToDelete)->first();
                if ($ticket) {
                    // Only delete if no winners
                    $winnersCount = $ticket->winners()->count();
                    if ($winnersCount === 0) {
                        $ticket->delete();
                    }
                    // If has winners, keep the ticket but mark as cancelled
                    else {
                        $ticket->update(['status' => 'cancelled']);
                    }
                }
            }

            // Create tickets for new dates
            $datesToAdd = array_diff($newDates, $currentDates);
            foreach ($datesToAdd as $dateToAdd) {
                PrizeTicket::create([
                    'prize_id' => $prize->id,
                    'date' => $dateToAdd,
                    'total_winners' => $validated['total_winners'],
                    'total_amount' => $validated['total_amount'],
                    'remaining_amount' => $validated['total_amount'],
                    'current_winners_count' => 0,
                    'status' => 'active',
                ]);
            }

            // Update existing tickets
            $datesToUpdate = array_intersect($currentDates, $newDates);
            foreach ($datesToUpdate as $dateToUpdate) {
                $ticket = $prize->tickets()->whereDate('date', $dateToUpdate)->first();
                if ($ticket) {
                    // Calculate new remaining amount based on already awarded prizes
                    $totalAwarded = $ticket->winners()
                        ->where('status', 'success')
                        ->sum('prize_amount');
                    
                    $newRemainingAmount = max(0, $validated['total_amount'] - $totalAwarded);
                    
                    // Update current winners count
                    $currentWinnersCount = $ticket->winners()
                        ->where('status', 'success')
                        ->count();
                    
                    // Update ticket
                    $ticket->update([
                        'total_winners' => $validated['total_winners'],
                        'total_amount' => $validated['total_amount'],
                        'remaining_amount' => $newRemainingAmount,
                        'current_winners_count' => $currentWinnersCount,
                        // Update status if needed (but don't change cancelled tickets)
                        'status' => $ticket->status === 'cancelled' ? 'cancelled' : (
                            ($currentWinnersCount >= $validated['total_winners'] || $newRemainingAmount <= 0) 
                                ? 'completed' 
                                : 'active'
                        ),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.prizes.index')
                ->with('success', 'تم تحديث الجائزة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث الجائزة: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified prize from storage.
     */
    public function destroy(string $id)
    {
        $prize = Prize::findOrFail($id);
        $prize->delete();

        return redirect()->route('admin.prizes.index')
            ->with('success', 'تم حذف الجائزة بنجاح');
    }

    /**
     * Display prizes statistics.
     */
    public function statistics()
    {
        // Total points added (sum of total_amount from all prizes)
        $totalPointsAdded = Prize::sum('total_amount');

        // Total points awarded (sum of prize_amount from all successful winners)
        $totalPointsAwarded = \App\Models\PrizeWinner::where('status', 'success')
            ->sum('prize_amount');

        // Total prizes count
        $totalPrizes = Prize::count();

        // Total tickets count
        $totalTickets = PrizeTicket::count();

        // Total winners count
        $totalWinners = \App\Models\PrizeWinner::where('status', 'success')->count();

        // Prizes by status
        $prizesByStatus = Prize::select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total_amount'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'total_amount' => (float) $item->total_amount,
                ];
            });

        // Tickets by status
        $ticketsByStatus = PrizeTicket::select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total_amount'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'total_amount' => (float) $item->total_amount,
                ];
            });

        return Inertia::render('Admin/Prizes/Statistics', [
            'statistics' => [
                'total_points_added' => (float) $totalPointsAdded,
                'total_points_awarded' => (float) $totalPointsAwarded,
                'remaining_points' => (float) ($totalPointsAdded - $totalPointsAwarded),
                'total_prizes' => $totalPrizes,
                'total_tickets' => $totalTickets,
                'total_winners' => $totalWinners,
            ],
            'prizesByStatus' => $prizesByStatus,
            'ticketsByStatus' => $ticketsByStatus,
        ]);
    }
}
