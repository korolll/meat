<?php

namespace App\Services\Models\File;

use App\Contracts\Models\File\FindUnusedFilesContract;
use App\Models\File;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FindUnusedFiles implements FindUnusedFilesContract
{
    /**
     * @return \Illuminate\Support\Collection|\App\Models\File[]
     */
    public function find(): Collection
    {
        $from = DB::raw('unused_files() as files');

        return File::query()->from($from)->createdBefore($this->createdBefore())->get();
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    private function createdBefore(): CarbonInterface
    {
        return now()->subDay();
    }
}
