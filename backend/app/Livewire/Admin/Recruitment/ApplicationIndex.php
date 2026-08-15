<?php

namespace App\Livewire\Admin\Recruitment;

use App\Enums\JobApplicationStatus;
use App\Livewire\AdminComponent;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ApplicationIndex extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $searchInput = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $position = '';

    #[Url(except: 10)]
    public int $perPage = 10;

    public array $selected = [];

    public array $pendingStatuses = [];

    public ?int $viewingApplicationId = null;

    public string $detailStatus = '';

    public string $internalNote = '';

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('recruitment.view');
        $this->searchInput = $this->search;
    }

    public function applySearch(): void
    {
        $this->search = trim($this->searchInput);
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = max(1, min(100, (int) $value));
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

    public function updateSelected(): void
    {
        Gate::authorize('recruitment.update');
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'exists:job_applications,id'],
            'pendingStatuses' => ['array'],
            'pendingStatuses.*' => [Rule::in(['new', 'reviewing'])],
        ]);

        $applications = JobApplication::whereKey($this->selected)->get();
        foreach ($applications as $application) {
            $nextStatus = $this->pendingStatuses[$application->id] ?? $application->status->value;
            $application->update([
                'status' => $nextStatus,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        $this->toast('Đã cập nhật '.count($applications).' hồ sơ ứng viên.');
    }

    public function viewApplication(int $id): void
    {
        $application = JobApplication::findOrFail($id);
        if ($application->status === JobApplicationStatus::New) {
            $application->update(['status' => JobApplicationStatus::Reviewing, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        }
        $application->refresh();
        $this->pendingStatuses[$id] = $application->status === JobApplicationStatus::New ? 'new' : 'reviewing';
        $this->viewingApplicationId = $id;
        $this->detailStatus = $application->status === JobApplicationStatus::New ? 'new' : 'reviewing';
        $this->internalNote = (string) ($application->internal_note ?? '');
    }

    public function closeApplication(): void
    {
        $this->reset('viewingApplicationId', 'detailStatus', 'internalNote');
    }

    public function saveReview(): void
    {
        Gate::authorize('recruitment.update');
        $data = $this->validate([
            'detailStatus' => ['required', Rule::in(['new', 'reviewing'])],
            'internalNote' => ['nullable', 'string', 'max:10000'],
        ]);
        JobApplication::findOrFail($this->viewingApplicationId)->update([
            'status' => $data['detailStatus'],
            'internal_note' => trim($data['internalNote'] ?? ''),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $this->pendingStatuses[$this->viewingApplicationId] = $data['detailStatus'];
        $this->toast('Đã cập nhật hồ sơ ứng viên.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('recruitment.delete');
        JobApplication::findOrFail($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [$id, (string) $id]));
        unset($this->pendingStatuses[$id]);
        if ($this->viewingApplicationId === $id) {
            $this->closeApplication();
        }
        $this->toast('Đã xóa hồ sơ ứng viên.');
    }

    public function bulkDelete(): void
    {
        Gate::authorize('recruitment.delete');
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'selected.*' => ['integer', 'exists:job_applications,id']]);
        JobApplication::whereKey($this->selected)->delete();
        foreach ($this->selected as $id) {
            unset($this->pendingStatuses[$id]);
        }
        $this->selected = [];
        $this->toast('Đã xóa các hồ sơ được chọn.');
    }

    public function requestDelete(int $id): void
    {
        Gate::authorize('recruitment.delete');
        $application = JobApplication::findOrFail($id);
        $this->pendingDeleteId = $application->id;
        $this->pendingDeleteName = $application->full_name ?: '#'.$application->id;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        Gate::authorize('recruitment.delete');
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'selected.*' => ['integer', 'distinct', 'exists:job_applications,id']]);
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
            $this->bulkDelete();
            $this->cancelDelete();

            return;
        }

        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy hồ sơ ứng tuyển cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $this->delete($this->pendingDeleteId);
        $this->cancelDelete();
    }

    private function applicationQuery(): Builder
    {
        return JobApplication::query()
            ->with(['position', 'cv'])
            ->when(trim($this->search), fn ($query) => $query->where(fn ($nested) => $nested
                ->where('full_name', 'like', '%'.trim($this->search).'%')
                ->orWhere('email', 'like', '%'.trim($this->search).'%')
                ->orWhere('phone', 'like', '%'.trim($this->search).'%')
                ->orWhere('address', 'like', '%'.trim($this->search).'%')))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->position, fn ($query) => $query->where('job_position_id', $this->position));
    }

    public function render()
    {
        $applications = $this->applicationQuery()->latest()->paginate($this->perPage);

        foreach ($applications as $application) {
            $this->pendingStatuses[$application->id] ??= $application->status === JobApplicationStatus::New ? 'new' : 'reviewing';
        }

        return view('livewire.admin.recruitment.application-index', [
            'applications' => $applications,
            'perPageOptions' => collect([5, 10, 20, 50, 100, $this->perPage])->unique()->sort()->values()->all(),
            'viewingApplication' => $this->viewingApplicationId ? JobApplication::with(['position', 'cv'])->find($this->viewingApplicationId) : null,
            'statuses' => [
                'new' => 'Chưa liên hệ',
                'reviewing' => 'Đã liên hệ',
            ],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản lý tuyển dụng'], ['label' => 'Quản lý đăng ký']],
        ]);
    }
}
