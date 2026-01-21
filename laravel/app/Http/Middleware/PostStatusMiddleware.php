<?php

namespace App\Http\Middleware;

use App\Enums\PostStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $post = $request->route('post');

        if($post->status !== PostStatus::Published) {
            return response()->json([
                'message' => 'Post is not published',
            ], 404);
        }
        return $next($request);
    }
}
