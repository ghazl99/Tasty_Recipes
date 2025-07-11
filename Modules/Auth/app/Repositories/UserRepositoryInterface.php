<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\Models\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function findByEmail(string $email): ?User;
    public function verify(User $user): void;
    public function update(User $user, array $data): User;
    public function findById(int $id): ?User;
}
