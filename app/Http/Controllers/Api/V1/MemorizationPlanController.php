<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MemorizationPlan;
use App\Models\MemorizationPlanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemorizationPlanController extends Controller
{
    public function today(Request $request)
    {
        $today = now()->toDateString();
        $items = MemorizationPlanItem::query()
            ->whereHas('memorizationPlan', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                    ->where('status', 'active');
            })
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function ($q) use ($today) {
                $q->whereNull('target_date')
                    ->orWhereDate('target_date', '<=', $today);
            })
            ->with(['memorizationPlan:id,title', 'surah:id,name_ar,name_en', 'fromAyah:id,ayah_number', 'toAyah:id,ayah_number'])
            ->orderByRaw('CASE WHEN target_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('target_date')
            ->orderBy('day_number')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function index(Request $request)
    {
        $plans = MemorizationPlan::where('user_id', $request->user()->id)
                                ->with(['items.fromAyah.surah', 'items.toAyah.surah'])
                                ->when($request->status, function ($q) use ($request) {
                                    return $q->where('status', $request->status);
                                })
                                ->when($request->plan_type, function ($q) use ($request) {
                                    return $q->where('plan_type', $request->plan_type);
                                })
                                ->orderBy('created_at', 'desc')
                                ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $plans
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'plan_type' => 'required|in:juz,surah,hizb,page,custom',
            'start_date' => 'required|date',
            'target_end_date' => 'nullable|date|after:start_date',
            'daily_target_type' => 'nullable|in:ayahs,pages,juz,hizb',
            'daily_target_value' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.surah_id' => 'required|exists:surahs,id',
            'items.*.from_ayah_id' => 'required|exists:ayahs,id',
            'items.*.to_ayah_id' => 'required|exists:ayahs,id',
            'items.*.day_number' => 'required|integer|min:1',
            'items.*.target_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = MemorizationPlan::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'plan_type' => $request->plan_type,
            'start_date' => $request->start_date,
            'target_end_date' => $request->target_end_date,
            'daily_target_type' => $request->daily_target_type ?? 'ayahs',
            'daily_target_value' => $request->daily_target_value ?? 5,
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            MemorizationPlanItem::create([
                'memorization_plan_id' => $plan->id,
                'surah_id' => $item['surah_id'],
                'from_ayah_id' => $item['from_ayah_id'],
                'to_ayah_id' => $item['to_ayah_id'],
                'day_number' => $item['day_number'],
                'target_date' => $item['target_date'] ?? null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Memorization plan created successfully',
            'data' => $plan->load(['items.fromAyah', 'items.toAyah'])
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $plan = MemorizationPlan::where('user_id', $request->user()->id)
                                ->with(['items.fromAyah.surah', 'items.toAyah.surah'])
                                ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $plan
        ]);
    }

    public function update(Request $request, $id)
    {
        $plan = MemorizationPlan::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,paused,completed,cancelled',
            'target_end_date' => 'nullable|date',
            'daily_target_type' => 'nullable|in:ayahs,pages,juz,hizb',
            'daily_target_value' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $plan->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Plan updated successfully',
            'data' => $plan
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $plan = MemorizationPlan::where('user_id', $request->user()->id)->findOrFail($id);
        $plan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Plan deleted successfully'
        ]);
    }

    public function updateItemStatus(Request $request, $planId, $itemId)
    {
        $plan = MemorizationPlan::where('user_id', $request->user()->id)->findOrFail($planId);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed,skipped',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $item = MemorizationPlanItem::where('memorization_plan_id', $planId)
                                   ->where('id', $itemId)
                                   ->firstOrFail();

        $item->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item status updated successfully',
            'data' => $item
        ]);
    }
}
