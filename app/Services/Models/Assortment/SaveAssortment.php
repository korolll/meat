<?php

namespace App\Services\Models\Assortment;

use App\Contracts\Models\Assortment\SaveAssortmentContract;
use App\Contracts\Models\Tag\FindOrCreateTagContract;
use App\Models\Assortment;
use App\Models\AssortmentBarcode;
use App\Structures\Models\Assortment\SavingAssortmentStructure;
use Illuminate\Support\Facades\DB;

class SaveAssortment implements SaveAssortmentContract
{
    /**
     * @var FindOrCreateTagContract
     */
    protected $tagFinder;

    /**
     * SaveAssortment constructor.
     * @param FindOrCreateTagContract $tagFinder
     */
    public function __construct(FindOrCreateTagContract $tagFinder)
    {
        $this->tagFinder = $tagFinder;
    }

    /**
     * @param Assortment $assortment
     * @param SavingAssortmentStructure $data
     * @return Assortment
     * @throws \Throwable
     */
    public function save(Assortment $assortment, SavingAssortmentStructure $data): Assortment
    {
        $assortment->fill($data->attributes);
        DB::transaction(function () use ($assortment, $data) {
            $assortment->save();
            $assortment->images()->sync($data->images);
            $assortment->assortmentProperties()->sync($data->properties);
            if ($data->tags !== null) {
                $this->syncTags($assortment, $data->tags);
            }

            if ($assortment->wasRecentlyCreated || $data->forceSyncFiles) {
                $assortment->files()->sync($data->files);
            }
            $this->saveBarcodes($assortment, $data->barcodes);
        });

        return $assortment;
    }

    /**
     * @param Assortment $assortment
     * @param array $tags
     * @return array
     */
    protected function syncTags(Assortment $assortment, array $tags): array
    {
        $uuIds = [];
        foreach ($tags as $tagName) {
            $uuIds[] = $this->tagFinder->findOrCreate($tagName)->uuid;
        }

        return $assortment->tags()->sync($uuIds);
    }

    protected function saveBarcodes(Assortment $assortment, array $barcodes)
    {
        $allCurrentBarcodes = $assortment->barcodes->keyBy('barcode');
        $foundBarcodes = [];

        foreach ($barcodes as $barcode) {
            if (! $allCurrentBarcodes->has($barcode)) {
                $barcodeModel = new AssortmentBarcode(compact('barcode'));
                $assortment->barcodes()->save($barcodeModel);
            } else {
                $barcodeModel = $allCurrentBarcodes[$barcode];
            }

            $foundBarcodes[$barcodeModel->barcode] = 1;
        }

        // Delete not added barcodes
        foreach ($allCurrentBarcodes as $currentBarcode) {
            if (! isset($foundBarcodes[$currentBarcode->barcode])) {
                $currentBarcode->forceDelete();
            }
        }

        $assortment->unsetRelation('barcodes');
    }
}
