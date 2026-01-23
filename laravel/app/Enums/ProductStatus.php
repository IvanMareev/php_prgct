<?php
declare(strict_types=1);

namespace App\Enums;


enum ProductStatus: string
{
    case PUBLISHED = 'published';

    case DRAFT = 'draft';
}
