<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Enums\PostStatus;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $regularUser;
    protected Category $category;
    protected Post $publishedPost;
    protected Post $draftPost;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем тестовые данные
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'user'
        ]);

        $this->category = Category::factory()->create();

        $this->publishedPost = Post::factory()->create([
            'status' => PostStatus::Published,
            'user_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'views' => 10
        ]);

        $this->draftPost = Post::factory()->create([
            'status' => PostStatus::Draft,
            'user_id' => $this->adminUser->id,
            'category_id' => $this->category->id
        ]);

        // Мокаем хранилище для тестов с файлами
        Storage::fake('public');
    }

    /** @test - Получение списка постов (публичный доступ) */
    public function it_can_get_list_of_published_posts(): void
    {
        // Создаем дополнительные посты для теста
        Post::factory()->count(3)->create(['status' => PostStatus::Published]);
        Post::factory()->count(2)->create(['status' => PostStatus::Draft]);

        $response = $this->getJson(route('posts.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'thumbnail',
                        'views',
                        'created_at'
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data') // 1 publishedPost + 3 новых published
            ->assertJsonMissing(['status' => PostStatus::Draft->value])
            ->assertJsonFragment([
                'id' => $this->publishedPost->id,
                'title' => $this->publishedPost->title,
                'views' => $this->publishedPost->views
            ]);
    }

    /** @test - Получение конкретного опубликованного поста */
    public function it_can_get_single_published_post(): void
    {
        $response = $this->getJson(route('posts.show', $this->publishedPost));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'body',
                    'thumbnail',
                    'status',
                    'views',
                    'created_at',
                    'category' => ['id', 'name'],
                    'user' => ['id', 'name', 'email'],
                    'comments_count'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->publishedPost->id,
                    'title' => $this->publishedPost->title,
                    'status' => PostStatus::Published->value,
                    'views' => 11 // Должен увеличиться на 1
                ]
            ]);
    }

    /** @test - Нельзя получить черновик через публичный API */
    public function it_cannot_get_draft_post_via_public_api(): void
    {
        $response = $this->getJson(route('posts.show', $this->draftPost));

        $response->assertStatus(404);
    }

    /** @test - Админ может видеть черновик */
    public function admin_can_see_draft_post(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson(route('posts.show', $this->draftPost));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->draftPost->id,
                    'status' => PostStatus::Draft->value
                ]
            ]);
    }

    /** @test - Создание поста администратором */
    public function admin_can_create_post(): void
    {
        Sanctum::actingAs($this->adminUser);

        $postData = [
            'title' => 'Новый пост',
            'body' => $this->faker->paragraphs(3, true),
            'category_id' => $this->category->id,
            'status' => PostStatus::Published->value,
            'thumbnail' => UploadedFile::fake()->image('post-thumbnail.jpg')
        ];

        $response = $this->postJson(route('posts.store'), $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'body',
                    'status',
                    'thumbnail',
                    'user_id'
                ]
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Новый пост',
                    'status' => PostStatus::Published->value,
                    'user_id' => $this->adminUser->id
                ]
            ]);

        // Проверяем, что файл был загружен
        Storage::disk('public')->assertExists(
            'thumbnails/' . $postData['thumbnail']->hashName()
        );

        // Проверяем запись в БД
        $this->assertDatabaseHas('posts', [
            'title' => 'Новый пост',
            'user_id' => $this->adminUser->id,
            'status' => PostStatus::Published
        ]);
    }

    /** @test - Обычный пользователь не может создать пост */
    public function regular_user_cannot_create_post(): void
    {
        Sanctum::actingAs($this->regularUser);

        $postData = [
            'title' => 'Пост от пользователя',
            'body' => 'Содержание',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson(route('posts.store'), $postData);

        $response->assertStatus(403);
    }

    /** @test - Неавторизованный пользователь не может создать пост */
    public function guest_cannot_create_post(): void
    {
        $postData = [
            'title' => 'Гостевой пост',
            'body' => 'Содержание',
            'category_id' => $this->category->id
        ];

        $response = $this->postJson(route('posts.store'), $postData);

        $response->assertStatus(401);
    }

    /** @test - Валидация при создании поста */
    public function it_validates_post_creation_data(): void
    {
        Sanctum::actingAs($this->adminUser);

        $invalidData = [
            'title' => '', // Пустой заголовок
            'body' => 'short', // Слишком короткий
            'category_id' => 99999 // Несуществующая категория
        ];

        $response = $this->postJson(route('posts.store'), $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'body', 'category_id']);
    }

    /** @test - Админ может обновить пост */
    public function admin_can_update_post(): void
    {
        Sanctum::actingAs($this->adminUser);

        $updateData = [
            'title' => 'Обновленный заголовок',
            'body' => 'Обновленное содержание',
            'status' => PostStatus::ARCHIVED->value,
            'category_id' => $this->category->id
        ];

        $response = $this->patchJson(
            route('posts.update', $this->publishedPost),
            $updateData
        );

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Обновленный заголовок',
                    'status' => PostStatus::ARCHIVED->value
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $this->publishedPost->id,
            'title' => 'Обновленный заголовок',
            'status' => PostStatus::ARCHIVED
        ]);
    }

    /** @test - Обновление с загрузкой нового изображения */
    public function it_can_update_post_with_new_thumbnail(): void
    {
        Sanctum::actingAs($this->adminUser);

        $newThumbnail = UploadedFile::fake()->image('new-thumbnail.jpg');

        $response = $this->patchJson(
            route('posts.update', $this->publishedPost),
            ['thumbnail' => $newThumbnail]
        );

        $response->assertStatus(200);

        Storage::disk('public')->assertExists(
            'thumbnails/' . $newThumbnail->hashName()
        );
    }

    /** @test - Обычный пользователь не может обновить пост */
    public function regular_user_cannot_update_post(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->patchJson(
            route('posts.update', $this->publishedPost),
            ['title' => 'Новый заголовок']
        );

        $response->assertStatus(403);
    }

    /** @test - Админ может удалить пост */
    public function admin_can_delete_post(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson(route('posts.destroy', $this->publishedPost));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post deleted successfully']);

        $this->assertSoftDeleted('posts', ['id' => $this->publishedPost->id]);
    }

    /** @test - Обычный пользователь не может удалить пост */
    public function regular_user_cannot_delete_post(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->deleteJson(route('posts.destroy', $this->publishedPost));

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $this->publishedPost->id]);
    }

    /** @test - Авторизованный пользователь может оставить комментарий */
    public function authenticated_user_can_add_comment_to_post(): void
    {
        Sanctum::actingAs($this->regularUser);

        $commentText = 'Отличный пост!';
        
        $response = $this->postJson(
            route('posts.comment.store', $this->publishedPost),
            ['text' => $commentText]
        );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'post_id',
                'text',
                'created_at'
            ])
            ->assertJson([
                'user_id' => $this->regularUser->id,
                'post_id' => $this->publishedPost->id,
                'text' => $commentText
            ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->publishedPost->id,
            'user_id' => $this->regularUser->id,
            'text' => $commentText
        ]);
    }

    /** @test - Нельзя оставить комментарий к черновику */
    public function cannot_comment_on_draft_post(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->postJson(
            route('posts.comment.store', $this->draftPost),
            ['text' => 'Комментарий']
        );

        $response->assertStatus(404);
    }

    /** @test - Гость не может оставить комментарий */
    public function guest_cannot_add_comment(): void
    {
        $response = $this->postJson(
            route('posts.comment.store', $this->publishedPost),
            ['text' => 'Анонимный комментарий']
        );

        $response->assertStatus(401);
    }

    /** @test - Валидация комментария */
    public function comment_requires_valid_text(): void
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->postJson(
            route('posts.comment.store', $this->publishedPost),
            ['text' => ''] // Пустой текст
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    /** @test - Поиск постов с фильтрацией */
    public function it_can_filter_posts_by_category(): void
    {
        $anotherCategory = Category::factory()->create();
        $postInAnotherCategory = Post::factory()->create([
            'category_id' => $anotherCategory->id,
            'status' => PostStatus::Published
        ]);

        $response = $this->getJson(route('posts.index', [
            'category_id' => $anotherCategory->id
        ]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $postInAnotherCategory->id])
            ->assertJsonMissing(['id' => $this->publishedPost->id]);
    }

    /** @test - Пагинация постов */
    public function it_can_paginate_posts(): void
    {
        // Создаем 15 постов (всего будет 16 с publishedPost)
        Post::factory()->count(15)->create(['status' => PostStatus::Published]);

        $response = $this->getJson(route('posts.index', ['per_page' => 5]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total'
                ]
            ])
            ->assertJson([
                'meta' => [
                    'per_page' => 5,
                    'total' => 16
                ]
            ]);
    }

    /** @test - Сортировка постов */
    public function it_can_sort_posts_by_views(): void
    {
        Post::factory()->create([
            'status' => PostStatus::Published,
            'views' => 100,
            'created_at' => now()->subDay()
        ]);
        
        Post::factory()->create([
            'status' => PostStatus::Published,
            'views' => 50,
            'created_at' => now()
        ]);

        $response = $this->getJson(route('posts.index', [
            'sort_by' => 'views',
            'sort_order' => 'desc'
        ]));

        $response->assertStatus(200);
        
        $posts = $response->json('data');
        $this->assertGreaterThanOrEqual(
            $posts[1]['views'] ?? 0,
            $posts[0]['views'] ?? 0
        );
    }

    /** @test - Инкремент просмотров при открытии поста */
    public function it_increments_views_count_when_viewing_post(): void
    {
        $initialViews = $this->publishedPost->views;

        $this->getJson(route('posts.show', $this->publishedPost));

        $this->publishedPost->refresh();
        $this->assertEquals($initialViews + 1, $this->publishedPost->views);
    }
}