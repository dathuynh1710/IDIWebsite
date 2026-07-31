<?php

namespace App\Livewire\Admin\Recruitment;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ApplicationIndex extends Component
{
    use WithPagination;

    #[Url(except: '')] public string $search = '';
    #[Url(except: '')] public string $status = '';
    #[Url(except: '')] public string $position = '';
    public array $selected = [];
    public ?int $viewingApplicationId = null;
    public string $detailStatus = '';
    public string $internalNote = '';

    public function mount(): void
    {
        Gate::authorize('recruitment.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'position'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function viewApplication(int $id): void
    {
        $application = JobApplication::findOrFail($id);
        if ($application->status === JobApplicationStatus::New) {
            $application->update(['status' => JobApplicationStatus::Reviewing, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        }
        $application->refresh();
        $this->viewingApplicationId = $id;
        $this->detailStatus = $application->status->value;
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
            'detailStatus' => ['required', Rule::enum(JobApplicationStatus::class)],
            'internalNote' => ['nullable', 'string', 'max:10000'],
        ]);
        JobApplication::findOrFail($this->viewingApplicationId)->update([
            'status' => $data['detailStatus'],
            'internal_note' => trim($data['internalNote'] ?? ''),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        $this->dispatch('admin-toast', message: 'Đã cập nhật hồ sơ ứng viên.', type: 'success');
    }

    public function delete(int $id): void
    {
        Gate::authorize('recruitment.delete');
        JobApplication::findOrFail($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [$id, (string) $id]));
        if ($this->viewingApplicationId === $id) {
            $this->closeApplication();
        }
    }

    public function bulkDelete(): void
    {
        Gate::authorize('recruitment.delete');
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'selected.*' => ['integer', 'exists:job_applications,id']]);
        JobApplication::whereKey($this->selected)->delete();
        $this->selected = [];
        $this->dispatch('admin-toast', message: 'Đã xóa các hồ sơ được chọn.', type: 'success');
    }

    public function render()
    {
        $applications = JobApplication::with(['position', 'cv'])
            ->when(trim($this->search), fn ($q) => $q->where(fn ($n) => $n
                ->where('full_name', 'like', '%'.trim($this->search).'%')
                ->orWhere('email', 'like', '%'.trim($this->search).'%')
                ->orWhere('phone', 'like', '%'.trim($this->search).'%')))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->position, fn ($q) => $q->where('job_position_id', $this->position))
            ->latest()->paginate(20);
        return view('livewire.admin.recruitment.application-index', [
            'applications' => $applications,
            'positions' => \App\Models\JobPosition::orderByDesc('sort_order')->get(),
            'viewingApplication' => $this->viewingApplicationId ? JobApplication::with(['position', 'cv'])->find($this->viewingApplicationId) : null,
            'statuses' => [
                'new' => 'Mới', 'reviewing' => 'Đã liên hệ', 'shortlisted' => 'Vào vòng chọn',
                'rejected' => 'Chưa liên hệ', 'hired' => 'Đã tuyển',
            ],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tuyển dụng'], ['label' => 'Hồ sơ ứng viên']],
        ]);
    }
}
