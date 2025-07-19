<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeeklyReview;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeeklyReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reviews = WeeklyReview::select('id', 'review_date', 'notes', 'created_at')
            ->where('user_id', $request->user()->id)
            ->orderBy('review_date', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'review_date' => 'required|date',
            'notes' => 'nullable|string',
            'review_data' => 'required|array',
            'review_data.completed_projects' => 'nullable|array',
            'review_data.active_projects_reviewed' => 'nullable|boolean',
            'review_data.someday_maybe_reviewed' => 'nullable|boolean',
            'review_data.waiting_for_reviewed' => 'nullable|boolean',
            'review_data.calendar_reviewed' => 'nullable|boolean',
            'review_data.next_actions_updated' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if review already exists for this date
        $existingReview = WeeklyReview::where('user_id', $request->user()->id)
            ->where('review_date', $request->review_date)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly review already exists for this date'
            ], 400);
        }

        $review = WeeklyReview::create([
            'review_date' => $request->review_date,
            'notes' => $request->notes,
            'review_data' => $request->review_data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Weekly review created successfully',
            'data' => $review
        ], 201);
    }

    public function show(Request $request, WeeklyReview $weeklyReview): JsonResponse
    {
        // Check ownership
        if ($weeklyReview->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly review not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $weeklyReview
        ]);
    }

    public function update(Request $request, WeeklyReview $weeklyReview): JsonResponse
    {
        // Check ownership
        if ($weeklyReview->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly review not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'review_data' => 'sometimes|required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $weeklyReview->update($request->only(['notes', 'review_data']));

        return response()->json([
            'success' => true,
            'message' => 'Weekly review updated successfully',
            'data' => $weeklyReview
        ]);
    }

    public function destroy(Request $request, WeeklyReview $weeklyReview): JsonResponse
    {
        // Check ownership
        if ($weeklyReview->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Weekly review not found'
            ], 404);
        }

        $weeklyReview->delete();

        return response()->json([
            'success' => true,
            'message' => 'Weekly review deleted successfully'
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        // Get current week's review or generate template
        $currentWeek = Carbon::now()->startOfWeek();
        
        $review = WeeklyReview::where('user_id', $request->user()->id)
            ->where('review_date', $currentWeek->format('Y-m-d'))
            ->first();

        if (!$review) {
            // Generate review template with current data
            $userId = $request->user()->id;
            
            $reviewData = [
                'completed_projects' => [],
                'active_projects_reviewed' => false,
                'someday_maybe_reviewed' => false,
                'waiting_for_reviewed' => false,
                'calendar_reviewed' => false,
                'next_actions_updated' => false,
                'stats' => [
                    'inbox_count' => Item::inbox()->where('user_id', $userId)->count(),
                    'next_actions_count' => Item::nextActions()->where('user_id', $userId)->count(),
                    'waiting_for_count' => Item::waitingFor()->where('user_id', $userId)->count(),
                    'someday_maybe_count' => Item::somedayMaybe()->where('user_id', $userId)->count(),
                    'active_projects_count' => Project::where('user_id', $userId)->where('status', 'active')->count(),
                    'overdue_items_count' => Item::overdue()->where('user_id', $userId)->count(),
                ]
            ];

            $review = [
                'id' => null,
                'review_date' => $currentWeek->format('Y-m-d'),
                'notes' => null,
                'review_data' => $reviewData,
                'user_id' => $userId,
                'created_at' => null,
                'updated_at' => null,
                'is_template' => true
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }
}
