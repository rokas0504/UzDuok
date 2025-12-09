<?php

namespace App\Http\Controllers\Points;

use App\Http\Controllers\Controller;
use App\Models\Points\PointTransaction;
use App\Models\Roles\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointTransactionController extends Controller
{
    /**
     * Get point transactions with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('role');

        // Only parents can view point transactions
        if (!$user->isParent()) {
            return response()->json([
                'message' => 'Tik tėvai gali peržiūrėti taškų istoriją',
            ], 403);
        }

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
}
