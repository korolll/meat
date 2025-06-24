<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'story_name',
        'logo_file_uuid',
        'show_from',
        'show_to'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'show_from',
        'show_to',
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\CarbonInterface|null          $moment
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShowed(Builder $query, ?CarbonInterface $moment = null)
    {
        if (! $moment) {
            $moment = now();
        }

        return $query
            ->whereRaw('?::timestamptz <@ tstzrange(show_from, show_to)')
            ->addBinding($moment);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storyTabs(): HasMany
    {
        return $this->hasMany(StoryTab::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logoFile()
    {
        return $this->belongsTo(File::class);
    }
}
