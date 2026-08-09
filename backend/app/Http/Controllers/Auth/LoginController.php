<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AdminAudit;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(array_merge($credentials, ['is_active' => true]), $request->boolean('remember'))) {
            throw ValidationException::withMessages(['username' => 'Tên đăng nhập hoặc mật khẩu không chính xác.']);
        }

        $request->session()->regenerate();

        DB::table('users')->where('id', Auth::id())->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'updated_at' => now(),
        ]);
        AdminAudit::log('login', 'Xác thực', 'Đăng nhập hệ thống');

        return redirect()->intended(route('admin.dashboard'))->with(Toast::success('Đăng nhập thành công.'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        AdminAudit::log('logout', 'Xác thực', 'Đăng xuất hệ thống');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(Toast::success('Đăng xuất thành công.'));
    }
}
