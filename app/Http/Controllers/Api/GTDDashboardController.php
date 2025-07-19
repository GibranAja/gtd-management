<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Project;
use App\Models\Context;
use App\Models\WeeklyReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GTDDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        // Get counts with optimized queries
        $counts = [
            'inbox' => Item::inbox()->where('user_id', $userId)->count(),
            'next_actions' => Item::nextActions()->where('user_id', $userId)->count(),
            'waiting_for' => Item::waitingFor()->where('user_id', $userId)->count(),
            'someday_maybe' => Item::somedayMaybe()->where('user_id', $userId)->count(),
            'active_projects' => Project::where('user_id', $userId)->where('status', 'active')->count(),
            'completed_projects' => Project::where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        // Get overdue and due items
        $overdueItems = Item::overdue()
            ->where('user_id', $userId)
            ->select('id', 'title', 'due_date', 'project_id', 'context_id')
            ->with(['project:id,title', 'context:id,name,color'])
            ->limit(5)
            ->get();

        $dueTodayItems = Item::dueToday()
            ->where('user_id', $userId)
            ->select('id', 'title', 'due_date', 'project_id', 'context_id')
            ->with(['project:id,title', 'context:id,name,color'])
            ->limit(5)
            ->get();

        $dueThisWeekItems = Item::dueThisWeek()
            ->where('user_id', $userId)
            ->select('id', 'title', 'due_date', 'project_id', 'context_id')
            ->with(['project:id,title', 'context:id,name,color'])
            ->limit(10)
            ->get();

        // Get recent activity
        $recentItems = Item::where('user_id', $userId)
            ->select('id', 'title', 'type', 'status', 'created_at', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get context breakdown
        $contextBreakdown = Context::select('id', 'name', 'color')
            ->where('user_id', $userId)
            ->withCount(['activeItems'])
            ->having('active_items_count', '>', 0)
            ->orderBy('active_items_count', 'desc')
            ->get();

        // Get project progress
        $activeProjects = Project::where('user_id', $userId)
            ->where('status', 'active')
            ->select('id', 'title', 'due_date')
            ->withCount(['items', 'activeItems', 'nextActions'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($project) {
                $project->progress_percentage = $project->progress_percentage;
                return $project;
            });

        // Get weekly review status
        $lastWeeklyReview = WeeklyReview::where('user_id', $userId)
            ->orderBy('review_date', 'desc')
            ->first();

        $weeklyReviewStatus = [
            'last_review_date' => $lastWeeklyReview ? $lastWeeklyReview->review_date : null,
            'days_since_last_review' => $lastWeeklyReview 
                ? Carbon::parse($lastWeeklyReview->review_date)->diffInDays(Carbon::now()) 
                : null,
            'is_overdue' => $lastWeeklyReview 
                ? Carbon::parse($lastWeeklyReview->review_date)->diffInDays(Carbon::now()) > 7 
                : true,
        ];

        // Get productivity stats
        $productivityStats = [
            'completed_this_week' => Item::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'completed_this_month' => Item::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->count(),
            'projects_completed_this_month' => Project::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->count(),
        ];

        // Get next actions by energy level
        $nextActionsByEnergy = Item::nextActions()
            ->where('user_id', $userId)
            ->select('energy_level', DB::raw('count(*) as count'))
            ->groupBy('energy_level')
            ->get()
            ->keyBy('energy_level');

        // Get waiting for items that might need follow-up
        $waitingForFollowUp = Item::waitingFor()
            ->where('user_id', $userId)
            ->where('waiting_since', '<=', Carbon::now()->subDays(7))
            ->select('id', 'title', 'waiting_for_person', 'waiting_since')
            ->orderBy('waiting_since', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'counts' => $counts,
                'overdue_items' => $overdueItems,
                'due_today_items' => $dueTodayItems,
                'due_this_week_items' => $dueThisWeekItems,
                'recent_activity' => $recentItems,
                'context_breakdown' => $contextBreakdown,
                'active_projects' => $activeProjects,
                'weekly_review_status' => $weeklyReviewStatus,
                'productivity_stats' => $productivityStats,
                'next_actions_by_energy' => [
                    'low' => $nextActionsByEnergy->get(1)->count ?? 0,
                    'medium' => $nextActionsByEnergy->get(2)->count ?? 0,
                    'high' => $nextActionsByEnergy->get(3)->count ?? 0,
                ],
                'waiting_for_follow_up' => $waitingForFollowUp,
                'generated_at' => Carbon::now()->toISOString(),
            ]
        ]);
    }
}
