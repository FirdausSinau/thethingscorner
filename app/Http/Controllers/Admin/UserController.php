<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $admins = User::query()
            ->whereIn('role', [UserRole::Admin->value, UserRole::AdminMaster->value])
            ->orderByDesc('role')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.admins.index', ['admins' => $admins]);
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        $admin = new User($request->safe()->only(['name', 'email', 'password']));
        $admin->forceFill([
            'role' => UserRole::Admin,
            'is_active' => true,
        ])->save();

        return redirect()
            ->route('admin.admins.index')
            ->with('status', 'Admin Biasa berhasil dibuat.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->ensureManageableAdmin($request, $user);

        $user->forceFill(['is_active' => false])->save();

        return redirect()
            ->route('admin.admins.index')
            ->with('status', 'Akun admin berhasil dinonaktifkan.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->ensureManageableAdmin($request, $user);

        $user->forceFill(['is_active' => true])->save();

        return redirect()
            ->route('admin.admins.index')
            ->with('status', 'Akun admin berhasil diaktifkan.');
    }

    /**
     * Only regular admin accounts can be toggled. Admin Master accounts are
     * protected so the store cannot lose its last master access.
     */
    private function ensureManageableAdmin(Request $request, User $user): void
    {
        abort_unless($request->user()->isAdminMaster(), 403);
        abort_unless($user->role === UserRole::Admin, 403);
    }
}
