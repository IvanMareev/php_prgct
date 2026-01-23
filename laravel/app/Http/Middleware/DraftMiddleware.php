<?php

namespace App\Http\Middleware;

use App\Enums\ProductStatus;
use App\Exceptions\Product\ProductNotFoundExeption;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DraftMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws ProductNotFoundExeption
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Product $product*/
        $product = $request->route('product');

        if ($product->status === ProductStatus::DRAFT) {
            throw new ProductNotFoundExeption();
        }
        return $next($request);
    }
}
