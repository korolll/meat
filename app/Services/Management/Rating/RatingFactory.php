<?php

namespace App\Services\Management\Rating;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Model;

class RatingFactory implements RatingFactoryContract
{
    /**
     * @param Model $rated
     * @param string $ratingTypeId
     * @return Rating
     */
    public function updateOrCreate($rated, string $ratingTypeId): Rating
    {
        return Rating::updateOrCreate(
            $this->getAttributes($rated, $ratingTypeId),
            $this->getValues($rated, $ratingTypeId)
        );
    }

    /**
     * @param Model $rated
     * @param string $ratingTypeId
     * @return array
     */
    protected function getAttributes($rated, string $ratingTypeId): array
    {
        return [
            'reference_type' => $rated->getMorphClass(),
            'reference_id' => $rated->getKey(),
            'rating_type_id' => $ratingTypeId,
        ];
    }

    /**
     * @param Model $rated
     * @param string $ratingTypeId
     * @return array
     */
    protected function getValues($rated, string $ratingTypeId): array
    {
        $calculator = $this->getRatingCalculator($rated, $ratingTypeId);

        return [
            'value' => $calculator->calculate($rated),
        ];
    }

    /**
     * @param Model $rated
     * @param string $ratingTypeId
     * @return RatingCalculatorContract
     */
    protected function getRatingCalculator($rated, string $ratingTypeId): RatingCalculatorContract
    {
        $class = get_class($rated);

        return app(config("app.ratings.{$class}.ratings.{$ratingTypeId}"));
    }
}
