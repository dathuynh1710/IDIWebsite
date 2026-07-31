<?php

namespace App\Livewire\Admin\AboutPages;

use App\Models\Page;
use App\Support\AboutPageRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Quản lý giới thiệu')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $template = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    public array $selected = [];

    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('pages.view');
        abort_unless(in_array($this->locale, ['vi', 'en', 'zh'], true), 404);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'template', 'status', 'locale'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'template', 'status');
        $this->locale = 'vi';
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
            $copy->translation_status = ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'];
            $copy->locale_published_at = [];
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

    private function toast(string $message): void
    {
        $this->dispatch('admin-toast', message: $message, type: 'success');
    }

    public function render()
    {
        $query = Page::query()->about()->with(['parent', 'featuredMedia']);
        match ($this->status) {
            'trashed' => $query->onlyTrashed(),
            'active' => $query->where('is_active', true),
            'hidden' => $query->where('is_active', false),
            default => null,
        };
        if ($this->template !== '') {
            $query->where('template', $this->template);
        }
        if ($search = trim($this->search)) {
            $locale = $this->locale;
            $query->where(fn ($builder) => $builder->where('code', 'like', "%{$search}%")
                ->orWhere("title->{$locale}", 'like', "%{$search}%")
                ->orWhere("slug->{$locale}", 'like', "%{$search}%"));
        }

        $pages = $query->orderBy('sort_order')->latest('updated_at')->paginate(15);
        foreach ($pages as $page) {
            $this->sortOrders[$page->id] ??= $page->sort_order;
        }

        return view('livewire.admin.about-pages.index', [
            'pages' => $pages,
            'templates' => Page::ABOUT_TEMPLATES,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý giới thiệu'],
            ],
        ]);
    }
}
