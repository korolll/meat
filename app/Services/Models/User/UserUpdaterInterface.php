<?php


namespace App\Services\Models\User;


use App\Models\User;

interface UserUpdaterInterface
{
    public function update(User $user, array $properties, array $emails, array $files): User;
}
