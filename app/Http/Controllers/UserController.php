<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index', ['users' => User::orderBy('name')->paginate(20)]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $data = $request->validate(['role' => ['required', 'in:admin,staff']]);

        if ($user->isAdmin() && $data['role'] === 'staff' && User::where('role', 'admin')->count() === 1) {
            throw ValidationException::withMessages([
                'role' => 'The last administrator cannot be demoted.',
            ]);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User role updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->isAdmin() && User::where('role', 'admin')->count() === 1) {
            throw ValidationException::withMessages([
                'user' => 'The last administrator cannot be archived.',
            ]);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User account archived.');
    }
}
