<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{

    private  UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {   
        return Inertia::render('Users/Index', [
            'users' => $this->userService->getUsers($request->string('search')->toString()),
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Form', [
            'user' => null,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->userService->create($request->validated());

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        $user = $user->load([
            'pointTransactions' => fn ($query) => $query->latest()->limit(15)
            ]);

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        if(!$user)
        {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }

        return Inertia::render('Users/Form', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->update($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
