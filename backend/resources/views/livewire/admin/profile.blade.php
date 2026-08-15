<div class="profile-page">
    <x-admin.page-header title="Hồ sơ" />

    <div class="profile-layout">
        <aside class="card profile-summary" aria-label="Tóm tắt tài khoản">
            <div class="profile-avatar" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
                <span></span>
            </div>
            <h2>{{ $name }}</h2>
            <p>{{ '@'.$username }}</p>

            <div class="profile-role-list">
                @forelse($roles as $role)
                    <span>{{ $role }}</span>
                @empty
                    <span>Quản trị viên</span>
                @endforelse
            </div>

            <dl class="profile-account-meta">
                <div>
                    <dt><x-ui.icon name="shield" size="18" /> Trạng thái</dt>
                    <dd>{{ auth()->user()->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</dd>
                </div>
                <div>
                    <dt><x-ui.icon name="calendar" size="18" /> Tham gia</dt>
                    <dd>{{ auth()->user()->created_at?->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt><x-ui.icon name="history" size="18" /> Đăng nhập gần nhất</dt>
                    <dd>{{ auth()->user()->last_login_at?->format('H:i - d/m/Y') ?: 'Chưa có dữ liệu' }}</dd>
                </div>
            </dl>
        </aside>

        <section class="card profile-form-card">
            <header class="profile-card-header">
                <span class="profile-card-icon is-password"><x-ui.icon name="key" size="24" /></span>
                <div>
                    <h2>Đổi mật khẩu</h2>
                    <p>Bảo vệ tài khoản bằng một mật khẩu mạnh và riêng biệt.</p>
                </div>
            </header>

            <form wire:submit="changePassword" class="profile-form"
                x-data="{
                    showCurrent: false,
                    showPassword: false,
                    showConfirmation: false,
                    passwordValue: @entangle('password'),
                    strength() {
                        return [
                            this.passwordValue.length >= 8,
                            /[A-Z]/.test(this.passwordValue),
                            /[0-9]|[^A-Za-z0-9\s]/.test(this.passwordValue)
                        ].filter(Boolean).length;
                    }
                }">
                <div class="profile-username-row">
                    <div class="form-field profile-readonly-field">
                        <label for="profile-username">Tên đăng nhập</label>
                        <div class="profile-readonly-input">
                            <x-ui.icon name="user" size="18" />
                            <input id="profile-username" value="{{ $username }}" readonly aria-readonly="true">
                        </div>
                        <p class="field-help">Tên đăng nhập do quản trị hệ thống thiết lập và không thể tự thay đổi.</p>
                    </div>
                </div>

                <div class="profile-password-layout">
                    <div class="profile-password-fields">
                        <div class="form-field">
                            <label for="current-password">Mật khẩu hiện tại <span>*</span></label>
                            <div class="profile-password-control">
                                <x-ui.icon name="lock" size="18" />
                                <input id="current-password" :type="showCurrent ? 'text' : 'password'" wire:model="current_password" autocomplete="current-password" placeholder="Nhập mật khẩu hiện tại">
                                <button type="button" @click="showCurrent = !showCurrent" :aria-label="showCurrent ? 'Ẩn mật khẩu hiện tại' : 'Hiện mật khẩu hiện tại'">
                                    <x-ui.icon name="eye" size="20" x-show="!showCurrent" />
                                    <x-ui.icon name="eye-off" size="20" x-show="showCurrent" x-cloak />
                                </button>
                            </div>
                            @error('current_password')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field">
                            <label for="new-password">Mật khẩu mới <span>*</span></label>
                            <div class="profile-password-control">
                                <x-ui.icon name="shield" size="18" />
                                <input id="new-password" :type="showPassword ? 'text' : 'password'" wire:model.live="password" autocomplete="new-password" placeholder="Nhập mật khẩu mới">
                                <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Ẩn mật khẩu mới' : 'Hiện mật khẩu mới'">
                                    <x-ui.icon name="eye" size="20" x-show="!showPassword" />
                                    <x-ui.icon name="eye-off" size="20" x-show="showPassword" x-cloak />
                                </button>
                            </div>
                            <div class="profile-strength" aria-live="polite">
                                <div><span :class="{ 'is-active': strength() >= 1 }"></span><span :class="{ 'is-active': strength() >= 2 }"></span><span :class="{ 'is-active': strength() >= 3 }"></span></div>
                                <small x-text="['Trống', 'Yếu', 'Trung bình', 'Mạnh'][strength()]"></small>
                            </div>
                            @error('password')<p class="field-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="form-field">
                            <label for="password-confirmation">Xác nhận mật khẩu mới <span>*</span></label>
                            <div class="profile-password-control">
                                <x-ui.icon name="check" size="18" />
                                <input id="password-confirmation" :type="showConfirmation ? 'text' : 'password'" wire:model="password_confirmation" autocomplete="new-password" placeholder="Nhập lại mật khẩu mới">
                                <button type="button" @click="showConfirmation = !showConfirmation" :aria-label="showConfirmation ? 'Ẩn xác nhận mật khẩu' : 'Hiện xác nhận mật khẩu'">
                                    <x-ui.icon name="eye" size="20" x-show="!showConfirmation" />
                                    <x-ui.icon name="eye-off" size="20" x-show="showConfirmation" x-cloak />
                                </button>
                            </div>
                            @error('password_confirmation')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <aside class="profile-password-rules" aria-label="Quy tắc bảo mật">
                        <h3>Quy tắc bảo mật</h3>
                        <ul>
                            <li :class="{ 'is-valid': passwordValue.length >= 8 }"><x-ui.icon name="check" size="17" /> Tối thiểu 8 ký tự</li>
                            <li :class="{ 'is-valid': /[A-Z]/.test(passwordValue) }"><x-ui.icon name="check" size="17" /> Ít nhất một chữ cái viết hoa</li>
                            <li :class="{ 'is-valid': /[0-9]|[^A-Za-z0-9\s]/.test(passwordValue) }"><x-ui.icon name="check" size="17" /> Có số hoặc ký tự đặc biệt</li>
                        </ul>
                    </aside>
                </div>

                <footer class="profile-form-actions">
                    <button class="button button-primary" type="submit" wire:loading.attr="disabled" wire:target="changePassword">
                        <x-ui.icon name="save" size="18" />
                        <span wire:loading.remove wire:target="changePassword">Lưu thay đổi</span>
                        <span wire:loading wire:target="changePassword">Đang lưu...</span>
                    </button>
                    <button class="button button-secondary" type="button" wire:click="resetPasswordForm">Hủy bỏ</button>
                </footer>
            </form>
        </section>
    </div>
</div>
