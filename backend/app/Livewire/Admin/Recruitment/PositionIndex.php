<?php

namespace App\Livewire\Admin\Recruitment;

use App\Livewire\AdminComponent;
use App\Models\JobPosition;
use App\Support\JobPositionRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PositionIndex extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $searchInput = '';

    #[Url(except: 'title')]
    public string $searchBy = 'title';

    #[Url(except: '')]
    public string $active = '';

    #[Url(except: '')]
    public string $date_from = '';

    #[Url(except: '')]
    public string $date_to = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    #[Url(except: 10)]
    public int $perPage = 10;

    public array $selected = [];

    public array $sortOrders = [];

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('recruitment.view');
        $this->searchInput = $this->search;
        if (! request()->has('perPage')) {
            $configuredPerPage = (int) (DB::table('module_settings')
                ->join('modules', 'modules.id', '=', 'module_settings.module_id')
                ->where('modules.code', 'careers')
                ->where('setting_key', 'items_per_page')
                ->value('setting_value') ?: 10);
            $this->perPage = max(1, min(100, $configuredPerPage));
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['active', 'date_from', 'date_to', 'locale', 'searchBy'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function applySearch(): void
    {
        $this->search = trim($this->searchInput);
        $this->resetPage();
        $this->selected = [];
    }

    public function togglePageSelection(array $ids): void
    {
        $ids = array_values(array_map('intval', $ids));
        $selected = array_values(array_map('intval', $this->selected));
        $allSelected = $ids !== [] && count(array_intersect($ids, $selected)) === count($ids);

        $this->selected = $allSelected
            ? array_values(array_diff($selected, $ids))
            : array_values(array_unique(array_merge($selected, $ids)));
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = max(1, min(100, (int) $value));
        $this->resetPage();
        $this->selected = [];
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('recruitment.update');
        $position = JobPosition::findOrFail($id);
        $position->update(['is_active' => ! $position->is_active, 'updated_by' => auth()->id()]);
        JobPositionRoutes::sync($position);
        $this->toastState($position->is_active, 'vị trí tuyển dụng');
    }

    public function duplicate(int $id): void
    {
        Gate::authorize('recruitment.create');
        $source = JobPosition::findOrFail($id);
        $copy = $source->replicate();
        $suffix = now()->format('His');
        $copy->code = $source->code ? "{$source->code}-COPY-{$suffix}" : null;
        $copy->title = collect($source->getTranslations('title'))->map(fn ($v) => "{$v} (Bản sao)")->all();
        $copy->slug = collect($source->getTranslations('slug'))->map(fn ($v) => Str::limit("{$v}-copy-{$suffix}", 255, ''))->all();
        $copy->translation_status = ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'];
        $copy->locale_published_at = [];
        $copy->is_active = false;
        $copy->created_by = auth()->id();
        $copy->updated_by = auth()->id();
        $copy->save();
        JobPositionRoutes::sync($copy);
        $this->toast('Đã nhân bản vị trí tuyển dụng.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('recruitment.delete');
        JobPosition::findOrFail($id)->delete();
        DB::table('localized_routes')->where('routeable_type', JobPosition::class)->where('routeable_id', $id)->delete();
        $this->toast('Đã chuyển vị trí tuyển dụng vào thùng rác.');
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('recruitment.delete');
        $position = JobPosition::findOrFail($id);
        $this->pendingDeleteId = $position->id;
        $this->pendingDeleteName = $position->getTranslation('title', $this->locale, false)
            ?: $position->getTranslation('title', 'vi', false)
            ?: '#'.$position->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('recruitment.delete');
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'selected.*' => ['integer', 'distinct', 'exists:job_positions,id']]);
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->reset('pendingDeleteId', 'pendingDeleteName', 'pendingBulkDelete');
    }

    public function confirmDelete(): void
    {
        Gate::authorize('recruitment.delete');
        if ($this->pendingBulkDelete) {
            $this->bulk('delete');
            $this->cancelDelete();

            return;
        }

        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy vị trí tuyển dụng cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $positionId = $this->pendingDeleteId;
        $this->delete($positionId);
        $this->selected = array_values(array_diff($this->selected, [$positionId, (string) $positionId]));
        $this->cancelDelete();
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'recruitment.delete' : 'recruitment.update');
        if (! in_array($action, ['show', 'hide', 'reorder', 'delete'], true)) {
            $this->toast('Thao tác với vị trí tuyển dụng không hợp lệ.', 'error');

            return;
        }
        foreach (JobPosition::whereKey($this->selected)->get() as $position) {
            if ($action === 'delete') {
                $position->delete();
            } elseif ($action === 'reorder') {
                $position->update(['sort_order' => (int) ($this->sortOrders[$position->id] ?? 0)]);
            } else {
                $position->update(['is_active' => $action === 'show']);
                JobPositionRoutes::sync($position);
            }
        }
        $this->selected = [];
        $this->toastBulk($action, 'vị trí tuyển dụng');
    }

    public function render()
    {
        $positions = JobPosition::withCount('applications')
            ->filtered(['search' => trim($this->search), 'search_by' => $this->searchBy, 'active' => $this->active, 'date_from' => $this->date_from, 'date_to' => $this->date_to, 'locale' => $this->locale])
            ->orderByDesc('sort_order')->latest('updated_at')->paginate($this->perPage);
        foreach ($positions as $position) {
            $this->sortOrders[$position->id] ??= $position->sort_order;
        }

        return view('livewire.admin.recruitment.position-index', [
            'positions' => $positions,
            'perPageOptions' => collect([5, 10, 20, 50, 100, $this->perPage])->unique()->sort()->values()->all(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản lý tuyển dụng']],
        ]);
    }
}
