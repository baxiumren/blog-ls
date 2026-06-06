<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q    = $request->get('q');
        $role = $request->get('role');

        $users = User::withCount('articles')
            ->when($q, fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($role, fn ($query) => $query->where('role', $role))
            ->orderByDesc('role')->orderBy('name')
            ->get();

        $stats = [
            'total'   => User::count(),
            'admins'  => User::where('role', 'admin')->count(),
            'editors' => User::where('role', 'editor')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', 'in:admin,editor'],
            'password' => ['required', 'string', 'min:6'],
            'bio'      => ['nullable', 'string', 'max:500'],
        ]);
        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'is_admin' => true,
            'password' => $data['password'],
            'bio'      => $data['bio'] ?? null,
        ]);
        return redirect('/admin/users')->with('ok', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', 'in:admin,editor'],
            'password' => ['nullable', 'string', 'min:6'],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        if ($user->isAdmin() && $data['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', "You can't demote the last admin.");
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->bio = $data['bio'] ?? null;
        $user->save();

        return redirect('/admin/users')->with('ok', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', "You can't delete your own account.");
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', "You can't delete the last admin.");
        }
        $user->delete();
        return redirect('/admin/users')->with('ok', 'User deleted.');
    }
}