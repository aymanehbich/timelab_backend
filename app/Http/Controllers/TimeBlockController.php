<?php

namespace App\Http\Controllers;

use App\Models\TimeBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TimeBlockController extends Controller
{
    // GET /api/time-blocks
    public function index(Request $request): JsonResponse
    {
        $query = TimeBlock::where('user_id', Auth::id());

        $date = $request->input('date', now()->format('Y-m-d'));
        $query->forDate($date);

        $blocks = $query->orderBy('start_hour')->get();

        return response()->json($blocks->map(fn($b) => $b->toApiResponse()));
    }

    // GET /api/time-blocks/{id}
    public function show(string $id): JsonResponse
    {
        $block = TimeBlock::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($block->toApiResponse());
    }

    // POST /api/time-blocks
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task.title' => 'required|string|max:255',
            'task.duration' => 'required|integer|min:1|max:720',
            'task.color' => 'required|string|max:50',
            'startHour' => 'required|numeric|min:0|max:24',
            'endHour' => 'required|numeric|min:0|max:24|gt:startHour',
            'blockDate' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed','errors'=>$validator->errors()], 422);
        }

        $data = $validator->validated();
        $blockDate = $data['blockDate'] ?? now()->format('Y-m-d');

        $tempBlock = new TimeBlock(['user_id'=>Auth::id()]);
        if ($tempBlock->conflictsWith($data['startHour'], $data['endHour'], $blockDate)) {
            return response()->json(['message'=>'Time conflict detected.'], 409);
        }

        $block = TimeBlock::create([
            'user_id' => Auth::id(),
            'task_title' => $data['task']['title'],
            'task_duration' => $data['task']['duration'],
            'task_color' => $data['task']['color'],
            'start_hour' => $data['startHour'],
            'end_hour' => $data['endHour'],
            'block_date' => $blockDate,
            'completed' => false,
        ]);

        return response()->json($block->toApiResponse(), 201);
    }

    // PATCH /api/time-blocks/{id}
    public function update(Request $request, string $id): JsonResponse
    {
        $block = TimeBlock::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'completed' => 'sometimes|boolean',
            'task.title' => 'sometimes|string|max:255',
            'task.duration' => 'sometimes|integer|min:1|max:720',
            'task.color' => 'sometimes|string|max:50',
            'startHour' => 'sometimes|numeric|min:0|max:24',
            'endHour' => 'sometimes|numeric|min:0|max:24|gt:startHour',
        ]);

        if ($validator->fails()) {
            return response()->json(['message'=>'Validation failed','errors'=>$validator->errors()], 422);
        }

        $data = $validator->validated();

        // Update task fields
        if (isset($data['task'])) {
            $block->task_title = $data['task']['title'] ?? $block->task_title;
            $block->task_duration = $data['task']['duration'] ?? $block->task_duration;
            $block->task_color = $data['task']['color'] ?? $block->task_color;
        }

        // Update time fields with conflict check
        $newStart = $data['startHour'] ?? $block->start_hour;
        $newEnd = $data['endHour'] ?? $block->end_hour;
        if ($block->conflictsWith($newStart, $newEnd, $block->block_date, $block->id)) {
            return response()->json(['message'=>'Time conflict detected.'], 409);
        }
        $block->start_hour = $newStart;
        $block->end_hour = $newEnd;

        if (isset($data['completed'])) {
            $block->completed = $data['completed'];
        }

        $block->save();
        return response()->json($block->toApiResponse());
    }

    // DELETE /api/time-blocks/{id}
    public function destroy(string $id): JsonResponse
    {
        $block = TimeBlock::where('user_id', Auth::id())->findOrFail($id);
        $block->delete();
        return response()->json(['message'=>'Time block deleted'], 200);
    }

    // GET /api/time-blocks-statistics
    public function statistics(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $query = TimeBlock::where('user_id', Auth::id())->forDate($date);

        $total = $query->count();
        $completed = $query->where('completed', true)->count();
        $remaining = $total - $completed;
        $percentage = $total>0 ? round(($completed/$total)*100) : 0;
        $xp = $completed * 50;

        return response()->json([
            'date'=>$date,
            'total'=>$total,
            'completed'=>$completed,
            'remaining'=>$remaining,
            'percentage'=>$percentage,
            'xp'=>$xp,
            'isPerfectDay'=>$total>0 && $completed===$total,
        ]);
    }

    // POST /api/time-blocks/bulk-update
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids'=>'required|array',
            'ids.*'=>'required|integer|exists:time_blocks,id',
            'completed'=>'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message'=>'Validation failed','errors'=>$validator->errors()], 422);
        }

        $data = $validator->validated();
        $updated = TimeBlock::where('user_id', Auth::id())
            ->whereIn('id', $data['ids'])
            ->update(['completed'=>$data['completed']]);

        return response()->json([
            'message'=>"Successfully updated {$updated} time blocks",
            'updated_count'=>$updated
        ]);
    }
}
