<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository extends BaseRepository
{
    protected function model(): string
    {
        return User::class;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->where(['email' => $email])->first();
    }

    /**
     * Set passcode for user
     *
     * @param string $userId
     * @param string $passcode
     * @return User
     */
    public function setPasscode(string $userId, string $passcode): User
    {
        $user = $this->find($userId);

        $user->update([
            'passcode' => Hash::make($passcode),
            'passcode_set_at' => now(),
        ]);

        return $user->fresh();
    }

    /**
     * Verify passcode
     *
     * @param string $userId
     * @param string $passcode
     * @return bool
     */
    public function verifyPasscode(string $userId, string $passcode): bool
    {
        $user = $this->find($userId);

        if (!$user->passcode) {
            return false;
        }

        return Hash::check($passcode, $user->passcode);
    }

    /**
     * Check if user has passcode set
     *
     * @param string $userId
     * @return bool
     */
    public function hasPasscode(string $userId): bool
    {
        $user = $this->find($userId);
        return !empty($user->passcode);
    }

    /**
     * Get users with roles
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getWithRoles(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery();
        $query = $this->applyTenantScope($query);
        $query->with('roles');

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        return $query->paginate($perPage);
    }
}

