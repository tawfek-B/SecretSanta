<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameSetting;
use App\Models\Spin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function state(Request $request)
    {
        $user = $request->user();

        $settings = GameSetting::findOrFail(1);

        $spin = $user->spin()
            ->with('recipient')
            ->first();

        // All participants except the current user.
        $participants = User::where('is_admin', false)
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->orderBy('id')
            ->get([
                'id',
                'nickname',
            ]);

        return response()->json([

            'spinning_enabled' => $settings->spinning_enabled,

            'has_spun' => $spin !== null && $spin->revealed_at !== null,

            'my_result' => $spin && $spin->revealed_at ? [
                'id' => $spin->recipient->id,
                'nickname' => $spin->recipient->nickname,
            ] : null,

            'participants' => $participants,

        ]);
    }

    public function myResult(Request $request)
    {
        $spin = $request->user()
            ->spin()
            ->with('recipient')
            ->first();

        if (! $spin || ! $spin->revealed_at) {
            return response()->json([
                'message' => 'You have not spun yet.',
            ], 404);
        }

        return response()->json([
            'recipient' => [
                'id' => $spin->recipient->id,
                'nickname' => $spin->recipient->nickname,
            ],

            'spun_at' => $spin->revealed_at,
        ]);
    }

    public function spin(Request $request)
    {
        $user = $request->user();

        $result = DB::transaction(
            function () use ($user) {

                // Lock settings while processing the request.
                $settings = GameSetting::where('id', 1)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Find the user's pre-generated assignment.
                $spin = Spin::where('user_id', $user->id)
                    ->with('recipient')
                    ->lockForUpdate()
                    ->first();

                if (! $spin) {
                    abort(409, 'No Secret Santa assignment exists for your account.');
                }

                // If already revealed, return the same result.
                if ($spin->revealed_at) {
                    return [
                        'already_spun' => true,
                        'spin' => $spin,
                    ];
                }

                // Check whether spinning is enabled.
                if (! $settings->spinning_enabled) {
                    abort(403, 'Secret Santa is currently disabled.');
                }

                // Reveal the permanent assignment.
                $spin->update([
                    'revealed_at' => now(),
                ]);

                return [
                    'already_spun' => false,
                    'spin' => $spin->load('recipient'),
                ];
            },
            3
        );

        return response()->json([
            'message' => $result['already_spun']
                ? 'You have already spun.'
                : 'Your Secret Santa has been assigned!',

            'already_spun' => $result['already_spun'],

            'result' => [
                'id' => $result['spin']->recipient->id,
                'nickname' => $result['spin']->recipient->nickname,
            ],

            'spun_at' => $result['spin']->revealed_at,
        ]);
    }
}
