<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\ActivateClientFavoriteAssortmentVariantRequest;
use App\Http\Responses\Clients\API\Profile\ClientPromoFavoriteAssortmentVariantResponse;
use App\Models\Assortment;
use App\Models\ClientPromoFavoriteAssortmentVariant;
use App\Services\Framework\Http\CollectionRequest;
use App\Services\Management\Promos\FavoriteAssortment\FavoriteAssortmentActivatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PromoFavoriteAssortmentVariantController extends Controller
{
    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     *
     * @return \App\Http\Responses\Clients\API\Profile\ClientPromoFavoriteAssortmentVariantResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(CollectionRequest $request)
    {
        $this->authorize('index-owned', ClientPromoFavoriteAssortmentVariant::class);

        $client = $this->client;
        return new ClientPromoFavoriteAssortmentVariantResponse(
            $request,
            $client->clientPromoFavoriteAssortmentVariants()
        );
    }

    /**
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant                                      $variant
     * @param \App\Http\Requests\Clients\API\Profile\ActivateClientFavoriteAssortmentVariantRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function activateDiscount(ClientPromoFavoriteAssortmentVariant $variant, ActivateClientFavoriteAssortmentVariantRequest $request)
    {
        $this->authorize('activate', $variant);
        $assortmentUuid = $request->get('assortment_uuid');
        $assortment = Assortment::findOrFail($assortmentUuid);
        if ($assortment->isForbiddenForDiscount()) {
            throw new BadRequestHttpException('Assortment is forbidden for discount');
        }

        /** @var FavoriteAssortmentActivatorInterface $activator */
        $activator = app(FavoriteAssortmentActivatorInterface::class);
        $activator->activate($assortmentUuid, $variant);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
