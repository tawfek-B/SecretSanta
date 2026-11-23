<?php

namespace App\Http\Controllers;

use App\Models\Spin;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(User::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Spin::exists()) {
            return back()->withErrors([
                'username' => 'The participant list is locked because Secret Santa assignments have already been generated.',
            ]);
        }

        $validatedData = $request->validate([
            'nickname' => 'nullable|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'nickname' => $validatedData['nickname'] ?? null,
            'username' => $validatedData['username'],
            'password' => bcrypt($validatedData['password']),
            'is_active' => true,
        ]);

        return redirect()->route('home')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->update([
            'is_active' => false,
        ]);

        return redirect()->route('home')
            ->with('success', 'User deactivated successfully.');
    }
}
