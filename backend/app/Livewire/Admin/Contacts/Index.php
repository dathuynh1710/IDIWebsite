<?php

namespace App\Livewire\Admin\Contacts;

use App\Enums\ContactStatus;
use App\Livewire\AdminComponent;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Quản lý liên lạc')]
class Index extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';



    #[Url(as: 'per_page', except: 5, history: true)]
    public int $perPage = 5;

    public array $selected = [];

    public ?int $viewingMessageId = null;

    // Kept only for compatibility with Livewire snapshots created before the
    // status editor was removed. This value is never displayed or persisted.
    public string $detailStatus = '';

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteName = '';

    public bool $pendingBulkDelete = false;

    public function mount(): void
    {
        Gate::authorize('contacts.manage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        abort_unless($this->status === '' || in_array($this->status, array_column(ContactStatus::cases(), 'value'), true), 404);
        $this->resetPage();
        $this->selected = [];
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
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
        $this->reset('search', 'status', 'dateFrom', 'dateTo');
        $this->selected = [];
        $this->resetPage();
    }

    public function viewMessage(int $messageId): void
    {
        $message = ContactMessage::findOrFail($messageId);

        if ($message->status === ContactStatus::Unread) {
            $message->update([
                'status' => ContactStatus::Read,
                'assigned_to' => auth()->id(),
            ]);
        }

        $this->viewingMessageId = $message->id;
    }

    public function closeMessage(): void
    {
        $this->viewingMessageId = null;
        $this->detailStatus = '';
    }

    public function requestDelete(int $messageId): void
    {
        $message = ContactMessage::findOrFail($messageId);

        $this->pendingDeleteId = $message->id;
        $this->pendingDeleteName = $message->full_name;
        $this->pendingBulkDelete = false;
    }

    public function requestBulkDelete(): void
    {
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct', 'exists:contact_messages,id'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một thư liên hệ.']);

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
        if ($this->pendingBulkDelete) {
            $this->validate([
                'selected' => ['required', 'array', 'min:1'],
                'selected.*' => ['integer', 'distinct', 'exists:contact_messages,id'],
            ], ['selected.required' => 'Vui lòng chọn ít nhất một thư liên hệ.']);

            ContactMessage::whereKey($this->selected)->delete();
            $this->selected = [];
            $this->resetDeleteConfirmation();
            $this->toast('Đã xóa vĩnh viễn các thư liên hệ đã chọn.');

            return;
        }

        if (! $this->pendingDeleteId) {
            $this->toast('Không tìm thấy thư liên hệ cần xóa. Vui lòng thử lại.', 'error');
            $this->cancelDelete();

            return;
        }
        $messageId = $this->pendingDeleteId;

        ContactMessage::findOrFail($messageId)->delete();
        $this->selected = array_values(array_diff($this->selected, [$messageId, (string) $messageId]));

        if ($this->viewingMessageId === $messageId) {
            $this->closeMessage();
        }

        $this->resetDeleteConfirmation();
        $this->toast('Đã xóa vĩnh viễn thư liên hệ.');
    }

    private function resetDeleteConfirmation(): void
    {
        $this->pendingDeleteId = null;
        $this->pendingDeleteName = '';
        $this->pendingBulkDelete = false;
    }

    public function render()
    {
        $filters = [
            'search' => trim($this->search),
            'status' => $this->status,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];

        return view('livewire.admin.contacts.index', [
            'messages' => ContactMessage::query()
                ->with('assignee')
                ->filtered($filters)
                ->latest()
                ->paginate($this->perPage),
            'viewingMessage' => $this->viewingMessageId
                ? ContactMessage::with('assignee')->find($this->viewingMessageId)
                : null,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý liên lạc'],
            ],
        ]);
    }
}
