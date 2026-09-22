<div>
    <x-admin.page-header title="Quản lý liên lạc" description="Tiếp nhận và xử lý thư gửi từ website" :breadcrumbs="$breadcrumbs" />

    <section class="filter-card card">
        <div class="contact-filter-grid">
            <div class="filter-search">
                <label for="contact-search">Tìm kiếm</label>
                <div>
                    <x-ui.icon name="search" size="18" />
                    <input id="contact-search" class="input" wire:model.live.debounce.300ms="search" placeholder="Tên, email, tiêu đề hoặc nội dung">
                </div>
            </div>
            <div>
                <label for="contact-status">Trạng thái</label>
                <select id="contact-status" class="select" wire:model.live="status">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(\App\Enums\ContactStatus::cases() as $contactStatus)<option value="{{ $contactStatus->value }}">{{ $contactStatus->label() }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="contact-locale">Ngôn ngữ</label>
                <select id="contact-locale" class="select" wire:model.live="locale">
                    <option value="">Tất cả ngôn ngữ</option>
                    <option value="vi">Tiếng Việt</option>
                    <option value="en">English</option>
                    <option value="zh">中文</option>
                </select>
            </div>
            <div>
                <label for="contact-date-from">Từ ngày</label>
                <input id="contact-date-from" class="input" type="date" wire:model.live="dateFrom">
            </div>
            <div>
                <label for="contact-date-to">Đến ngày</label>
                <input id="contact-date-to" class="input" type="date" wire:model.live="dateTo">
            </div>

            <div class="filter-actions">
                <button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button>
            </div>
        </div>
    </section>

    <section class="card contact-list-card" wire:loading.class="is-loading">
        <div class="contact-toolbar">
            <div class="contact-toolbar-actions">
                <button class="button button-danger" type="button" wire:click="requestBulkDelete"><x-ui.icon name="trash" size="17" /> Xóa</button>
            </div>
            <span class="category-selection-count">{{ count($selected) ? 'Đã chọn '.count($selected).' thư' : 'Chọn thư để thao tác hàng loạt' }}</span>
        </div>
        @if($messages->isEmpty())
            <x-ui.empty-state title="Không có thư liên hệ phù hợp" description="Hãy thử thay đổi từ khóa, thời gian hoặc trạng thái lọc." />
        @else
            <x-ui.data-table class="contact-desktop-list" label="Danh sách thư liên hệ">
                <table class="data-table contact-table">
                    <thead>
                        <tr><th class="selection-column"><input class="table-checkbox" type="checkbox" wire:key="contact-select-page-{{ $messages->currentPage() }}-{{ $perPage }}-{{ $allPageSelected ? 'all' : ($somePageSelected ? 'some' : 'none') }}" wire:click="togglePageSelection" @checked($allPageSelected) x-data x-init="$el.checked = @js($allPageSelected); $el.indeterminate = @js($somePageSelected)" x-effect="$el.checked = @js($allPageSelected); $el.indeterminate = @js($somePageSelected)" aria-label="Chọn tất cả thư trên trang này" title="Chọn tất cả thư trên trang này"></th><th>Người gửi</th><th>Nội dung liên hệ</th><th>Ngày gửi</th><th>Trạng thái</th><th class="table-actions-heading">Thao tác</th></tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                            @php
                                $value = $message->status->value;
                                $isSelected = in_array((int) $message->id, array_map('intval', $selected), true);
                            @endphp
                            <tr wire:key="contact-{{ $message->id }}" class="{{ $message->status === \App\Enums\ContactStatus::Unread ? 'is-unread' : '' }}">
                                <td><input class="table-checkbox" type="checkbox" wire:key="contact-checkbox-{{ $message->id }}-{{ $isSelected ? 'selected' : 'clear' }}" wire:model.live="selected" value="{{ $message->id }}" @checked($isSelected) aria-label="Chọn thư của {{ $message->full_name }}"></td>
                                <td>
                                    <div class="contact-sender">
                                        <strong>{{ $message->full_name }}</strong><small>{{ $message->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <button class="contact-content-button" type="button" wire:click="viewMessage({{ $message->id }})">
                                        <strong>{{ $message->subject ?: 'Không có tiêu đề' }}</strong>
                                        <span>{{ \Illuminate\Support\Str::limit($message->message, 125) }}</span>
                                    </button>
                                </td>
                                <td><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('H:i, d/m/Y') }}</time></td>
                                <td><x-ui.badge :tone="$message->status->tone()">{{ $message->status->label() }}</x-ui.badge></td>
                                <td><div class="row-actions">
                                    <button class="icon-button is-success" type="button" wire:click="viewMessage({{ $message->id }})" title="Xem chi tiết" aria-label="Xem thư của {{ $message->full_name }}"><x-ui.icon name="eye" size="18" /></button>
                                    <button class="icon-button is-danger" type="button" wire:click="requestDelete({{ $message->id }})" title="Xóa" aria-label="Xóa thư của {{ $message->full_name }}"><x-ui.icon name="trash" size="18" /></button>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.data-table>

            <div class="contact-mobile-list">
                @foreach($messages as $message)
                    @php
                        $value = $message->status->value;
                    @endphp
                    <article class="contact-mobile-card {{ $message->status === \App\Enums\ContactStatus::Unread ? 'is-unread' : '' }}" wire:key="contact-mobile-{{ $message->id }}">
                        @php $isSelected = in_array((int) $message->id, array_map('intval', $selected), true); @endphp
                        <label class="contact-mobile-select"><input class="table-checkbox" type="checkbox" wire:key="contact-mobile-checkbox-{{ $message->id }}-{{ $isSelected ? 'selected' : 'clear' }}" wire:model.live="selected" value="{{ $message->id }}" @checked($isSelected)> Chọn</label>
                        <button type="button" wire:click="viewMessage({{ $message->id }})">
                            <div class="contact-sender"><strong>{{ $message->full_name }}</strong><small>{{ $message->email }}</small></div>
                            <h3>{{ $message->subject ?: 'Không có tiêu đề' }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($message->message, 100) }}</p>
                            <time>{{ $message->created_at->format('H:i, d/m/Y') }}</time>
                        </button>
                    </article>
                @endforeach
            </div>
            <x-ui.pagination :paginator="$messages" :per-page-options="[5, 10, 20, 50, 100]" />
        @endif
    </section>

    @if($viewingMessage)
        <div class="contact-detail-modal" x-data x-on:keydown.escape.window="if (!$wire.pendingDeleteId && !$wire.pendingBulkDelete) $wire.closeMessage()" role="dialog" aria-modal="true" aria-labelledby="contact-detail-title">
            <button class="contact-detail-backdrop" type="button" wire:click="closeMessage" aria-label="Đóng"></button>
            <aside class="contact-detail-panel">
                <header class="contact-detail-header">
                    <div><span>Chi tiết liên hệ</span><strong>#{{ str_pad($viewingMessage->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                    <button class="icon-button" type="button" wire:click="closeMessage" aria-label="Đóng"><x-ui.icon name="x" /></button>
                </header>
                <div class="contact-detail-body">
                    <div class="contact-detail-person is-contact-message">
                        <div><h2 id="contact-detail-title">{{ $viewingMessage->full_name }}</h2><a href="mailto:{{ $viewingMessage->email }}">{{ $viewingMessage->email }}</a></div>
                    </div>
                    <dl class="contact-meta">
                        <div><dt>Loại yêu cầu</dt><dd>{{ $viewingMessage->inquiryTypeLabel() }}</dd></div>
                        <div><dt>Điện thoại</dt><dd>{{ $viewingMessage->phone ?: 'Không cung cấp' }}</dd></div>
                        <div><dt>Địa chỉ</dt><dd>{{ $viewingMessage->address ?: 'Không cung cấp' }}</dd></div>
                        <div><dt>Ngôn ngữ</dt><dd>{{ ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'][$viewingMessage->locale] ?? 'Không xác định' }}</dd></div>
                        <div><dt>Đồng ý bảo mật</dt><dd>{{ $viewingMessage->consented_at ? 'Đã đồng ý' : 'Chưa ghi nhận' }}</dd></div>
                        <div><dt>Ngày gửi</dt><dd>{{ $viewingMessage->created_at->format('H:i, d/m/Y') }}</dd></div>
                    </dl>
                    <section class="contact-message-box">
                        <span>Tiêu đề</span>
                        <h3>{{ $viewingMessage->subject ?: 'Không có tiêu đề' }}</h3>
                        <p>{!! nl2br(e($viewingMessage->message)) !!}</p>
                    </section>
                </div>
                <footer class="contact-detail-footer">
                    <button class="button button-danger" type="button" wire:click="requestDelete({{ $viewingMessage->id }})"><x-ui.icon name="trash" size="17" /> Xóa</button>
                    <a class="button button-primary" href="mailto:{{ $viewingMessage->email }}?subject={{ rawurlencode('Re: '.($viewingMessage->subject ?: 'Liên hệ IDI Seafood')) }}"><x-ui.icon name="mail" size="17" /> Trả lời email</a>
                </footer>
            </aside>
        </div>
    @endif

    @if($pendingDeleteId || $pendingBulkDelete)
        <div
            class="modal-backdrop contact-delete-modal"
            wire:key="contact-delete-confirmation"
            wire:click.self="cancelDelete"
            x-data
            x-init="$nextTick(() => $refs.cancelButton.focus())"
            x-on:keydown.escape.window="$wire.cancelDelete()"
        >
            <section class="modal-card contact-delete-card" role="alertdialog" aria-modal="true" aria-labelledby="contact-delete-title" aria-describedby="contact-delete-description">
                <div class="modal-icon contact-delete-icon"><x-ui.icon name="alert" size="30" /></div>
                <h2 id="contact-delete-title">Xóa thư liên hệ?</h2>
                <p id="contact-delete-description">
                    @if($pendingBulkDelete)
                        Bạn sắp xóa vĩnh viễn <strong>{{ count($selected) }} thư liên hệ</strong> đã chọn.
                    @else
                        Bạn sắp xóa vĩnh viễn thư của <strong>“{{ $pendingDeleteName }}”</strong>.
                    @endif
                </p>
                <p class="contact-delete-warning">Dữ liệu sau khi xóa sẽ không thể khôi phục.</p>
                <div class="modal-actions contact-delete-actions">
                    <button class="button button-secondary" type="button" wire:click="cancelDelete" x-ref="cancelButton">Không, giữ lại</button>
                    <button class="button button-danger" type="button" wire:click="confirmDelete" wire:loading.attr="disabled" wire:target="confirmDelete">
                        <x-ui.icon name="trash" size="17" />
                        <span wire:loading.remove wire:target="confirmDelete">Có, xóa thư</span>
                        <span wire:loading wire:target="confirmDelete">Đang xóa...</span>
                    </button>
                </div>
            </section>
        </div>
    @endif
</div>
