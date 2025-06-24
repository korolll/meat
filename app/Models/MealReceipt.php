<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealReceipt extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'meal-receipt';

    /**
     * @var bool
     */
    public $incrementing = false;

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
    protected $fillable = [
        'name',
        'section',
        'title',
        'description',
        'ingredients',
        'file_uuid',
        'duration',
    ];

    protected $casts = [
        'ingredients' => 'array'
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(GenerateUuidPrimary::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mealReceiptTabs(): HasMany
    {
        return $this->hasMany(MealReceiptTab::class);
    }

    public function assortments(): BelongsToMany
    {
        return $this->belongsToMany(Assortment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientLikes(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_meal_receipt_likes')
            ->withPivot('is_positive');
    }
}
