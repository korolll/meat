<?php

namespace App\Http\Controllers\API;

use App\Events\FileUploaded;
use App\Exceptions\ClientExceptions\FileUploadFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileStoreRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\FileCategory;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class FileController extends Controller
{
    /**
     * @param FileStoreRequest $request
     *
     * @return mixed
     * @throws \Throwable
     */
    public function store(FileStoreRequest $request)
    {
        $this->authorize('create', File::class);

        $fileAttributes = $this->storeFile(
            $request->file_category_id,
            $request->file('file')
        );

        $file = new File($fileAttributes);
        $file->user()->associate($this->user);
        $file->saveOrFail();

        FileUploaded::dispatch($file);

        return FileResource::make($file);
    }

    /**
     * @param string       $fileCategoryId
     * @param UploadedFile $file
     *
     * @return array
     * @throws \App\Exceptions\TealsyException
     */
    protected function storeFile(string $fileCategoryId, UploadedFile $file)
    {
        if ($fileCategoryId === FileCategory::ID_ASSORTMENT_IMAGE) {
            $waterMarkPath = config('image.watermark.path');
            if ($waterMarkPath && file_exists($waterMarkPath)) {
                $this->applyWaterMark($waterMarkPath, $file);
            }
        }

        if (($path = $file->storePublicly($fileCategoryId)) === false) {
            throw new FileUploadFailedException();
        }

        return [
            'file_category_id' => $fileCategoryId,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * @param string                        $waterMarkPath
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return void
     */
    protected function applyWaterMark(string $waterMarkPath, UploadedFile $file)
    {
        $originalPath = $file->getRealPath();
        $img = Image::make($originalPath);
        $imgW = $img->getWidth();
        $imgH = $img->getHeight();

        $wm = Image::make($waterMarkPath);
        $wmW = $wm->getWidth();
        $wmH = $wm->getHeight();

        $op = $imgW > $wmW || $imgH > $wmH ? 1 : -1;

        if ($imgW > $imgH) {
            $targetW = $imgW;
            $targetH = $wmH + ($op * abs($imgW - $wmW));
        } else {
            $targetH = $imgH;
            $targetW = $wmW + ($op * abs($imgH - $wmH));
        }

        $wmResized = $wm->resize($targetW, $targetH);
        $img->insert($wmResized, 'center');
        $img->save($originalPath);
    }
}
