<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Context;
use App\Http\Resources\ContextResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ContextController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contexts = Context::where('user_id', $request->user()->id)
            ->withCount(['activeItems'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ContextResource::collection($contexts)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $context = Context::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'color' => $request->color ?? '#3b82f6',
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Context created successfully',
            'data' => new ContextResource($context)
        ], 201);
    }

    public function show(Request $request, Context $context): JsonResponse
    {
        // Check ownership
        if ($context->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Context not found'
            ], 404);
        }

        // Load with related items count
        $context->loadCount(['activeItems', 'items']);

        return response()->json([
            'success' => true,
            'data' => new ContextResource($context)
        ]);
    }

    public function update(Request $request, Context $context): JsonResponse
    {
        // Check ownership
        if ($context->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Context not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $context->update($request->only(['name', 'icon', 'color']));

        return response()->json([
            'success' => true,
            'message' => 'Context updated successfully',
            'data' => new ContextResource($context)
        ]);
    }

    public function destroy(Request $request, Context $context): JsonResponse
    {
        // Check ownership
        if ($context->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Context not found'
            ], 404);
        }

        // Check if context has items
        $itemsCount = $context->items()->count();
        if ($itemsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete context with existing items'
            ], 400);
        }

        $context->delete();

        return response()->json([
            'success' => true,
            'message' => 'Context deleted successfully'
        ]);
    }
}
