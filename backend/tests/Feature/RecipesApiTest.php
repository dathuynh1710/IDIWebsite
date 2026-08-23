<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Database\Seeders\BusinessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_recipes_are_published_to_the_frontend_api(): void
    {
        $this->seed();

        $this->getJson('/api/recipes?locale=vi')
            ->assertOk()
            ->assertJsonPath('total', 8)
            ->assertJsonPath('items.0.title', 'Cà ri cá tra với dừa và sả')
            ->assertJsonPath('items.0.isFeatured', true)
            ->assertJsonPath('items.0.image.url', 'https://www.idiseafood.com/vnt_upload/recipes/08_2026/mon_an.png')
            ->assertJsonPath('pageConfig.title', 'Công thức bạn có thể thử');
    }

    public function test_recipe_detail_is_localized_and_contains_two_column_content(): void
    {
        $this->seed();

        $this->getJson('/api/recipes/pangasius-fish-curry-coconut-lemongrass?locale=en')
            ->assertOk()
            ->assertJsonPath('data.title', 'Pangasius fish curry with coconut and lemongrass')
            ->assertJsonPath('data.contentLeftHtml', fn (string $html) => str_contains($html, 'I.D.I Corp BAP-certified pangasius fillet'))
            ->assertJsonPath('data.contentRightHtml', fn (string $html) => str_contains($html, 'Cook the curry sauce with coconut milk and lemongrass until fragrant.'))
            ->assertJsonMissingPath('data.servings')
            ->assertJsonMissingPath('data.ingredients');
    }

    public function test_legacy_placeholder_recipe_is_removed_when_source_recipes_are_seeded(): void
    {
        $this->seed();

        Recipe::create([
            'code' => 'RECIPE_GRILLED_PANGASIUS',
            'title' => ['vi' => 'Cá tra nướng sả'],
            'slug' => ['vi' => 'ca-tra-nuong-sa'],
            'translation_status' => ['vi' => 'published'],
            'locale_published_at' => ['vi' => now()->subDay()->toIso8601String()],
            'is_active' => true,
        ]);

        $this->seed(BusinessSeeder::class);

        $this->assertSoftDeleted('recipes', ['code' => 'RECIPE_GRILLED_PANGASIUS']);
        $this->getJson('/api/recipes?locale=vi')->assertOk()->assertJsonPath('total', 8);
    }
}
