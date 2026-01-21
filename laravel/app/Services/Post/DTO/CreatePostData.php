<?php

namespace App\Services\Post\DTO;

use Spatie\LaravelData\Data;

class CreatePostData extends Data
{
    public string  $category_id;
    public string  $title;
    public string  $body;
    public string  $thumbnail;
    public string  $status;
    public string  $views;
    public string  $user_id;
}
