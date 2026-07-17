<?php

namespace App\Policies;

use App\Models\CctvRecording;
use App\Models\User;

class CctvRecordingPolicy
{
    public function before(User $user): ?bool { return $user->isAdminRw() ? true : null; }
    public function viewAny(User $user): bool { return $user->isAdminRt() && $user->managedRtId() !== null; }
    public function view(User $user, CctvRecording $recording): bool
    {
        return $user->isAdminRt() && (int) $recording->camera->rt_id === $user->managedRtId();
    }
}
