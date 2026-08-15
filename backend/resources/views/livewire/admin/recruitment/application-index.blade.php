<div class="recruitment-applications-page">
    <x-admin.page-header title="Quản lý đăng ký tuyển dụng" :breadcrumbs="$breadcrumbs" class="recruitment-application-heading" />

    <section class="card application-search-card">
        <form wire:submit="applySearch">
            <div class="form-field application-keyword-field">
                <label for="application-keyword">Từ khóa</label>
                <div class="application-search-row">
                    <input id="application-keyword" class="input" wire:model="searchInput" placeholder="Vui lòng nhập từ khóa cần tìm [Họ tên, Số điện thoại, Email,...]">
                    <button class="button button-primary" type="submit"><x-ui.icon name="search" size="16" /> Tìm kiếm</button>
                </div>
            </div>
        </form>
        <p class="application-total">Tổng cộng: <strong>{{ $applications->total() }}</strong></p>
    </section>

    <section class="card application-list-card">
        <div class="application-reference-toolbar">
            <div class="category-toolbar-actions">
                <button class="button button-success" wire:click="updateSelected" @disabled(!$selected)><x-ui.icon name="save" size="15" /> Cập nhật</button>
                <button class="button button-danger" type="button" wire:click="requestBulkDelete" @disabled(!$selected)><x-ui.icon name="trash" size="15" /> Xóa</button>
            </div>
            @if($selected)<span>Đã chọn <strong>{{ count($selected) }}</strong> hồ sơ</span>@endif
        </div>

        @if($applications->isEmpty())
            <x-ui.empty-state title="Chưa có hồ sơ ứng viên" description="Hồ sơ gửi từ website sẽ xuất hiện tại đây." icon="users" />
        @else
            @php
                $pageIds = $applications->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $selectedIds = array_map('intval', $selected);
                $allPageSelected = $pageIds !== [] && count(array_intersect($pageIds, $selectedIds)) === count($pageIds);
            @endphp
            <div class="table-responsive application-table-wrap">
                <table class="data-table application-reference-table">
                    <thead><tr>
                        <th class="selection-column"><input class="table-checkbox" type="checkbox" wire:click="togglePageSelection(@js($pageIds))" @checked($allPageSelected) aria-label="Chọn tất cả hồ sơ trên trang"></th>
                        <th>Thông tin đăng ký</th>
                        <th>Nội dung</th>
                        <th class="table-actions-heading">Thao tác</th>
                    </tr></thead>
                    <tbody>
                        @foreach($applications as $item)
                            <tr wire:key="application-{{ $item->id }}">
                                <td class="selection-column"><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}" aria-label="Chọn hồ sơ của {{ $item->full_name }}"></td>
                                <td>
                                    <div class="application-person">
                                        <button type="button" wire:click="viewApplication({{ $item->id }})"><x-ui.icon name="user" size="15" /><strong>{{ $item->full_name }}</strong></button>
                                        <div class="application-contact-line"><span>☎ {{ $item->phone ?: 'Chưa có số điện thoại' }}</span><span><x-ui.icon name="mail" size="14" /> {{ $item->email }}</span></div>
                                        <div class="application-address"><x-ui.icon name="map-pin" size="14" /> {{ $item->address ?: 'Chưa cung cấp địa chỉ' }}</div>
                                        <div class="application-date">Ngày đăng ký: {{ $item->created_at->format('H:i, d/m/Y') }}</div>
                                        <div class="application-status-row">
                                            <select class="application-status-select" wire:model="pendingStatuses.{{ $item->id }}" aria-label="Trạng thái hồ sơ của {{ $item->full_name }}">
                                                @foreach($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                            </select>
                                            <span class="application-position-label">{{ $item->position?->getTranslation('title', 'vi', false) ?: 'Ứng tuyển tự do' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="application-content">
                                        <span class="application-content-label">File:</span>
                                        @if($item->cv)
                                            <a href="{{ $item->cv->url }}" target="_blank" title="Tải CV {{ $item->cv->original_name }}"><span>{{ $item->cv->original_name }}</span><x-ui.icon name="download" size="15" /></a>
                                        @else
                                            <span class="application-no-file">Chưa có tệp CV đính kèm</span>
                                        @endif
                                        @if($item->cover_letter)
                                            <span class="application-content-label is-message">Nội dung:</span>
                                            <p>{{ $item->cover_letter }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="row-actions"><button class="icon-button is-danger application-delete-button" type="button" wire:click="requestDelete({{ $item->id }})" title="Xóa hồ sơ" aria-label="Xóa hồ sơ của {{ $item->full_name }}"><x-ui.icon name="trash" size="18" /></button></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-ui.pagination :paginator="$applications" :per-page-options="$perPageOptions" />
        @endif
    </section>

    @if($viewingApplication)
        <div class="contact-detail-modal" wire:key="application-detail-{{ $viewingApplication->id }}">
            <button class="contact-detail-backdrop" type="button" wire:click="closeApplication" aria-label="Đóng"></button>
            <aside class="contact-detail-panel application-detail-panel" role="dialog" aria-modal="true" aria-label="Chi tiết hồ sơ ứng viên">
                <header class="contact-detail-header"><div><span>Hồ sơ #{{ $viewingApplication->id }}</span><strong>{{ $viewingApplication->position?->getTranslation('title', 'vi', false) ?: 'Ứng tuyển tự do' }}</strong></div><button class="icon-button" wire:click="closeApplication" aria-label="Đóng"><x-ui.icon name="x" /></button></header>
                <div class="contact-detail-body">
                    <div class="contact-detail-person"><span class="contact-avatar is-large">{{ mb_strtoupper(mb_substr($viewingApplication->full_name, 0, 1)) }}</span><div><h2>{{ $viewingApplication->full_name }}</h2><a href="mailto:{{ $viewingApplication->email }}">{{ $viewingApplication->email }}</a></div></div>
                    <dl class="contact-meta"><div><dt>Điện thoại</dt><dd><a href="tel:{{ $viewingApplication->phone }}">{{ $viewingApplication->phone ?: '—' }}</a></dd></div><div><dt>Ngày gửi</dt><dd>{{ $viewingApplication->created_at->format('H:i d/m/Y') }}</dd></div><div><dt>Địa chỉ</dt><dd>{{ $viewingApplication->address ?: '—' }}</dd></div><div><dt>CV</dt><dd>@if($viewingApplication->cv)<a href="{{ $viewingApplication->cv->url }}" target="_blank">Tải {{ $viewingApplication->cv->original_name }}</a>@else — @endif</dd></div></dl>
                    <div class="contact-message-box"><span>Nội dung ứng tuyển</span><p>{{ $viewingApplication->cover_letter ?: 'Ứng viên không gửi nội dung giới thiệu.' }}</p></div>
                    <div class="form-stack"><x-form.select name="detailStatus" label="Trạng thái xử lý" :options="$statuses" wire:model="detailStatus" /><x-form.textarea name="internalNote" label="Ghi chú nội bộ" wire:model="internalNote" rows="6" maxlength="10000" /></div>
                </div>
                <footer class="contact-detail-footer"><button class="button button-danger" type="button" wire:click="requestDelete({{ $viewingApplication->id }})"><x-ui.icon name="trash" size="16" /> Xóa</button><div><button class="button button-secondary" wire:click="closeApplication">Đóng</button><button class="button button-primary" wire:click="saveReview"><x-ui.icon name="save" size="16" /> Lưu xử lý</button></div></footer>
            </aside>
        </div>
    @endif

    @if($pendingDeleteId || $pendingBulkDelete)
        <x-ui.delete-confirmation-modal wire-key="recruitment-application-delete-confirmation" title="Xóa hồ sơ ứng tuyển?" confirm-label="Có, xóa hồ sơ" warning="Hồ sơ sẽ bị xóa khỏi danh sách quản lý.">
            @if($pendingBulkDelete)
                Bạn sắp xóa <strong>{{ count($selected) }} hồ sơ đã chọn</strong>.
            @else
                Bạn sắp xóa hồ sơ của <strong>“{{ $pendingDeleteName }}”</strong>.
            @endif
        </x-ui.delete-confirmation-modal>
    @endif
</div>
