<?php

namespace App\Services\Models\Draft;

use App\Contracts\Models\Draft\DeleteDraftByNameContract;
use App\Contracts\Models\Draft\FindDraftByNameContract;
use App\Models\User;

class DeleteDraftByName implements DeleteDraftByNameContract
{
    /**
     * @var FindDraftByNameContract
     */
    protected $finder;

    /**
     * @param FindDraftByNameContract $finder
     */
    public function __construct(FindDraftByNameContract $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param User $user
     * @param string $name
     * @return bool
     * @throws \Throwable
     */
    public function delete(User $user, string $name): bool
    {
        $draft = $this->finder->find($user, $name);

        return $draft->delete();
    }
}
