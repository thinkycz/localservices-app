<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopCoverImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_uploads_a_normalized_shop_cover_image(): void
    {
        Storage::fake('public');
        $provider = User::factory()->create([
            'is_vendor' => true,
            'email_verified_at' => now(),
        ]);
        $category = Category::create(['name' => 'Wellness', 'slug' => 'wellness']);

        $response = $this->actingAs($provider)->post(route('vendor.shops.store'), [
            'name' => 'Studio Klid',
            'category_id' => $category->id,
            'currency' => 'CZK',
            'description' => 'Příjemné studio v centru města.',
            'address' => 'Vinohradská 12',
            'city' => 'Praha',
            'state' => 'Česko',
            'is_online_only' => false,
            'is_available' => true,
            'image' => UploadedFile::fake()->image('cover.jpg', 1800, 1200),
        ]);

        $response->assertRedirect();
        $shop = $provider->shops()->firstOrFail();
        $this->assertNotNull($shop->image);
        $this->assertStringEndsWith('.webp', $shop->image);
        Storage::disk('public')->assertExists($shop->image);

        $oldPath = $shop->image;
        $this->actingAs($provider)->put(route('vendor.shops.update', $shop), [
            'name' => $shop->name,
            'category_id' => $category->id,
            'currency' => 'CZK',
            'description' => $shop->description,
            'address' => $shop->address,
            'city' => $shop->city,
            'state' => $shop->state,
            'is_online_only' => false,
            'is_available' => true,
            'image' => UploadedFile::fake()->image('replacement.png', 900, 1600),
        ])->assertSessionHasNoErrors();

        $newPath = $shop->fresh()->image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $this->actingAs($provider)->put(route('vendor.shops.update', $shop), [
            'name' => $shop->name,
            'category_id' => $category->id,
            'currency' => 'CZK',
            'description' => $shop->description,
            'address' => $shop->address,
            'city' => $shop->city,
            'state' => $shop->state,
            'is_online_only' => false,
            'is_available' => true,
            'image' => UploadedFile::fake()->create('oversize.jpg', 6000, 'image/jpeg'),
        ])->assertSessionHasErrors('image');

        $this->assertSame($newPath, $shop->fresh()->image);
        Storage::disk('public')->assertExists($newPath);
    }
}
