<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function guest_cannot_access_products()
    {
        $this->getJson('/api/products')
            ->assertUnauthorized();
    }

    /** @test */
    public function authenticated_user_can_get_products()
    {
        Sanctum::actingAs($this->user);

        Product::factory()->count(3)->create();

        $this->getJson('/api/products')
            ->assertOk();
    }

    /** @test */
    public function user_can_view_single_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $product->id]);
    }

    /** @test */
    public function non_admin_cannot_create_product()
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/products', [
            'name' => 'Test',
            'price' => 100,
        ])->assertForbidden();
    }

    /** @test */
    public function admin_can_create_product()
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/products', [
            'name' => 'aaaa',
            'price' => 32,
            'count' => 2,
            'status' => 'published',
        ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'aaaa']);
    }

    /** @test */
    public function admin_can_update_product()
    {
        Sanctum::actingAs($this->admin);

        $product = Product::factory()->create();

        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated',
        ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated']);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        Sanctum::actingAs($this->admin);

        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function user_can_add_review()
    {
        Sanctum::actingAs($this->admin);

        $product = Product::factory()->create();

        $this->postJson("/api/products/{$product->id}/reviews", [
            'text' => 'Great!',
            'rating' => 5,
        ])
            ->assertCreated();

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'text' => 'Great!',
        ]);
    }

    /** @test */
    public function review_requires_authentication()
    {
        $product = Product::factory()->create();

        $this->postJson("/api/products/{$product->id}/reviews", [
            'text' => 'Great!',
            'rating' => 5,
        ])
            ->assertUnauthorized();
    }
}
