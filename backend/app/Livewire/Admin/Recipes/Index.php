<?php

namespace App\Livewire\Admin\Recipes;

use App\Livewire\AdminComponent;
use App\Models\Recipe;
use App\Support\RecipeRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Quản lý Recipes')]
class Index extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $difficulty = '';

    #[Url(except: '')]
    public string $active = '';

    #[Url(except: '')]
    public string $date_from = '';

    #[Url(except: '')]
    public string $date_to = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    #[Url(as: 'per_page', except: 5, history: true)]
    public int $perPage = 5;

    public array $selected = [];

    public array $sortOrders = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('recipes.view');
        abort_unless(in_array($this->locale, ['vi', 'en', 'zh'], true), 404);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'difficulty', 'active', 'date_from', 'date_to', 'locale'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;
        $this->perPage = in_array($perPage, [5, 10, 20, 50, 100], true) ? $perPage : 5;
        $this->selected = [];
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'difficulty', 'active', 'date_from', 'date_to');
        $this->locale = 'vi';
        $this->resetPage();
    }

    public function toggleVisibility(int $recipeId): void
    {
        Gate::authorize('recipes.update');
        $recipe = Recipe::findOrFail($recipeId);
        $recipe->update(['is_active' => ! $recipe->is_active, 'updated_by' => auth()->id()]);
        RecipeRoutes::sync($recipe);
        $this->toast($recipe->is_active ? 'Đã hiển thị công thức.' : 'Đã ẩn công thức.');
    }

    public function duplicate(int $recipeId): void
    {
        Gate::authorize('recipes.create');
        $source = Recipe::with(['ingredients', 'steps'])->findOrFail($recipeId);
        $copy = DB::transaction(function () use ($source): Recipe {
            $copy = $source->replicate();
            $suffix = now()->format('His');
            $copy->code = $source->code ? Str::limit($source->code.'_COPY_'.$suffix, 100, '') : null;
            $copy->title = collect($source->getTranslations('title'))->map(fn ($title) => $title.' (Bản sao)')->all();
            $copy->slug = collect($source->getTranslations('slug'))->map(fn ($slug) => Str::limit($slug.'-copy-'.$suffix, 255, ''))->all();
            $copy->translation_status = ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'];
            $copy->locale_published_at = [];
            $copy->is_active = false;
            $copy->created_by = auth()->id();
            $copy->updated_by = auth()->id();
            $copy->save();
            foreach ($source->ingredients as $ingredient) {
                $copy->ingredients()->create($ingredient->only(['name', 'quantity', 'unit', 'note', 'sort_order']));
            }
            foreach ($source->steps as $step) {
                $copy->steps()->create($step->only(['media_id', 'instruction', 'sort_order']));
            }
            RecipeRoutes::sync($copy);

            return $copy;
        });
        $this->toast("Đã nhân bản công thức #{$copy->id} dưới dạng bản nháp.");
    }

    public function delete(int $recipeId): void
    {
        Gate::authorize('recipes.delete');
        $recipe = Recipe::findOrFail($recipeId);
        $recipe->delete();
        DB::table('localized_routes')->where('routeable_type', Recipe::class)->where('routeable_id', $recipe->id)->delete();
        $this->toast('Đã chuyển công thức vào thùng rác.');
    }

    public function requestDelete(int $recipeId): void
    {
        Gate::authorize('recipes.delete');
        $recipe = Recipe::findOrFail($recipeId);

        $this->pendingDeleteId = $recipe->id;
        $this->pendingDeleteName = $recipe->getTranslation('title', $this->locale, false)
            ?: $recipe->getTranslation('title', 'vi', false)
            ?: '#'.$recipe->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('recipes.delete');
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct', 'exists:recipes,id'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một công thức.']);

        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->resetDeleteConfirmation();
    }

    public function confirmDelete(): void
    {
        Gate::authorize('recipes.delete');

        if ($this->pendingBulkDelete) {
            $this->validate([
                'selected' => ['required', 'array', 'min:1'],
                'selected.*' => ['integer', 'distinct', 'exists:recipes,id'],
            ], ['selected.required' => 'Vui lòng chọn ít nhất một công thức.']);

            DB::transaction(function (): void {
                foreach (Recipe::whereKey($this->selected)->get() as $recipe) {
                    $recipe->delete();
                    DB::table('localized_routes')->where('routeable_type', Recipe::class)->where('routeable_id', $recipe->id)->delete();
                }
            });
            $this->selected = [];
            $this->resetDeleteConfirmation();
            $this->toast('Đã chuyển các công thức được chọn vào thùng rác.');

            return;
        }

        abort_unless($this->pendingDeleteId, 422);
        $recipeId = $this->pendingDeleteId;
        $this->delete($recipeId);
        $this->selected = array_values(array_diff($this->selected, [$recipeId, (string) $recipeId]));
        $this->resetDeleteConfirmation();
    }

    private function resetDeleteConfirmation(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = false;
    }

    public function bulk(string $action): void
    {
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct'],
            'sortOrders.*' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một công thức.']);
        Gate::authorize($action === 'delete' ? 'recipes.delete' : 'recipes.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);

        DB::transaction(function () use ($action): void {
            foreach (Recipe::whereKey($this->selected)->get() as $recipe) {
                if (in_array($action, ['show', 'hide'], true)) {
                    $recipe->update(['is_active' => $action === 'show', 'updated_by' => auth()->id()]);
                    RecipeRoutes::sync($recipe);
                } elseif ($action === 'reorder' && array_key_exists($recipe->id, $this->sortOrders)) {
                    $recipe->update(['sort_order' => (int) $this->sortOrders[$recipe->id], 'updated_by' => auth()->id()]);
                } else {
                    $recipe->delete();
                    DB::table('localized_routes')->where('routeable_type', Recipe::class)->where('routeable_id', $recipe->id)->delete();
                }
            }
        });
        $this->selected = [];
        $this->toast('Đã cập nhật các công thức được chọn.');
    }

    public function render()
    {
        $filters = [
            'search' => trim($this->search), 'difficulty' => $this->difficulty,
            'active' => $this->active, 'date_from' => $this->date_from,
            'date_to' => $this->date_to, 'locale' => $this->locale,
        ];
        $recipes = Recipe::query()->with(['featuredMedia', 'videoMedia'])
            ->filtered($filters)->orderByDesc('sort_order')->latest('updated_at')->paginate($this->perPage);
        foreach ($recipes as $recipe) {
            $this->sortOrders[$recipe->id] ??= $recipe->sort_order;
        }

        return view('livewire.admin.recipes.index', [
            'recipes' => $recipes,
            'difficulties' => ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'],
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý Recipes'],
            ],
        ]);
    }
}
