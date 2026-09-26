<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;

class ReferralService
{
    /**
     * Mark a referral as rewarded when this user
     * makes their first successful DATA transaction.
     *
     * Call this AFTER a data transaction is confirmed successful.
     */
    public function rewardReferrerForUserIfNeeded(User $user): void
    {
        // Find a referral where this user is the friend (by email) and not yet rewarded
        $referral = Referral::where('friend_email', $user->email)
            ->where('status', 'registered') // only once
            ->first();

        if (! $referral) {
            // No pending referral or already rewarded/paid
            return;
        }

        // At this stage we do NOT touch the wallet.
        // We only mark this referral as "rewarded" so it
        // becomes part of the withdrawable balance.
        $referral->status = 'rewarded';
        $referral->save();
    }
}
