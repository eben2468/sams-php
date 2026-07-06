<?php

namespace App\Http\Controllers;

use App\Core\Request;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('performer');

        if ($action = $request->input('action')) {
            $query->byAction($action);
        }

        if ($userId = $request->input('user_id')) {
            $query->byUser($userId);
        }

        if ($targetType = $request->input('target_type')) {
            $query->byTargetType($targetType);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('timestamp', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('timestamp', '<=', $endDate);
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(50, (int) $request->input('page', 1));

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'logs'       => $logs->items(),
                'pagination' => [
                    'total'       => $logs->total(),
                    'page'        => $logs->currentPage(),
                    'limit'       => $logs->perPage(),
                    'total_pages' => $logs->lastPage(),
                ],
            ]);
        }

        return view('audit.index', compact('logs'));
    }
}
