<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user with hashed password.
     *
     * @param array $data User registration data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // Hash the password
        ]);
    }

    /**
     * Find user by email address.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Mark the user's email as verified.
     *
     * @param User $user
     * @return void
     */
    public function verify(User $user): void
    {
        $user->email_verified_at = now(); // Set verification timestamp
        $user->save();
    }

    /**
     * Update the given user's data.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
