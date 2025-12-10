<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Monitorar queries lentas (> 500ms em produção, > 1000ms em desenvolvimento)
        $threshold = config('app.debug') ? 1000 : 500;

        DB::listen(function ($query) use ($threshold) {
            if ($query->time > $threshold) {
                Log::warning('Slow Query Detected', [
                    'sql' => $query->sql,
                    'time' => $query->time . 'ms',
                    'bindings' => $query->bindings,
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
            }
        });

        return $next($request);
    }
}
