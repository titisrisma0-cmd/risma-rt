<?php

namespace App\Policies;

use App\Models\CctvCamera;
use App\Models\User;

class CctvCameraPolicy
{
    public function before(User $user): ?bool { return $user->isAdminRw() ? true : null; }
    public function viewAny(User $user): bool { return $user->isAdminRt() && $user->managedRtId() !== null; }
    public function create(User $user): bool { return $this->viewAny($user); }
    public function view(User $user, CctvCamera $camera): bool { return $this->owns($user, $camera); }
    public function update(User $user, CctvCamera $camera): bool { return $this->owns($user, $camera); }
    public function delete(User $user, CctvCamera $camera): bool { return $this->owns($user, $camera); }
    private function owns(User $user, CctvCamera $camera): bool { return $user->isAdminRt() && (int) $camera->rt_id === $user->managedRtId(); }
}
