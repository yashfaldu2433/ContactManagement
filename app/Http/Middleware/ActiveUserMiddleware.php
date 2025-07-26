<?php

namespace App\Http\Middleware;

use App\Services\JsonResponseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware
{
    protected $jsonResponseService;

    public function __construct(JsonResponseService $jsonResponseService)
    {
        $this->jsonResponseService = $jsonResponseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = auth()->user();

        if ($authUser->status == config('constant.ACTIVE_STATUS')) {
            return $next($request);
        }

        return $this->jsonResponseService->sendResponse(false, null, __('message.PERMISSION_DENIED_MESSAGE'), 403);
    }
}
