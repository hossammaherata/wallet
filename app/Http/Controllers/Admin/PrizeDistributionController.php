<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PrizeDistribution;
use App\Models\PrizeDistributionWinner;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * PrizeDistributionController
 * 
 * Handles admin operations for viewing prize distributions and winners.
 * 
 * @package App\Http\Controllers\Admin
 */
class PrizeDistributionController extends BaseController
{
    /**
     * Display a listing of prize distributions.
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $authUser = $request->user();
        if ($authUser->isStore()) {
            return redirect()->route('store.dashboard');
        }

        $keyword = $request->get('keyword', null);
        $eventType = $request->get('event_type');
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = PrizeDistribution::withCount('winners');

        // Search by event ID or event title
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('event_id', 'like', "%{$keyword}%")
                    ->orWhereJsonContains('event_meta->title', $keyword)
                    ->orWhereJsonContains('event_meta->title_ar', $keyword);
            });
        }

        // Filter by event type
        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Calculate total distributions count before pagination
        $totalDistributions = (clone $query)->count();

        $distributions = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Calculate total points awarded (from successful winners only)
        // Apply same filters as the main query
        $winnersQuery = PrizeDistributionWinner::where('status', 'success')
            ->whereHas('prizeDistribution', function ($q) use ($keyword, $eventType, $status, $dateFrom, $dateTo) {
                if ($keyword) {
                    $q->where(function ($query) use ($keyword) {
                        $query->where('event_id', 'like', "%{$keyword}%")
                            ->orWhereJsonContains('event_meta->title', $keyword)
                            ->orWhereJsonContains('event_meta->title_ar', $keyword);
                    });
                }
                if ($eventType) {
                    $q->where('event_type', $eventType);
                }
                if ($status) {
                    $q->where('status', $status);
                }
                if ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                }
            });

        $totalPointsAwarded = $winnersQuery->sum('prize_amount');

        return Inertia::render('Admin/prize-distributions/Index', [
            'distributions' => $distributions,
            'statistics' => [
                'total_points_awarded' => (float) $totalPointsAwarded,
                'total_distributions' => $totalDistributions,
            ],
            'filters' => [
                'keyword' => $keyword,
                'event_type' => $eventType,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Display the specified prize distribution with all winners.
     * 
     * @param int $id
     * @return \Inertia\Response
     */
    public function show($id)
    {
        $distribution = PrizeDistribution::with(['winners.user', 'winners.transaction'])
            ->findOrFail($id);

        // Get winners grouped by status
        $winners = $distribution->winners()
            ->with(['user', 'transaction'])
            ->orderBy('position')
            ->orderBy('category')
            ->get();

        return Inertia::render('Admin/prize-distributions/Show', [
            'distribution' => $distribution,
            'winners' => $winners,
        ]);
    }
}
