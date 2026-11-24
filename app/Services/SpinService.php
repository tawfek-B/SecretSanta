<?php

namespace App\Services;

use App\Models\Spin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SpinService
{
    public function generateAssignments(): void
    {
        DB::transaction(function () {

            // Get all active non-admin participants.
            $participants = User::where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $count = $participants->count();

            if ($count < 2) {
                throw ValidationException::withMessages([
                    'users' => 'At least two active participants are required.',
                ]);
            }

            // Do not generate assignments twice.
            if (Spin::exists()) {
                throw ValidationException::withMessages([
                    'assignments' => 'Assignments have already been generated.',
                ]);
            }

            // Create a random permutation with no self-assignment.
            do {
                $recipients = $participants->shuffle();

                $hasSelfAssignment = $participants
                    ->values()
                    ->contains(function ($participant, $index) use ($recipients) {
                        return $participant->id === $recipients[$index]->id;
                    });
            } while ($hasSelfAssignment);

            // Save the complete assignment.
            foreach ($participants->values() as $index => $participant) {

                Spin::create([
                    'user_id' => $participant->id,
                    'recipient_user_id' => $recipients[$index]->id,
                    'spun_at' => now(),
                    'revealed_at' => null,
                ]);
            }
        }, 3);
    }
}
