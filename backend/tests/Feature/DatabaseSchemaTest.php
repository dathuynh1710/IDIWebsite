<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_cms_tables_exist(): void
    {
        $tables = [
            'locales',
            'media',
            'modules',
            'localized_routes',
            'redirects',
            'content_revisions',
            'product_categories',
            'products',
            'attributes',
            'product_attributes',
            'posts',
            'pages',
            'recipes',
            'investor_documents',
            'job_positions',
            'contact_messages',
            'sliders',
            'site_settings',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_main_multilingual_json_columns_exist(): void
    {
        $columns = [
            'products' => ['title', 'content', 'translation_status'],
            'product_categories' => ['name'],
            'posts' => ['title'],
            'pages' => ['title'],
            'recipes' => ['title', 'summary', 'content_left', 'content_right'],
        ];

        foreach ($columns as $table => $expectedColumns) {
            $this->assertTrue(
                Schema::hasColumns($table, $expectedColumns),
                "Missing multilingual columns on table: {$table}"
            );
        }

        $this->assertFalse(Schema::hasTable('recipe_ingredients'));
        $this->assertFalse(Schema::hasTable('recipe_steps'));
        $this->assertFalse(Schema::hasColumn('recipes', 'servings'));
        $this->assertFalse(Schema::hasColumn('recipes', 'difficulty'));
    }
}
