<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = UserManagementService::manageableUsersQueryFor($request->user());

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(15)->withQueryString();
        $roleOptions = UserManagementService::assignableRoleOptionsFor($request->user());

        return view('users.index', compact('users', 'roleOptions'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $roleOptions = UserManagementService::assignableRoleOptionsFor($request->user());

        return view('users.create', compact('roleOptions'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $user->syncRoles([$request->validated('role')]);

        return redirect()
            ->to(getDashboardUserRoute('index'))
            ->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roleOptions = UserManagementService::assignableRoleOptionsFor(auth()->user());
        $currentRole = $user->getRoleNames()->first();

        return view('users.edit', compact('user', 'roleOptions', 'currentRole'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => $request->validated('password')]);
        }

        $user->syncRoles([$request->validated('role')]);

        return redirect()
            ->to(getDashboardUserRoute('index'))
            ->with('success', 'User account updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Soft-delete so audit FKs remain valid.
        $user->delete();

        return redirect()
            ->to(getDashboardUserRoute('index'))
            ->with('success', 'User account deactivated successfully. Historical records are retained.');
    }
}
