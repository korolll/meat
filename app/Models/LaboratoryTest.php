<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Observers\LaboratoryTestObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryTest extends Model
{
    use SoftDeletes;

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
        'laboratory_test_status_id',
        'laboratory_test_appeal_type_uuid',

        'customer_full_name',
        'customer_organization_name',
        'customer_organization_address',
        'customer_inn',
        'customer_kpp',
        'customer_ogrn',

        'customer_position',
        'customer_bank_correspondent_account',
        'customer_bank_current_account',
        'customer_bank_identification_code',
        'customer_bank_name',

        'batch_number',
        'parameters',

        'assortment_barcode',
        'assortment_uuid',
        'assortment_name',
        'assortment_manufacturer',
        'assortment_production_standard_id',
        'assortment_supplier_user_uuid',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            LaboratoryTestObserver::class
        ]);
    }

    /**
     * @return bool
     */
    public function getIsNewAttribute()
    {
        return $this->laboratory_test_status_id === LaboratoryTestStatus::ID_NEW;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeNew(Builder $query)
    {
        return $query->where('laboratory_test_status_id', LaboratoryTestStatus::ID_NEW);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortment()
    {
        return $this->belongsTo(Assortment::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_user_uuid')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function executorUser()
    {
        return $this->belongsTo(User::class, 'executor_user_uuid')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customerFiles()
    {
        return $this->belongsToMany(File::class, 'file_laboratory_test', 'laboratory_test_uuid')
            ->wherePivot('file_category_id', FileCategory::ID_LABORATORY_TEST_FILE_CUSTOMER)
            ->withPivot('public_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function executorFiles()
    {
        return $this->belongsToMany(File::class, 'file_laboratory_test', 'laboratory_test_uuid')
            ->wherePivot('file_category_id', FileCategory::ID_LABORATORY_TEST_FILE_EXECUTOR)
            ->withPivot('public_name');
    }
}
