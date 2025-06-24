<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;

class GenerateImageFileThumbnails implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /**
     * Размеры генерируемых превью
     */
    private const THUMBNAIL_DIMENSIONS = [
        [200, 200],
        [1000, 1000],
    ];

    /**
     * @var \App\Models\File
     */
    private $file;

    /**
     * @param \App\Models\File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(): void
    {
        $image = Image::make($this->readFile());

        foreach (self::THUMBNAIL_DIMENSIONS as [$width, $height]) {
            if ($this->file->isThumbnailExists($width, $height) === false) {
                $this->makeThumbnail($image, $width, $height);
            }
        }

        $this->file->save();
    }

    /**
     * @param \Intervention\Image\Image $image
     * @param int $width
     * @param int $height
     */
    private function makeThumbnail(InterventionImage $image, int $width, int $height): void
    {
        $path = $this->makeThumbnailPath($width, $height);
        $contents = $this->makeThumbnailContents($image, $width, $height);

        if ($this->saveThumbnail($path, $contents)) {
            $this->file->setThumbnail($width, $height, $path);
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     */
    private function makeThumbnailPath(int $width, int $height): string
    {
        return "thumbnails/{$this->file->file_category_id}/{$width}x{$height}/{$this->file->uuid}.png";
    }

    /**
     * @param int $width
     * @param int $height
     * @param \Intervention\Image\Image $image
     * @return string
     */
    private function makeThumbnailContents(InterventionImage $image, int $width, int $height): string
    {
        return $image->fit($width, $height)->encode('png');
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function readFile(): string
    {
        return Storage::get($this->file->path);
    }

    /**
     * @param string $path
     * @param string $contents
     * @return bool
     */
    private function saveThumbnail(string $path, string $contents): bool
    {
        return Storage::put($path, $contents);
    }
}
