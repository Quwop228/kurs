<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::latest()->paginate(15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,editor,reader',
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Роль пользователя обновлена.');
    }
}
