<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Context;
use App\Http\Resources\ItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $status = $request->query('status', 'active');
        $context_id = $request->query('context_id');
        
        $query = Item::where('user_id', $request->user()->id)
            ->where('status', $status)
            ->with(['project:id,title', 'context:id,name,color']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($context_id) {
            $query->where('context_id', $context_id);
        }

        $items = $query->orderBy('due_date', 'asc')
            ->orderBy('energy_level', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:inbox,next_action,waiting_for,someday_maybe,reference',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
            'energy_level' => 'integer|min:1|max:3',
            'time_estimate' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:contexts,id',
            'waiting_for_person' => 'nullable|string|max:255',
            'waiting_since' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate project ownership
        if ($request->project_id) {
            $projectExists = DB::table('projects')
                ->where('id', $request->project_id)
                ->where('user_id', $request->user()->id)
                ->exists();
            
            if (!$projectExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }
        }

        // Validate context ownership
        if ($request->context_id) {
            $contextExists = DB::table('contexts')
                ->where('id', $request->context_id)
                ->where('user_id', $request->user()->id)
                ->exists();
            
            if (!$contextExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Context not found'
                ], 404);
            }
        }

        $item = Item::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'due_date' => $request->due_date,
            'reminder_date' => $request->reminder_date,
            'energy_level' => $request->energy_level ?? 2,
            'time_estimate' => $request->time_estimate,
            'notes' => $request->notes,
            'project_id' => $request->project_id,
            'context_id' => $request->context_id,
            'waiting_for_person' => $request->waiting_for_person,
            'waiting_since' => $request->waiting_since,
            'user_id' => $request->user()->id,
        ]);

        // Load relations
        $item->load(['project:id,title', 'context:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully',
            'data' => new ItemResource($item)
        ], 201);
    }

    public function show(Request $request, Item $item): JsonResponse
    {
        // Check ownership
        if ($item->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $item->load(['project:id,title', 'context:id,name,color']);

        return response()->json([
            'success' => true,
            'data' => new ItemResource($item)
        ]);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        // Check ownership
        if ($item->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:inbox,next_action,waiting_for,someday_maybe,reference',
            'status' => 'in:active,completed,cancelled',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
            'energy_level' => 'integer|min:1|max:3',
            'time_estimate' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:contexts,id',
            'waiting_for_person' => 'nullable|string|max:255',
            'waiting_since' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($request->only([
            'title', 'description', 'type', 'status', 'due_date', 'reminder_date',
            'energy_level', 'time_estimate', 'notes', 'project_id', 'context_id',
            'waiting_for_person', 'waiting_since'
        ]));

        $item->load(['project:id,title', 'context:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'data' => new ItemResource($item)
        ]);
    }

    public function destroy(Request $request, Item $item): JsonResponse
    {
        // Check ownership
        if ($item->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    }

    // GTD specific methods
    public function inbox(Request $request): JsonResponse
    {
        $items = Item::inbox()
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)
        ]);
    }

    public function nextActions(Request $request): JsonResponse
    {
        $context_id = $request->query('context_id');
        $energy_level = $request->query('energy_level');
        $time_estimate = $request->query('time_estimate');

        $query = Item::nextActions()
            ->where('user_id', $request->user()->id)
            ->with(['project:id,title', 'context:id,name,color']);

        if ($context_id) {
            $query->byContext($context_id);
        }

        if ($energy_level) {
            $query->byEnergyLevel($energy_level);
        }

        if ($time_estimate) {
            $query->byTimeEstimate($time_estimate);
        }

        $items = $query->orderBy('due_date', 'asc')
            ->orderBy('energy_level', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)
        ]);
    }

    public function waitingFor(Request $request): JsonResponse
    {
        $items = Item::waitingFor()
            ->where('user_id', $request->user()->id)
            ->orderBy('waiting_since', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)
        ]);
    }

    public function somedayMaybe(Request $request): JsonResponse
    {
        $items = Item::somedayMaybe()
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)
        ]);
    }

    public function reference(Request $request): JsonResponse
    {
        $items = Item::reference()
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ItemResource::collection($items)
        ]);
    }

    public function complete(Request $request, Item $item): JsonResponse
    {
        // Check ownership
        if ($item->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $item->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Item completed successfully',
            'data' => new ItemResource($item)
        ]);
    }

    public function clarify(Request $request, Item $item): JsonResponse
    {
        // Check ownership
        if ($item->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:next_action,waiting_for,someday_maybe,reference',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:contexts,id',
            'waiting_for_person' => 'nullable|string|max:255',
            'waiting_since' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($request->only([
            'type', 'project_id', 'context_id', 'waiting_for_person', 'waiting_since'
        ]));

        $item->load(['project:id,title', 'context:id,name,color']);

        return response()->json([
            'success' => true,
            'message' => 'Item clarified successfully',
            'data' => new ItemResource($item)
        ]);
    }

    public function byContext(Request $request, Context $context): JsonResponse
    {
        // Check ownership
        if ($context->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Context not found'
            ], 404);
        }

        $items = Item::byContext($context->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with(['project:id,title'])
            ->orderBy('due_date', 'asc')
            ->orderBy('energy_level', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'context' => [
                    'id' => $context->id,
                    'name' => $context->name,
                    'color' => $context->color,
                ],
                'items' => ItemResource::collection($items)
            ]
        ]);
    }
}
