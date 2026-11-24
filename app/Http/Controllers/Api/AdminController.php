<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameSetting;
use App\Models\Item;
use App\Models\Spin;
use App\Models\User;
use App\Services\SpinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Add an item.
    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $item = Item::create($validated);

        return response()->json([
            'message' => 'Item created successfully.',
            'item' => $item,
        ], 201);
    }

    // Edit an item.
    public function updateItem(Request $request, Item $item)
    {
        if (! $item->is_available) {
            return response()->json([
                'message' => 'Assigned items cannot be edited.',
            ], 409);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Item updated successfully.',
            'item' => $item,
        ]);
    }

    public function updateSpinning(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $settings = GameSetting::findOrFail(1);

        $settings->update([
            'spinning_enabled' => $validated['enabled'],
        ]);

        return response()->json([
            'message' => 'Secret Santa settings updated.',
            'spinning_enabled' => $settings->spinning_enabled,
            'draw_started' => Spin::exists(),
        ]);
    }

    public function spins()
    {
        $spins = Spin::with([
            'user:id,username,nickname',
            'recipient:id,username,nickname',
        ])
            ->orderBy('spun_at')
            ->get();

        return response()->json([
            'spins' => $spins->map(function ($spin) {

                return [
                    'id' => $spin->id,

                    'giver' => [
                        'id' => $spin->user->id,
                        'nickname' => $spin->user->nickname,
                    ],

                    'recipient' => [
                        'id' => $spin->recipient->id,
                        'nickname' => $spin->recipient->nickname,
                    ],

                    'revealed' => $spin->revealed_at !== null,

                    'revealed_at' => $spin->revealed_at,
                ];
            }),
        ]);
    }

    // View all items, including assigned ones.
    public function items()
    {
        return response()->json([
            'items' => Item::with('spin.user:id,username,nickname')
                ->orderBy('id')
                ->get(),
        ]);
    }

    // View user allocation status.
    public function users()
    {
        return response()->json([
            'users' => User::with('spin.recipient:id,username,nickname')
                ->get([
                    'id',
                    'nickname',
                    'username',
                    'is_active',
                ]),
        ]);
    }
}