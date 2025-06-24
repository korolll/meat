<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\CreateMealReceiptReactionRequest;
use App\Http\Resources\Clients\API\MealReceiptResource;
use App\Http\Responses\Clients\API\MealReceiptCollectionResponse;
use App\Http\Responses\MealReceiptUniqueSectionCollectionResponse;
use App\Models\MealReceipt;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Services\Database\VirtualColumns\MealReceiptIsFavorite;

class MealReceiptController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
        $query = MealReceipt::query()->select([
            'meal_receipts.*',
            'client_meal_receipt_likes.is_positive as client_like_value'
        ])
        ->leftJoin('client_meal_receipt_likes', function (JoinClause $join) {
            $join->on('client_meal_receipt_likes.meal_receipt_uuid', 'meal_receipts.uuid');
            $join->where('client_meal_receipt_likes.client_uuid', $this->client->uuid);
        });

        $query->addVirtualColumn(MealReceiptIsFavorite::class, 'is_favorite', [(string)optional($this->client)->uuid]);

        return MealReceiptCollectionResponse::create($query);
    }

    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function uniqueSections()
    {
        $query = MealReceipt::query()
            ->select(['section'])
            ->groupBy('section');
        return MealReceiptUniqueSectionCollectionResponse::create($query);
    }

    /**
     * @param \App\Models\MealReceipt $mealReceipt
     *
     * @return \App\Http\Resources\Clients\API\MealReceiptResource
     */
    public function show(MealReceipt $mealReceipt)
    {
        $like = $mealReceipt->clientLikes()
            ->select('client_meal_receipt_likes.is_positive')
            ->where('client_meal_receipt_likes.client_uuid', $this->client->uuid)
            ->toBase()
            ->first();

        $mealReceipt->client_like_value = $like ? $like->is_positive : null;

        return MealReceiptResource::make($mealReceipt);
    }

    /**
     * @param \App\Http\Requests\Clients\API\CreateMealReceiptReactionRequest $request
     * @param \App\Models\MealReceipt                                         $mealReceipt
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function reaction(CreateMealReceiptReactionRequest $request, MealReceipt $mealReceipt)
    {
        $client = $this->client;

        $validated = $request->validated();
        $exist = $mealReceipt->clientLikes()
            ->where('client_uuid', $client->uuid)
            ->exists();

        if (! $exist) {
            DB::table('client_meal_receipt_likes')->insert([[
                'client_uuid' => $client->uuid,
                'meal_receipt_uuid' => $mealReceipt->uuid,
                'is_positive' => $validated['is_positive'],
            ]]);
        } else {
            DB::table('client_meal_receipt_likes')
                ->where('meal_receipt_uuid', $mealReceipt->uuid)
                ->where('client_uuid', $client->uuid)
                ->update(['is_positive' => $validated['is_positive']]);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}
