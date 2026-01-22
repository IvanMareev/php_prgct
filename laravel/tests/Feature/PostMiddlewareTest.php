<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;
    private Post $publishedPost;
    private Post $draftPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
        
        $this->publishedPost = Post::factory()->create([
            'is_published' => true,
            'published_at' => now()->subDay()
        ]);
        
        $this->draftPost = Post::factory()->create([
            'is_published' => false,
            'published_at' => null
        ]);
    }

    /** @test */
    public function admin_middleware_allows_admin_access()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('posts.store'), [
                'title' => 'Test',
                'content' => 'Test content'
            ]);

        $response->assertStatus(201); // Или 422 если не хватает данных
    }

    /** @test */
    public function admin_middleware_blocks_non_admin()
    {
        $response = $this->actingAs($this->regularUser)
            ->postJson(route('posts.store'), [
                'title' => 'Test',
                'content' => 'Test content'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function post_published_middleware_allows_access_to_published_posts()
    {
        $response = $this->getJson(route('posts.show', $this->publishedPost));

        $response->assertStatus(200);
    }

    /** @test */
    public function post_published_middleware_blocks_access_to_drafts_for_guests()
    {
        $response = $this->getJson(route('posts.show', $this->draftPost));

        $response->assertStatus(404); // Или 403
    }

    /** @test */
    public function post_published_middleware_allows_admin_access_to_drafts()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('posts.show', $this->draftPost));

        $response->assertStatus(200);
    }

    /** @test */
    public function auth_sanctum_middleware_requires_authentication()
    {
        $response = $this->deleteJson(route('posts.destroy', $this->publishedPost));

        $response->assertStatus(401);
    }
}