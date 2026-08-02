<div>
    <x-admin.page-header title="Quản lý đăng ký tuyển dụng" description="Theo dõi, phân loại và xử lý hồ sơ ứng viên." :breadcrumbs="$breadcrumbs" />
    <section class="card filter-card"><div class="application-filter-grid">
        <div class="form-field filter-search"><label>Từ khóa</label><div><x-ui.icon name="search" size="17" /><input class="input" wire:model.live.debounce.350ms="search" placeholder="Họ tên, số điện thoại, email..."></div></div>
        <div class="form-field"><label>Vị trí ứng tuyển</label><select class="select" wire:model.live="position"><option value="">Tất cả vị trí</option>@foreach($positions as $item)<option value="{{ $item->id }}">{{ $item->getTranslation('title', 'vi', false) }}</option>@endforeach</select></div>
        <x-form.select name="status" label="Trạng thái" :options="['' => 'Tất cả'] + $statuses" wire:model.live="status" />
    </div></section>
    <section class="card application-list-card">
        <div class="category-toolbar"><div class="category-toolbar-actions"><button class="button button-danger" wire:click="bulkDelete" wire:confirm="Xóa các hồ sơ đã chọn?" @disabled(!$selected)>Xóa đã chọn</button></div><span>{{ $applications->total() }} hồ sơ</span></div>
        @if($applications->isEmpty())
            <x-ui.empty-state title="Chưa có hồ sơ ứng viên" description="Hồ sơ gửi từ website sẽ xuất hiện tại đây." icon="users" />
        @else
            <div class="table-responsive"><table class="data-table application-table"><thead><tr><th></th><th>Thông tin đăng ký</th><th>Vị trí</th><th>Nội dung</th><th>Trạng thái</th><th></th></tr></thead><tbody>
                @foreach($applications as $item)<tr wire:key="application-{{ $item->id }}">
                    <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
                    <td><button class="application-person" type="button" wire:click="viewApplication({{ $item->id }})"><strong>{{ $item->full_name }}</strong><span>{{ $item->phone }} · {{ $item->email }}</span><small>{{ $item->address }} · {{ $item->created_at->format('H:i, d/m/Y') }}</small></button></td>
                    <td>{{ $item->position?->getTranslation('title', 'vi', false) ?: 'Ứng tuyển tự do' }}</td>
                    <td><div class="application-content">@if($item->cv)<a href="{{ $item->cv->url }}" target="_blank"><x-ui.icon name="upload" size="14" /> {{ $item->cv->original_name }}</a>@endif<p>{{ \Illuminate\Support\Str::limit(strip_tags($item->cover_letter), 140) ?: 'Không có thư giới thiệu.' }}</p></div></td>
                    <td><x-ui.badge :tone="match($item->status->value) {'new' => 'info', 'reviewing' => 'warning', 'shortlisted', 'hired' => 'success', default => 'neutral'}">{{ $statuses[$item->status->value] }}</x-ui.badge></td>
                    <td><div class="row-actions"><button class="icon-button is-dark" wire:click="viewApplication({{ $item->id }})" title="Xem hồ sơ"><x-ui.icon name="eye" size="18" /></button><button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa hồ sơ này?" title="Xóa"><x-ui.icon name="trash" size="18" /></button></div></td>
                </tr>@endforeach
            </tbody></table></div><x-ui.pagination :paginator="$applications" />
        @endif
    </section>
    @if($viewingApplication)
        <div class="contact-detail-modal" wire:key="application-detail-{{ $viewingApplication->id }}">
            <button class="contact-detail-backdrop" type="button" wire:click="closeApplication" aria-label="Đóng"></button>
            <aside class="contact-detail-panel application-detail-panel" role="dialog" aria-modal="true" aria-label="Chi tiết hồ sơ ứng viên">
                <header class="contact-detail-header"><div><span>Hồ sơ #{{ $viewingApplication->id }}</span><strong>{{ $viewingApplication->position?->getTranslation('title', 'vi', false) ?: 'Ứng tuyển tự do' }}</strong></div><button class="icon-button" wire:click="closeApplication"><x-ui.icon name="x" /></button></header>
                <div class="contact-detail-body">
                    <div class="contact-detail-person"><span class="contact-avatar is-large">{{ mb_strtoupper(mb_substr($viewingApplication->full_name, 0, 1)) }}</span><div><h2>{{ $viewingApplication->full_name }}</h2><a href="mailto:{{ $viewingApplication->email }}">{{ $viewingApplication->email }}</a></div></div>
                    <dl class="contact-meta"><div><dt>Điện thoại</dt><dd><a href="tel:{{ $viewingApplication->phone }}">{{ $viewingApplication->phone }}</a></dd></div><div><dt>Ngày gửi</dt><dd>{{ $viewingApplication->created_at->format('H:i d/m/Y') }}</dd></div><div><dt>Địa chỉ</dt><dd>{{ $viewingApplication->address ?: '—' }}</dd></div><div><dt>CV</dt><dd>@if($viewingApplication->cv)<a href="{{ $viewingApplication->cv->url }}" target="_blank">Tải {{ $viewingApplication->cv->original_name }}</a>@else—@endif</dd></div></dl>
                    <div class="contact-message-box"><span>Thư giới thiệu</span><p>{{ $viewingApplication->cover_letter ?: 'Ứng viên không gửi thư giới thiệu.' }}</p></div>
                    <div class="form-stack"><x-form.select name="detailStatus" label="Trạng thái xử lý" :options="$statuses" wire:model="detailStatus" /><x-form.textarea name="internalNote" label="Ghi chú nội bộ" wire:model="internalNote" rows="6" maxlength="10000" /></div>
                </div>
                <footer class="contact-detail-footer"><button class="button button-danger" wire:click="delete({{ $viewingApplication->id }})" wire:confirm="Xóa hồ sơ này?"><x-ui.icon name="trash" size="16" /> Xóa</button><div><button class="button button-secondary" wire:click="closeApplication">Đóng</button><button class="button button-primary" wire:click="saveReview"><x-ui.icon name="save" size="16" /> Lưu xử lý</button></div></footer>
            </aside>
        </div>
    @endif
</div>
