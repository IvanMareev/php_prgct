<?php
declare(strict_types=1);
namespace App\Enums;

enum PostStatus: string
{
    case Published = 'published';
    case Private = 'private';
    case Draft = 'draft';
}
