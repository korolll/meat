<?php

namespace App\Http\Controllers\API;

use App\Contracts\Models\Counter\IncrementCounterContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\Documents\Word\SupplyContractWordTemplate;
use App\Services\Models\User\UserUpdaterInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * @return mixed
     */
    public function show()
    {
        $user = $this->user;

        return UserResource::make($user);
    }

    /**
     * @param ProfileUpdateRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function update(ProfileUpdateRequest $request)
    {
        /** @var UserUpdaterInterface $updater */
        $updater = app(UserUpdaterInterface::class);
        $user = DB::transaction(function () use ($updater, $request) {
            return $updater->update(
                $this->user,
                $request->validated(),
                $request->getAdditionalEmails(),
                $request->getFiles()
            );
        });

        return UserResource::make($user);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function supplyContract(Request $request)
    {
        $this->authorize('supply-contract', $this->user);

        $day = now()->format('d.m.Y');
        $counterName = 'profile_supply_contract_calls_at_' . $day;
        $number = (int) app(IncrementCounterContract::class)->increment($counterName);

        $isPDF = $request->exists('toPDF');
        $fileType = $isPDF ? 'pdf' : 'docx';

        return SupplyContractWordTemplate::make($this->user, $number, $isPDF)
            ->toResponse("Договор поставки поставщика ООО Тилси.{$fileType}");
    }

    /**
     * @param array $additionalEmails
     */
    protected function syncAdditionalEmails(array $additionalEmails): void
    {
        $this->user->userAdditionalEmails()->delete();

        foreach ($additionalEmails as $email) {
            $this->user->userAdditionalEmails()->create(
                compact('email')
            );
        }
    }

    /**
     * @param array $files
     */
    protected function syncFiles(array $files): void
    {
        $this->user->files()->sync($files);
    }
}
