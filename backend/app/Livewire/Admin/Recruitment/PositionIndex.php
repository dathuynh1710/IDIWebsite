<?php

namespace App\Livewire\Admin\Recruitment;

use App\Models\JobPosition;
use App\Support\JobPositionRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PositionIndex extends Component
{
    use WithPagination;

    #[Url(except: '')] public string $search = '';
    #[Url(except: '')] public string $active = '';
    #[Url(except: '')] public string $date_from = '';
    #[Url(except: '')] public string $date_to = '';
    #[Url(except: 'vi')] public string $locale = 'vi';
    public array $selected = [];
    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('recruitment.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'active', 'date_from', 'date_to', 'locale'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('recruitment.update');
        $position = JobPosition::findOrFail($id);
        $position->update(['is_active' => ! $position->is_active, 'updated_by' => auth()->id()]);
        JobPositionRoutes::sync($position);
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
        $this->dispatch('admin-toast', message: 'Đã nhân bản vị trí tuyển dụng.', type: 'success');
    }

    public function delete(int $id): void
    {
        Gate::authorize('recruitment.delete');
        JobPosition::findOrFail($id)->delete();
        DB::table('localized_routes')->where('routeable_type', JobPosition::class)->where('routeable_id', $id)->delete();
        $this->dispatch('admin-toast', message: 'Đã xóa vị trí tuyển dụng.', type: 'success');
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'recruitment.delete' : 'recruitment.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);
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
    }

    public function render()
    {
        $perPage = (int) (DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'careers')->where('setting_key', 'items_per_page')->value('setting_value') ?: 10);
        $positions = JobPosition::withCount('applications')
            ->filtered(['search' => trim($this->search), 'active' => $this->active, 'date_from' => $this->date_from, 'date_to' => $this->date_to, 'locale' => $this->locale])
            ->orderByDesc('sort_order')->latest('updated_at')->paginate($perPage);
        foreach ($positions as $position) {
            $this->sortOrders[$position->id] ??= $position->sort_order;
        }
        return view('livewire.admin.recruitment.position-index', [
            'positions' => $positions,
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản lý tuyển dụng']],
        ]);
    }
}
