<?php

namespace App\Models;

use App\Observers\GenerateCreatedAt;
use App\Observers\GenerateUuidPrimary;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class File extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $casts = [
        'thumbnails' => 'array',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'file_category_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            GenerateCreatedAt::class,
        ]);
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/tiff']);
    }

    /**
     * @param int $width
     * @param int $height
     * @return bool
     */
    public function isThumbnailExists(int $width, int $height): bool
    {
        return array_key_exists("{$width}x{$height}", $this->thumbnails);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $path
     */
    public function setThumbnail(int $width, int $height, string $path): void
    {
        $thumbnails = $this->thumbnails;

        Arr::set($thumbnails, "{$width}x{$height}", $path);

        $this->thumbnails = $thumbnails;
    }

    /**
     * @param string $value
     * @return array
     */
    public function getThumbnailsAttribute($value): array
    {
        return $value ? $this->fromJson($value) : [];
    }

    /**
     * @param array|null $value
     */
    public function setThumbnailsAttribute(?array $value)
    {
        $this->attributes['thumbnails'] = $value ? $this->asJson($value) : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\CarbonInterface $carbon
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedBefore(Builder $query, CarbonInterface $carbon)
    {
        return $query->where($this->qualifyColumn('created_at'), '<', $carbon);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fileCategory()
    {
        return $this->belongsTo(FileCategory::class)->withTrashed();
    }
}
