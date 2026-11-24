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

        $participants = User::where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereNotIn(
                'id',
                Spin::select('recipient_user_id')
            )
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

        $result = DB::transaction(function () use ($user) {

            // Serialize all draws so recipients cannot be selected twice.
            $settings = GameSetting::where('id', 1)
                ->lockForUpdate()
                ->firstOrFail();

            // If this user already drew, always return the same result.
            $existingSpin = Spin::where('user_id', $user->id)
                ->with('recipient')
                ->first();

            if ($existingSpin) {
                return [
                    'already_spun' => true,
                    'spin' => $existingSpin,
                ];
            }

            if (! $settings->spinning_enabled) {
                abort(403, 'Secret Santa is currently disabled.');
            }

            if (! $user->is_active) {
                abort(403, 'Your account cannot participate.');
            }

            // Participants who have not drawn yet.
            $remainingGivers = User::where('is_active', true)
                ->whereNotIn(
                    'id',
                    Spin::select('user_id')
                )
                ->get(['id']);

            if ($remainingGivers->count() < 2) {
                abort(409, 'There are not enough remaining participants to draw.');
            }

            // Recipients who have not already been selected.
            $availableRecipients = User::where('is_active', true)
                ->whereNotIn(
                    'id',
                    Spin::select('recipient_user_id')
                )
                ->where('id', '!=', $user->id);

            if ($remainingGivers->count() === 2) {
                // Final two participants: force a pair so neither gets themselves.
                $otherGiver = $remainingGivers
                    ->firstWhere('id', '!=', $user->id);

                $recipient = $availableRecipients
                    ->where('id', $otherGiver->id)
                    ->first();
            } else {
                // Normal draw: choose a random available recipient.
                $recipient = $availableRecipients
                    ->inRandomOrder()
                    ->first();
            }

            if (! $recipient) {
                abort(409, 'No valid recipient is available.');
            }

            // Save the draw immediately. It cannot be changed afterward.
            $spin = Spin::create([
                'user_id' => $user->id,
                'recipient_user_id' => $recipient->id,
                'spun_at' => now(),
                'revealed_at' => now(),
            ]);

            return [
                'already_spun' => false,
                'spin' => $spin->load('recipient'),
            ];
        }, 3);

        return response()->json([
            'message' => $result['already_spun']
                ? 'You have already spun.'
                : 'Your Secret Santa has been assigned!',

            'already_spun' => $result['already_spun'],

            'result' => [
                'id' => $result['spin']->recipient->id,
                'nickname' => $result['spin']->recipient->nickname,
            ],

            'spun_at' => $result['spin']->spun_at,
        ]);
    }
}
