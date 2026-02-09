<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ProductStatus;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DraftMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws ProductNotFoundException
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Product $product */
        $product = $request->route('product');

        if ($product->status === ProductStatus::DRAFT) {
            throw new ProductNotFoundException();
        }
        return $next($request);
    }
}
