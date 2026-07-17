<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warga;

class WargaPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdminRw() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdminRt();
    }

    public function view(User $user, Warga $warga): bool
    {
        return $this->ownsWarga($user, $warga);
    }

    public function create(User $user): bool
    {
        return $user->isAdminRt() && $user->managedRtId() !== null;
    }

    public function update(User $user, Warga $warga): bool
    {
        return $this->ownsWarga($user, $warga);
    }

    public function delete(User $user, Warga $warga): bool
    {
        return $this->ownsWarga($user, $warga);
    }

    private function ownsWarga(User $user, Warga $warga): bool
    {
        return $user->isAdminRt()
            && $user->managedRtId() !== null
            && (int) optional($warga->profile)->rt_id === (int) $user->managedRtId();
    }
}
