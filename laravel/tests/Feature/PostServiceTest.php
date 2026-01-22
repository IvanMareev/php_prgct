<?php

namespace Tests\Unit;

use App\Http\Requests\Post\PostRequest;
use App\Http\Requests\Post\PostUpdatePostRequect;
use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $postService;
    private User $adminUser;
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->postService = new PostService();
        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->post = Post::factory()->create(['user_id' => $this->adminUser->id]);
        
        Storage::fake('public');
    }

    /** @test */
    public function it_can_store_post()
    {
        $postData = [
            'title' => 'Test Post',
            'content' => 'Test content with more than 50 characters for validation.',
            'is_published' => true,
            'user_id' => $this->adminUser->id
        ];

        $result = $this->postService->store($postData);

        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Test Post', $result['data']->title);
        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);
    }

    /** @test */
    public function it_can_update_post()
    {
        $requestMock = $this->createMock(PostUpdatePostRequect::class);
        $requestMock->expects($this->once())
            ->method('validated')
            ->willReturn([
                'title' => 'Updated Title',
                'content' => 'Updated content'
            ]);

        $result = $this->postService->update($requestMock, $this->post);

        $this->assertEquals('Updated Title', $result->title);
        $this->post->refresh();
        $this->assertEquals('Updated Title', $this->post->title);
    }

    /** @test */
    public function it_handles_thumbnail_upload_during_update()
    {
        Storage::fake('public');
        
        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg');
        
        $requestMock = $this->createMock(PostUpdatePostRequect::class);
        $requestMock->expects($this->once())
            ->method('validated')
            ->willReturn([
                'thumbnail' => $thumbnail
            ]);

        $result = $this->postService->update($requestMock, $this->post);

        Storage::disk('public')->assertExists('thumbnails/' . $thumbnail->hashName());
        $this->assertNotNull($result->thumbnail);
    }

    /** @test */
    public function it_can_update_post_without_changes()
    {
        $originalTitle = $this->post->title;
        
        $requestMock = $this->createMock(PostUpdatePostRequect::class);
        $requestMock->expects($this->once())
            ->method('validated')
            ->willReturn([]); // Пустой массив - без изменений

        $result = $this->postService->update($requestMock, $this->post);

        $this->assertEquals($originalTitle, $result->title);
    }
}