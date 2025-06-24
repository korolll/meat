<?php

namespace App\Services\Documents\Word;

use App\Contracts\Models\Product\MakeProductsAvailableForRequestQueryContract;
use App\Models\LegalForm;
use App\Models\Product;
use App\Models\SignerType;
use App\Models\User;

class SupplyContractWordTemplate extends WordTemplate
{
    /**
     * @var int
     */
    protected $number;

    /**
     * SupplyContractWordTemplate constructor.
     * @param User $user
     * @param int $number
     * @param bool $isPDF
     */
    public function __construct(User $user, int $number, bool $isPDF)
    {
        $this->user = $user;
        $this->number = $number;

        parent::__construct(config('app.documents.word.supply_contract.path'), $isPDF);
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $now = now();
        $lastOfYear = $now->lastOfYear();

        return [
            'date' => $now->format('d.m.Y'),
            'number' => $this->getNumber(),
            'valid_until' => $lastOfYear->format('d.m.Y'),

            'full_name' => $this->user->full_name,

            'inn' => $this->user->inn,
            'kpp' => $this->user->kpp,
            'ogrn' => $this->user->ogrn,

            'org_name' => $this->user->organization_name,
            'org_address' => $this->user->organization_address,
            'address' => $this->user->address,

            'legal_form' => $this->user->legalForm->name,
            'legal_form_short' => $this->user->legalForm->short_name,

            'bank_name' => $this->user->bank_name,
            'bic' => $this->user->bank_identification_code,
            'bank_ks' => $this->user->bank_correspondent_account,
            'bank_rs' => $this->user->bank_current_account,

            'signer' => $this->getSigner(),
            'signer_fio' => $this->getSignerFullName(),
            'side_desc' => $this->getSideDescription(),
        ];
    }

    /**
     * @param int $cnt
     * @param string $name
     * @param string $unit
     * @param string $nds
     * @param string $price
     */
    protected function replaceTableRow(int $cnt, string $name, string $unit, string $nds, string $price)
    {
        $this->template->setValue('_t_number#' . $cnt, $cnt);
        $this->template->setValue('_t_name#' . $cnt, $name);
        $this->template->setValue('_t_unit#' . $cnt, $unit);
        $this->template->setValue('_t_nds#' . $cnt, $nds);
        $this->template->setValue('_t_price#' . $cnt, $price);
    }

    /**
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    protected function processTableWithDefault()
    {
        $this->template->cloneRow('_t_number', 1);
        $this->replaceTableRow(1, '-', '-', '-', '-');
    }

    /**
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    protected function processTemplate(): void
    {
        $userUuids = (array) config('app.documents.word.supply_contract.user_uuids');
        if (!$userUuids) {
            $this->processTableWithDefault();

            return;
        }

        $query = resolve(MakeProductsAvailableForRequestQueryContract::class)->make([
            'customer_user_uuid' => $this->user->uuid,
            'product_user_uuids' => $userUuids
        ]);

        $count = $query->count();
        if (!$count) {
            $this->processTableWithDefault();

            return;
        }

        $this->template->cloneRow('_t_number', $count);
        $cnt = 1;
        $query->each(function (Product $product) use (&$cnt) {
            $this->replaceTableRow(
                $cnt,
                $product->assortment->name,
                $product->assortment->assortmentUnit->short_name,
                (string) $product->assortment->nds_percent,
                (string) $product->price
            );
            $cnt++;
        });
    }


    /**
     * @return string
     */
    protected function getNumber(): string
    {
        return 'ТП' . now()->format('d/m-') . $this->number;
    }

    /**
     * @return string
     */
    protected function getSigner(): string
    {
        if ($this->user->legal_form_id === LegalForm::ID_IP) {
            return '';
        }

        switch ($this->user->signer_type_id) {
            case SignerType::ID_CONFIDANT:
                return (string) $this->user->signer_full_name;
            case SignerType::ID_GENERAL_DIRECTOR:
                return 'Генеральный директор';
            default:
                return '';
        }
    }

    /**
     * @return string
     */
    protected function getSignerFullName(): string
    {
        if ($this->user->legal_form_id === LegalForm::ID_IP) {
            return $this->user->full_name;
        }

        return (string) $this->user->signer_full_name;
    }

    /**
     * @return string
     */
    protected function getSideDescription(): string
    {
        $message = '';
        if ($this->user->legal_form_id === LegalForm::ID_IP) {
            $date = $this->user->date_of_ip_registration_certificate;
            $dateStr = $date ? $date->format('d.m.Y') : '';

            $message .= 'Индивидуальный предприниматель ' . $this->user->full_name . ', действующий на основании ';
            $message .= 'Свидетельства о государственной регистрации физического лица в качестве индивидуального предпринимателя ';
            $message .= '№' . $this->user->ip_registration_certificate_number;
            $message .= ' от ' . $dateStr . ', именуемый';
        } else {
            $message .= 'Генеральный директор ' . $this->user->legalForm->name . ' "' . $this->user->organization_name . '"';
            $message .= ', в лице ' . $this->user->signer_full_name;
            $message .= ' действующего на основании ';

            if ($this->user->signer_type_id === SignerType::ID_GENERAL_DIRECTOR) {
                $message .= 'устава';
            } else {
                $date = $this->user->date_of_power_of_attorney;
                $dateStr = $date ? $date->format('d.m.Y') : '';

                $message .= 'доверенности №' . $this->user->power_of_attorney_number;
                $message .= ' от ' . $dateStr;
            }

            $message .= ', именуемое';
        }

        return $message;
    }
}
