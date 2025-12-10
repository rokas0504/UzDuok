<?php

namespace App\Http\Controllers\Points;

use App\Http\Controllers\Controller;
use App\Http\Requests\Points\DeductPointsRequest;
use App\Models\Points\PointTransaction;
use App\Models\Roles\Role;
use App\Models\Users\User;
use App\Services\Points\PointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointTransactionController extends Controller
{
    public function __construct(
        private readonly PointService $pointService
    ) {}
    /**
     * Get point transactions with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('role');

        // Get all children IDs (users with 'child' role)
        $childRole = Role::where('slug', 'child')->first();
        $childrenIds = User::where('role_id', $childRole->id)->pluck('id');

        // Build query
        $query = PointTransaction::whereIn('user_id', $childrenIds)
            ->with('user:id,name');

        // Filter by child
        if ($request->has('child_id') && $request->child_id) {
            $query->where('user_id', $request->child_id);
        }

        // Filter by reason
        if ($request->has('reason') && $request->reason) {
            $query->where('reason', $request->reason);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Order by newest first
        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get available reasons for filtering.
     */
    public function reasons(): JsonResponse
    {
        return response()->json([
            'reasons' => [
                'task_completed',
                'task_cancelled',
                'shop_purchase',
            ],
        ]);
    }

    /**
     * Manually deduct points from a child.
     * Only parents can deduct points.
     */
    public function deductPoints(DeductPointsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $child = User::findOrFail($validated['child_id']);
        $child->load('role');

        if (!$child->isChild()) {
            return response()->json([
                'message' => 'Nurodytas vartotojas nėra vaikas',
            ], 400);
        }

        try {
            $this->pointService->deductPoints(
                $child,
                $validated['points'],
                $validated['reason']
            );

            return response()->json([
                'message' => 'Taškai sėkmingai numinusuoti',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
