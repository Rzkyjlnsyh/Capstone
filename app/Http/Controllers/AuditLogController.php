<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load('user');

        return response()->json([
            'id' => $auditLog->id,
            'user' => $auditLog->user ? [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->name,
                'email' => $auditLog->user->email,
            ] : null,
            'entity_type' => $auditLog->entity_type,
            'entity_id' => $auditLog->entity_id,
            'action' => $auditLog->action,
            'changes' => $auditLog->changes,
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'created_at' => $auditLog->created_at,
        ]);
    }
}
