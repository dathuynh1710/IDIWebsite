<div>
    <x-admin.page-header title="Quản lý liên lạc" description="Tiếp nhận và xử lý thư gửi từ website" :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-secondary" href="{{ route('admin.contacts.settings') }}" wire:navigate><x-ui.icon name="settings" size="18" /> Cấu hình liên lạc</a></x-slot:actions>
    </x-admin.page-header>

    <section class="contact-stat-grid" aria-label="Tổng quan liên hệ">
        <button class="contact-stat {{ $status === '' ? 'is-active' : '' }}" type="button" wire:click="$set('status', '')">
            <span class="contact-stat-icon is-blue"><x-ui.icon name="mail" size="20" /></span>
            <span><strong>{{ number_format($counts['all']) }}</strong><small>Tất cả liên hệ</small></span>
        </button>
        <button class="contact-stat {{ $status === 'new' ? 'is-active' : '' }}" type="button" wire:click="$set('status', 'new')">
            <span class="contact-stat-icon is-orange"><x-ui.icon name="alert" size="20" /></span>
            <span><strong>{{ number_format($counts['new']) }}</strong><small>Chưa xem</small></span>
        </button>
        <button class="contact-stat {{ $status === 'in_progress' ? 'is-active' : '' }}" type="button" wire:click="$set('status', 'in_progress')">
            <span class="contact-stat-icon is-violet"><x-ui.icon name="eye" size="20" /></span>
            <span><strong>{{ number_format($counts['in_progress']) }}</strong><small>Đang xử lý</small></span>
        </button>
        <button class="contact-stat {{ $status === 'resolved' ? 'is-active' : '' }}" type="button" wire:click="$set('status', 'resolved')">
            <span class="contact-stat-icon is-green"><x-ui.icon name="check" size="20" /></span>
            <span><strong>{{ number_format($counts['resolved']) }}</strong><small>Đã hoàn tất</small></span>
        </button>
        <button class="contact-stat {{ $status === 'spam' ? 'is-active' : '' }}" type="button" wire:click="$set('status', 'spam')">
            <span class="contact-stat-icon is-red"><x-ui.icon name="alert" size="20" /></span>
            <span><strong>{{ number_format($counts['spam']) }}</strong><small>Spam</small></span>
        </button>
    </section>

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
                    <option value="new">Chưa xem</option>
                    <option value="in_progress">Đang xử lý</option>
                    <option value="resolved">Đã hoàn tất</option>
                    <option value="spam">Spam</option>
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
            <div>
                <label for="contact-locale">Ngôn ngữ</label>
                <select id="contact-locale" class="select" wire:model.live="locale">
                    <option value="">Tất cả</option>
                    <option value="vi">Tiếng Việt</option>
                    <option value="en">English</option>
                    <option value="zh">中文</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button>
            </div>
        </div>
    </section>

    <section class="card contact-list-card" wire:loading.class="is-loading">
        <div class="contact-toolbar">
            <div class="contact-toolbar-actions">
                <button class="button button-secondary" type="button" wire:click="bulk('unread')"><x-ui.icon name="mail" size="17" /> Chưa xem</button>
                <button class="button button-secondary" type="button" wire:click="bulk('read')"><x-ui.icon name="eye" size="17" /> Đã xem</button>
                <button class="button button-success" type="button" wire:click="bulk('resolved')"><x-ui.icon name="check" size="17" /> Hoàn tất</button>
                <button class="button button-secondary" type="button" wire:click="bulk('spam')"><x-ui.icon name="alert" size="17" /> Spam</button>
                <button class="button button-danger" type="button" wire:click="bulk('delete')" wire:confirm="Xóa vĩnh viễn các thư liên hệ đã chọn?"><x-ui.icon name="trash" size="17" /> Xóa</button>
            </div>
            <span class="category-selection-count">{{ count($selected) ? 'Đã chọn '.count($selected).' thư' : 'Chọn thư để thao tác hàng loạt' }}</span>
        </div>
        @error('selected')<div class="validation-summary" role="alert">{{ $message }}</div>@enderror

        @if($messages->isEmpty())
            <x-ui.empty-state title="Không có thư liên hệ phù hợp" description="Hãy thử thay đổi từ khóa, thời gian hoặc trạng thái lọc." />
        @else
            <div class="table-responsive contact-desktop-list">
                <table class="data-table contact-table">
                    <thead>
                        <tr><th class="selection-column"></th><th>Người gửi</th><th>Nội dung liên hệ</th><th>Ngày gửi</th><th>Trạng thái</th><th class="table-actions-heading">Thao tác</th></tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                            @php
                                $statusLabels = ['new' => 'Chưa xem', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã hoàn tất', 'spam' => 'Spam'];
                                $statusTones = ['new' => 'warning', 'in_progress' => 'info', 'resolved' => 'success', 'spam' => 'neutral'];
                                $value = $message->status->value;
                            @endphp
                            <tr wire:key="contact-{{ $message->id }}" class="{{ $value === 'new' ? 'is-unread' : '' }}">
                                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $message->id }}" aria-label="Chọn thư của {{ $message->full_name }}"></td>
                                <td>
                                    <div class="contact-sender">
                                        <span class="contact-avatar">{{ mb_strtoupper(mb_substr($message->full_name, 0, 1)) }}</span>
                                        <span><strong>{{ $message->full_name }}</strong><small>{{ $message->email }}</small></span>
                                    </div>
                                </td>
                                <td>
                                    <button class="contact-content-button" type="button" wire:click="viewMessage({{ $message->id }})">
                                        <strong>{{ $message->subject ?: 'Không có tiêu đề' }}</strong>
                                        <span>{{ \Illuminate\Support\Str::limit($message->message, 125) }}</span>
                                    </button>
                                </td>
                                <td><time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('H:i, d/m/Y') }}</time></td>
                                <td><x-ui.badge :tone="$statusTones[$value]">{{ $statusLabels[$value] }}</x-ui.badge></td>
                                <td><div class="row-actions">
                                    <button class="icon-button is-success" type="button" wire:click="viewMessage({{ $message->id }})" title="Xem chi tiết" aria-label="Xem thư của {{ $message->full_name }}"><x-ui.icon name="eye" size="18" /></button>
                                    <button class="icon-button is-danger" type="button" wire:click="delete({{ $message->id }})" wire:confirm="Xóa vĩnh viễn thư của {{ $message->full_name }}?" title="Xóa" aria-label="Xóa thư của {{ $message->full_name }}"><x-ui.icon name="trash" size="18" /></button>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="contact-mobile-list">
                @foreach($messages as $message)
                    @php
                        $value = $message->status->value;
                    @endphp
                    <article class="contact-mobile-card {{ $value === 'new' ? 'is-unread' : '' }}" wire:key="contact-mobile-{{ $message->id }}">
                        <label class="contact-mobile-select"><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $message->id }}"> Chọn</label>
                        <button type="button" wire:click="viewMessage({{ $message->id }})">
                            <div class="contact-sender"><span class="contact-avatar">{{ mb_strtoupper(mb_substr($message->full_name, 0, 1)) }}</span><span><strong>{{ $message->full_name }}</strong><small>{{ $message->email }}</small></span></div>
                            <h3>{{ $message->subject ?: 'Không có tiêu đề' }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($message->message, 100) }}</p>
                            <time>{{ $message->created_at->format('H:i, d/m/Y') }}</time>
                        </button>
                    </article>
                @endforeach
            </div>
            {{ $messages->links() }}
        @endif
    </section>

    @if($viewingMessage)
        @php
            $detailStatusLabels = ['new' => 'Chưa xem', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã hoàn tất', 'spam' => 'Spam'];
            $initial = mb_strtoupper(mb_substr($viewingMessage->full_name, 0, 1));
        @endphp
        <div class="contact-detail-modal" x-data x-on:keydown.escape.window="$wire.closeMessage()" role="dialog" aria-modal="true" aria-labelledby="contact-detail-title">
            <button class="contact-detail-backdrop" type="button" wire:click="closeMessage" aria-label="Đóng"></button>
            <aside class="contact-detail-panel">
                <header class="contact-detail-header">
                    <div><span>Chi tiết liên hệ</span><strong>#{{ str_pad($viewingMessage->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                    <button class="icon-button" type="button" wire:click="closeMessage" aria-label="Đóng"><x-ui.icon name="x" /></button>
                </header>
                <div class="contact-detail-body">
                    <div class="contact-detail-person">
                        <span class="contact-avatar is-large">{{ $initial }}</span>
                        <div><h2 id="contact-detail-title">{{ $viewingMessage->full_name }}</h2><a href="mailto:{{ $viewingMessage->email }}">{{ $viewingMessage->email }}</a></div>
                    </div>
                    <dl class="contact-meta">
                        <div><dt>Điện thoại</dt><dd>{{ $viewingMessage->phone ?: 'Không cung cấp' }}</dd></div>
                        <div><dt>Ngôn ngữ</dt><dd>{{ strtoupper($viewingMessage->locale ?: '—') }}</dd></div>
                        <div><dt>Ngày gửi</dt><dd>{{ $viewingMessage->created_at->format('H:i, d/m/Y') }}</dd></div>
                        <div><dt>Phụ trách</dt><dd>{{ $viewingMessage->assignee?->name ?: 'Chưa phân công' }}</dd></div>
                    </dl>
                    <section class="contact-message-box">
                        <span>Tiêu đề</span>
                        <h3>{{ $viewingMessage->subject ?: 'Không có tiêu đề' }}</h3>
                        <p>{!! nl2br(e($viewingMessage->message)) !!}</p>
                    </section>
                    <div class="form-field">
                        <label for="detail-status">Trạng thái xử lý</label>
                        <select id="detail-status" class="select" wire:model="detailStatus">
                            @foreach($detailStatusLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <footer class="contact-detail-footer">
                    <button class="button button-danger" type="button" wire:click="delete({{ $viewingMessage->id }})" wire:confirm="Xóa vĩnh viễn thư liên hệ này?"><x-ui.icon name="trash" size="17" /> Xóa</button>
                    <div>
                        <button class="button button-secondary" type="button" wire:click="updateDetailStatus">Lưu trạng thái</button>
                        <a class="button button-primary" href="mailto:{{ $viewingMessage->email }}?subject={{ rawurlencode('Re: '.($viewingMessage->subject ?: 'Liên hệ IDI Seafood')) }}"><x-ui.icon name="mail" size="17" /> Trả lời email</a>
                    </div>
                </footer>
            </aside>
        </div>
    @endif
</div>
