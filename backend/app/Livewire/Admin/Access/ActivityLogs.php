<?php

namespace App\Livewire\Admin\Access;

use App\Livewire\AdminComponent;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
#[Title('Nhật ký hoạt động')]
class ActivityLogs extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'admin', except: '')]
    public string $adminId = '';

    #[Url(except: '')]
    public string $action = '';

    #[Url(except: '')]
    public string $module = '';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    #[Url(as: 'per_page', except: 20)]
    public int $perPage = 20;

    public ?int $viewingId = null;

    public function mount(): void
    {
        Gate::authorize('activity.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'adminId', 'action', 'module', 'dateFrom', 'dateTo', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'adminId', 'action', 'module', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    public function view(int $id): void
    {
        Gate::authorize('activity.view');
        Activity::where('log_name', 'admin')->findOrFail($id);
        $this->viewingId = $id;
    }

    public function closeDetail(): void
    {
        $this->viewingId = null;
    }

    public function render()
    {
        $query = Activity::query()->with('causer')->where('log_name', 'admin')
            ->when(trim($this->search), fn ($q) => $q->where('description', 'like', '%'.trim($this->search).'%'))
            ->when($this->adminId, fn ($q) => $q->where('causer_type', User::class)->where('causer_id', $this->adminId))
            ->when($this->action, fn ($q) => $q->where('event', $this->action))
            ->when($this->module, fn ($q) => $q->where('properties->module', $this->module))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest();

        $base = Activity::query()->where('log_name', 'admin');

        return view('livewire.admin.access.activity-logs', [
            'logs' => $query->paginate(in_array($this->perPage, [10, 20, 50, 100], true) ? $this->perPage : 20),
            'admins' => User::withTrashed()->orderBy('name')->get(['id', 'name', 'username']),
            'actions' => (clone $base)->whereNotNull('event')->distinct()->orderBy('event')->pluck('event'),
            'modules' => (clone $base)->whereNotNull('properties')->get()->pluck('properties')->map(fn ($p) => $p->get('module'))->filter()->unique()->sort()->values(),
            'viewingLog' => $this->viewingId ? Activity::with(['causer', 'subject'])->where('log_name', 'admin')->find($this->viewingId) : null,
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Nhật ký hoạt động']],
        ]);
    }
}
