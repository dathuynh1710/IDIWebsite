<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LOCALES = ['vi', 'en', 'zh'];

    public function up(): void
    {
        if (! Schema::hasColumn('recipes', 'content_left')) {
            Schema::table('recipes', function (Blueprint $table): void {
                $table->json('content_left')->nullable()->after('summary');
            });
        }
        if (! Schema::hasColumn('recipes', 'content_right')) {
            Schema::table('recipes', function (Blueprint $table): void {
                $table->json('content_right')->nullable()->after('content_left');
            });
        }

        $this->migrateLegacyContent();

        Schema::dropIfExists('recipe_steps');
        Schema::dropIfExists('recipe_ingredients');

        $legacyColumns = collect([
            'content', 'servings', 'preparation_time', 'cooking_time', 'difficulty',
            'show_ingredients', 'show_steps',
        ])->filter(fn (string $column): bool => Schema::hasColumn('recipes', $column))->all();

        if ($legacyColumns !== []) {
            Schema::table('recipes', fn (Blueprint $table) => $table->dropColumn($legacyColumns));
        }
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->json('content')->nullable()->after('summary');
            $table->string('servings', 100)->nullable();
            $table->unsignedInteger('preparation_time')->nullable();
            $table->unsignedInteger('cooking_time')->nullable();
            $table->string('difficulty', 50)->nullable();
            $table->boolean('show_ingredients')->default(true);
            $table->boolean('show_steps')->default(true);
        });

        DB::table('recipes')->orderBy('id')->each(function (object $recipe): void {
            DB::table('recipes')->where('id', $recipe->id)->update([
                'content' => $recipe->content_right,
                'difficulty' => 'easy',
            ]);
        });

        Schema::create('recipe_ingredients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->json('name');
            $table->string('quantity', 100)->nullable();
            $table->json('unit')->nullable();
            $table->json('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::create('recipe_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('instruction');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::table('recipes', fn (Blueprint $table) => $table->dropColumn(['content_left', 'content_right']));
    }

    private function migrateLegacyContent(): void
    {
        if (! Schema::hasColumn('recipes', 'content')) {
            return;
        }

        DB::table('recipes')->orderBy('id')->each(function (object $recipe): void {
            $left = [];
            $right = $this->decodeTranslations($recipe->content ?? null);

            foreach (self::LOCALES as $locale) {
                $ingredients = Schema::hasTable('recipe_ingredients')
                    ? DB::table('recipe_ingredients')->where('recipe_id', $recipe->id)->orderBy('sort_order')->get()
                    : collect();
                $steps = Schema::hasTable('recipe_steps')
                    ? DB::table('recipe_steps')->where('recipe_id', $recipe->id)->orderBy('sort_order')->get()
                    : collect();

                if ($ingredients->isNotEmpty()) {
                    $items = $ingredients->map(function (object $item) use ($locale): string {
                        $name = $this->localized($item->name, $locale);
                        $unit = $this->localized($item->unit, $locale);
                        $note = $this->localized($item->note, $locale);
                        $amount = trim(($item->quantity ?? '').' '.$unit);
                        $text = trim($amount.' '.$name);
                        if ($note !== '') {
                            $text .= ' ('.$note.')';
                        }

                        return '<li>'.e($text).'</li>';
                    })->implode('');
                    $left[$locale] = '<ul>'.$items.'</ul>';
                }

                if ($steps->isNotEmpty()) {
                    $items = $steps->map(fn (object $item): string => '<li>'.e($this->localized($item->instruction, $locale)).'</li>')->implode('');
                    $right[$locale] = trim(($right[$locale] ?? '').'<ol>'.$items.'</ol>');
                }
            }

            DB::table('recipes')->where('id', $recipe->id)->update([
                'content_left' => $left === [] ? null : json_encode($left, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'content_right' => $right === [] ? null : json_encode($right, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        });
    }

    private function decodeTranslations(?string $value): array
    {
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? array_filter($decoded, fn ($item) => is_string($item) && trim($item) !== '') : [];
    }

    private function localized(?string $value, string $locale): string
    {
        $translations = $this->decodeTranslations($value);

        return trim((string) ($translations[$locale] ?? $translations['vi'] ?? ''));
    }
};
