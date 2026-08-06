<?php

namespace App\Livewire\Admin\Investors;

use App\Livewire\AdminComponent;
use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class DocumentIndex extends AdminComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $year = '';

    #[Url(except: '')]
    public string $active = '';

    #[Url(except: '')]
    public string $date_from = '';

    #[Url(except: '')]
    public string $date_to = '';

    #[Url(except: 'vi')]
    public string $locale = 'vi';

    public array $selected = [];

    public array $sortOrders = [];

    public function mount(): void
    {
        Gate::authorize('investors.view');
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'category', 'year', 'active', 'date_from', 'date_to', 'locale'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function toggleVisibility(int $id): void
    {
        Gate::authorize('investors.update');
        $document = InvestorDocument::findOrFail($id);
        $document->update(['is_active' => ! $document->is_active, 'updated_by' => auth()->id()]);
        $this->toastState($document->is_active, 'tài liệu');
    }

    public function delete(int $id): void
    {
        Gate::authorize('investors.delete');
        InvestorDocument::findOrFail($id)->delete();
        $this->toast('Đã chuyển tài liệu vào thùng rác.');
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'sortOrders.*' => ['nullable', 'integer', 'min:0']]);
        Gate::authorize($action === 'delete' ? 'investors.delete' : 'investors.update');
        abort_unless(in_array($action, ['show', 'hide', 'reorder', 'delete'], true), 422);
        foreach (InvestorDocument::whereKey($this->selected)->get() as $document) {
            if ($action === 'delete') {
                $document->delete();
            } elseif ($action === 'reorder') {
                $document->update(['sort_order' => (int) ($this->sortOrders[$document->id] ?? 0), 'updated_by' => auth()->id()]);
            } else {
                $document->update(['is_active' => $action === 'show', 'updated_by' => auth()->id()]);
            }
        }
        $this->selected = [];
        $this->toastBulk($action, 'tài liệu');
    }

    public function render()
    {
        $perPage = (int) (DB::table('module_settings')->join('modules', 'modules.id', '=', 'module_settings.module_id')
            ->where('modules.code', 'investors')->where('setting_key', 'items_per_page')->value('setting_value') ?: 15);
        $documents = InvestorDocument::with(['category', 'files.media'])
            ->filtered([
                'search' => trim($this->search), 'category' => $this->category, 'year' => $this->year,
                'active' => $this->active, 'date_from' => $this->date_from, 'date_to' => $this->date_to,
                'locale' => $this->locale,
            ])
            ->orderByDesc('sort_order')->orderByDesc('published_on')->latest('updated_at')->paginate($perPage);
        foreach ($documents as $document) {
            $this->sortOrders[$document->id] ??= $document->sort_order;
        }

        return view('livewire.admin.investors.document-index', [
            'documents' => $documents,
            'categories' => DocumentCategory::orderByDesc('sort_order')->get(),
            'years' => InvestorDocument::whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year', 'year')->all(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quản lý quan hệ cổ đông']],
        ]);
    }
}
