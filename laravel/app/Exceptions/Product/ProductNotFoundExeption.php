<?php
declare(strict_types=1);
namespace App\Exceptions\Product;

use Exception;
use Throwable;

class ProductNotFoundExeption extends Exception
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?? transMessage('product_not_found'), $code, $previous);
    }
}
