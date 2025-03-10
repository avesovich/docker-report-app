<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a list of users.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasRole('administrator')) {
            abort(403, 'You are not authorized to access this page.');
        }

        $search = $request->query('search');
        $role = $request->query('role');

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%$search%")
                             ->orWhere('email', 'like', "%$search%");
            })
            ->when($role, function ($query, $role) {
                return $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        if (!auth()->user()->hasRole('administrator')) {
            abort(403, 'You are not authorized to create users.');
        }

        return Inertia::render('Users/Create', [
            'roles' => ['administrator', 'editor', 'executive'], // ✅ Pass roles to frontend
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('administrator')) {
            abort(403, 'You are not authorized to create users.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in(['administrator', 'editor', 'executive'])],
        ]);

        // ✅ Prevent unauthorized role assignment
        if (!auth()->user()->hasRole('administrator') && $validatedData['role'] === 'administrator') {
            return back()->withErrors('You are not authorized to assign this role.');
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->assignRole($validatedData['role']);

        return redirect()->route('users.index')->with('successMessage', 'User created successfully!');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        if (!auth()->user()->hasRole('administrator')) {
            abort(403, 'You are not authorized to edit users.');
        }

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => ['administrator', 'editor', 'executive'],
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('administrator')) {
            abort(403, 'You are not authorized to update users.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'role' => ['required', Rule::in(['administrator', 'editor', 'executive'])],
        ]);

        // ✅ Prevent unauthorized role changes
        if (!auth()->user()->hasRole('administrator') && $validatedData['role'] === 'administrator') {
            return back()->withErrors('You are not authorized to assign this role.');
        }

        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'] ? bcrypt($validatedData['password']) : $user->password,
        ]);

        $user->syncRoles([$validatedData['role']]);

        return redirect()->route('users.index')->with('successMessage', 'User updated successfully!');
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors('You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
