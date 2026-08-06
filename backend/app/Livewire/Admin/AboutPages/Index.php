<?php

namespace App\Livewire\Admin\AboutPages;

use App\Livewire\AdminComponent;
use App\Models\Page;
use App\Support\AboutPageRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Quản lý giới thiệu')]
class Index extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $template = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    #[Url(as: 'per_page', except: 10, history: true)]
    public int $perPage = 10;

    public array $selected = [];

    public array $sortOrders = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('pages.view');
        abort_unless(in_array($this->locale, ['vi', 'en', 'zh'], true), 404);

    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'template', 'locale'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'template');
        $this->locale = 'vi';
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = max(1, min(100, (int) $value));
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleVisibility(int $pageId): void
    {
        Gate::authorize('pages.update');
        $page = Page::query()->about()->findOrFail($pageId);
        $page->update(['is_active' => ! $page->is_active, 'updated_by' => auth()->id()]);
        AboutPageRoutes::sync($page);
        $this->toast($page->is_active ? 'Đã hiển thị nội dung giới thiệu.' : 'Đã ẩn nội dung giới thiệu.');
    }

    public function duplicate(int $pageId): void
    {
        Gate::authorize('pages.create');
        $source = Page::query()->about()->findOrFail($pageId);
        $copy = DB::transaction(function () use ($source): Page {
            $copy = $source->replicate();
            $suffix = now()->format('His');
            $copy->code = $source->code ? Str::limit($source->code.'_COPY_'.$suffix, 100, '') : null;
            $copy->title = collect($source->getTranslations('title'))->map(fn ($title) => $title.' (Bản sao)')->all();
            $copy->slug = collect($source->getTranslations('slug'))->map(fn ($slug) => Str::limit($slug.'-copy-'.$suffix, 255, ''))->all();
            $copy->is_active = false;
            $copy->created_by = auth()->id();
            $copy->updated_by = auth()->id();
            $copy->save();
            AboutPageRoutes::sync($copy);

            return $copy;
        });
        $this->toast("Đã nhân bản nội dung #{$copy->id} dưới dạng bản nháp.");
    }

    public function delete(int $pageId): void
    {
        Gate::authorize('pages.delete');
        $page = Page::query()->about()->findOrFail($pageId);
        Page::where('parent_id', $page->id)->update(['parent_id' => null]);
        $page->delete();
        DB::table('localized_routes')->where('routeable_type', Page::class)->where('routeable_id', $page->id)->delete();
        $this->toast('Đã chuyển nội dung giới thiệu vào thùng rác.');
    }

    public function requestDelete(int $pageId): void
    {
        Gate::authorize('pages.delete');
        $page = Page::query()->about()->findOrFail($pageId);

        $this->pendingDeleteId = $page->id;
        $this->pendingDeleteName = $page->getTranslation('title', $this->locale, false)
            ?: $page->getTranslation('title', 'vi', false)
            ?: '#'.$page->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('pages.delete');
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct', 'exists:pages,id'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một nội dung.']);

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
        Gate::authorize('pages.delete');

        if ($this->pendingBulkDelete) {
            $this->bulk('delete');
            $this->resetDeleteConfirmation();

            return;
        }

        abort_unless($this->pendingDeleteId, 422);
        $pageId = $this->pendingDeleteId;
        $this->delete($pageId);
        $this->selected = array_values(array_diff($this->selected, [$pageId, (string) $pageId]));
        $this->resetDeleteConfirmation();
    }

    private function resetDeleteConfirmation(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = false;
    }

    public function restore(int $pageId): void
    {
        Gate::authorize('pages.delete');
        $page = Page::onlyTrashed()->about()->findOrFail($pageId);
        $page->restore();
        AboutPageRoutes::sync($page);
        $this->toast('Đã khôi phục nội dung giới thiệu.');
    }

    public function bulk(string $action): void
    {
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct'],
            'sortOrders.*' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một nội dung.']);
        Gate::authorize($action === 'delete' ? 'pages.delete' : 'pages.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);

        $pages = Page::query()->about()->whereKey($this->selected)->get();
        DB::transaction(function () use ($action, $pages): void {
            foreach ($pages as $page) {
                if ($action === 'show' || $action === 'hide') {
                    $page->update(['is_active' => $action === 'show', 'updated_by' => auth()->id()]);
                    AboutPageRoutes::sync($page);
                } elseif ($action === 'reorder' && array_key_exists($page->id, $this->sortOrders)) {
                    $page->update(['sort_order' => (int) $this->sortOrders[$page->id], 'updated_by' => auth()->id()]);
                } elseif ($action === 'delete') {
                    Page::where('parent_id', $page->id)->update(['parent_id' => null]);
                    $page->delete();
                    DB::table('localized_routes')->where('routeable_type', Page::class)->where('routeable_id', $page->id)->delete();
                }
            }
        });
        $this->selected = [];
        $this->toast(match ($action) {
            'show' => 'Đã hiển thị các nội dung đã chọn.',
            'hide' => 'Đã ẩn các nội dung đã chọn.',
            'reorder' => 'Đã cập nhật thứ tự hiển thị.',
            'delete' => 'Đã chuyển các nội dung đã chọn vào thùng rác.',
        });
    }

    public function render()
    {
        $query = Page::query()->about()->with(['parent', 'featuredMedia']);
        if ($this->template !== '') {
            $query->where('template', $this->template);
        }
        if ($search = trim($this->search)) {
            $locale = $this->locale;
            $query->where(fn ($builder) => $builder->where('code', 'like', "%{$search}%")
                ->orWhere("title->{$locale}", 'like', "%{$search}%")
                ->orWhere("slug->{$locale}", 'like', "%{$search}%"));
        }

        $pages = $query->orderBy('sort_order')->latest('updated_at')->paginate($this->perPage);
        foreach ($pages as $page) {
            $this->sortOrders[$page->id] ??= $page->sort_order;
        }

        return view('livewire.admin.about-pages.index', [
            'pages' => $pages,
            'templates' => Page::ABOUT_TEMPLATES,
            'perPageOptions' => collect([5, 10, 20, 50, 100, $this->perPage])->unique()->sort()->values()->all(),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý giới thiệu'],
            ],
        ]);
    }
}
