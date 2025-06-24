<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseView extends Model
{
    /**
     * @var string
     */
    protected $table = 'purchases_view';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'source_line_id';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at'
    ];

    /**
     * READONLY
     *
     * @param array $options
     *
     * @return bool
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        throw new \Exception('Readonly model');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function ratingReceipt()
    {
       return $this->ratingMorphOne(ReceiptLine::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function ratingOrder()
    {
        return $this->ratingMorphOne(OrderProduct::class);
//        return $this->morphOne(RatingScore::class, 'rated_through_reference', OrderProduct::MORPH_TYPE_ALIAS);
//        return $this->morphOne(RatingScore::class, 'rated_through_reference');
    }

    /**
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    protected function ratingMorphOne(string $class)
    {
        $instance = $this->newRelatedInstance(RatingScore::class);
        $name = 'rated_through_reference';
        $type = null;
        $id = null;
        $localKey = null;

        [$type, $id] = $this->getMorphs($name, $type, $id);

        $table = $instance->getTable();

        $localKey = $localKey ?: $this->getKeyName();
        $obj = new $class();
        $obj->source_line_id = $this->source_line_id;

        return $this->newMorphOne($instance->newQuery(), $obj, $table.'.'.$type, $table.'.'.$id, $localKey);
    }
}
