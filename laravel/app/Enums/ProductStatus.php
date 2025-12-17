<?php


namespace App\Enums;


enum ProductStatus: string
{
    case PUBLISHED = 'published';

    case DRAFT = 'draft';
}
