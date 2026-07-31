<?php

namespace App\Livewire\Admin\Contacts;

use App\Enums\ContactStatus;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Quản lý liên lạc')]
class Index extends Component
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

    #[Url(except: '')]
    public string $locale = '';

    public array $selected = [];

    public ?int $viewingMessageId = null;

    public string $detailStatus = '';

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

    public function updatedLocale(): void
    {
        abort_unless($this->locale === '' || in_array($this->locale, ['vi', 'en', 'zh'], true), 404);
        $this->resetPage();
        $this->selected = [];
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'status', 'dateFrom', 'dateTo', 'locale');
        $this->selected = [];
        $this->resetPage();
    }

    public function viewMessage(int $messageId): void
    {
        $message = ContactMessage::findOrFail($messageId);

        if ($message->status === ContactStatus::New) {
            $message->update([
                'status' => ContactStatus::InProgress,
                'assigned_to' => auth()->id(),
            ]);
        }

        $this->viewingMessageId = $message->id;
        $this->detailStatus = $message->fresh()->status->value;
    }

    public function closeMessage(): void
    {
        $this->viewingMessageId = null;
        $this->detailStatus = '';
    }

    public function updateDetailStatus(): void
    {
        $this->validate([
            'detailStatus' => ['required', Rule::enum(ContactStatus::class)],
        ]);

        $message = ContactMessage::findOrFail($this->viewingMessageId);
        $message->update([
            'status' => $this->detailStatus,
            'assigned_to' => $this->detailStatus === ContactStatus::New->value ? null : ($message->assigned_to ?: auth()->id()),
            'replied_at' => $this->detailStatus === ContactStatus::Resolved->value ? ($message->replied_at ?: now()) : $message->replied_at,
        ]);

        $this->toast('Đã cập nhật trạng thái liên hệ.');
    }

    public function delete(int $messageId): void
    {
        ContactMessage::findOrFail($messageId)->delete();
        $this->selected = array_values(array_diff($this->selected, [$messageId, (string) $messageId]));
        $this->closeMessage();
        $this->toast('Đã xóa thư liên hệ.');
    }

    public function bulk(string $action): void
    {
        $this->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'distinct', 'exists:contact_messages,id'],
        ], ['selected.required' => 'Vui lòng chọn ít nhất một thư liên hệ.']);

        abort_unless(in_array($action, ['read', 'unread', 'resolved', 'spam', 'delete'], true), 422);
        $messages = ContactMessage::whereKey($this->selected);

        if ($action === 'delete') {
            $messages->delete();
        } else {
            $status = match ($action) {
                'read' => ContactStatus::InProgress,
                'unread' => ContactStatus::New,
                'resolved' => ContactStatus::Resolved,
                'spam' => ContactStatus::Spam,
            };
            $values = [
                'status' => $status->value,
                'assigned_to' => $status === ContactStatus::New ? null : auth()->id(),
                'updated_at' => now(),
            ];
            if ($status === ContactStatus::Resolved) {
                $values['replied_at'] = now();
            } elseif ($status === ContactStatus::New) {
                $values['replied_at'] = null;
            }
            $messages->update($values);
        }

        $this->selected = [];
        $this->toast(match ($action) {
            'read' => 'Đã đánh dấu các thư là đã xem.',
            'unread' => 'Đã đánh dấu các thư là chưa xem.',
            'resolved' => 'Đã hoàn tất các liên hệ đã chọn.',
            'spam' => 'Đã chuyển các thư vào mục spam.',
            'delete' => 'Đã xóa các thư liên hệ đã chọn.',
        });
    }

    private function toast(string $message): void
    {
        $this->dispatch('admin-toast', message: $message, type: 'success');
    }

    public function render()
    {
        $filters = [
            'search' => trim($this->search),
            'status' => $this->status,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'locale' => $this->locale,
        ];
        $perPage = (int) (DB::table('module_settings')
            ->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'contact')->where('setting_key', 'items_per_page')
            ->value('setting_value') ?: 15);

        return view('livewire.admin.contacts.index', [
            'messages' => ContactMessage::query()
                ->with('assignee')
                ->filtered($filters)
                ->latest()
                ->paginate($perPage),
            'viewingMessage' => $this->viewingMessageId
                ? ContactMessage::with('assignee')->find($this->viewingMessageId)
                : null,
            'counts' => [
                'all' => ContactMessage::count(),
                'new' => ContactMessage::where('status', ContactStatus::New)->count(),
                'in_progress' => ContactMessage::where('status', ContactStatus::InProgress)->count(),
                'resolved' => ContactMessage::where('status', ContactStatus::Resolved)->count(),
                'spam' => ContactMessage::where('status', ContactStatus::Spam)->count(),
            ],
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý liên lạc'],
            ],
        ]);
    }
}
