<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('admin.superadmin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.superadmin.users.form', [
            'user' => new User(['is_active' => true, 'role' => 'admin_bidang']),
            'bidangOptions' => $this->bidangOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin_bidang', 'super_admin', 'pimpinan'])],
            'bidang' => ['nullable', 'string', 'max:60', 'required_if:role,admin_bidang'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        if ($data['role'] !== 'admin_bidang') {
            $data['bidang'] = null;
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Akun admin berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('admin.superadmin.users.form', [
            'user' => $user,
            'bidangOptions' => $this->bidangOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin_bidang', 'super_admin', 'pimpinan'])],
            'bidang' => ['nullable', 'string', 'max:60', 'required_if:role,admin_bidang'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if ($data['role'] !== 'admin_bidang') {
            $data['bidang'] = null;
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Akun admin diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        $user->delete();

        return back()->with('status', 'Akun admin dihapus.');
    }

    /** Daftar kode bidang dari kategori admin_bidang. */
    protected function bidangOptions(): array
    {
        return Category::where('routing_role', 'admin_bidang')
            ->whereNotNull('routing_bidang')
            ->orderBy('name')
            ->pluck('name', 'routing_bidang')
            ->all();
    }
}
