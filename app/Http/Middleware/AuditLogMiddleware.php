<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    protected array $methodsToLog = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        $method = strtoupper($request->method());

        if (! $user || ! in_array($method, $this->methodsToLog, true)) {
            return $response;
        }

        $route = $request->route();
        $entityType = $route?->getName() ?? $route?->uri() ?? $request->path();

        $routeParams = $route?->parameters() ?? [];
        $entityId = null;

        if (! empty($routeParams)) {
            $firstParam = reset($routeParams);
            $entityId = is_object($firstParam) ? ($firstParam->id ?? null) : $firstParam;
        }

        $payload = $request->all();
        $payload = Arr::except($payload, ['password', 'password_confirmation', 'token']);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'entity_type' => $entityType,
            'entity_id' => (string) ($entityId ?? ''),
            'action' => $method,
            'changes' => [
                'payload' => $payload,
                'status' => $response->getStatusCode(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $response;
    }
}
