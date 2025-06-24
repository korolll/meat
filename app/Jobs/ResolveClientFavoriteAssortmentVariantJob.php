<?php

namespace App\Jobs;

use App\Services\Management\Promos\FavoriteAssortment\Resolver\FavoriteAssortmentVariantResolverInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResolveClientFavoriteAssortmentVariantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $force;
    public array $clientUuids;

    /**
     * @param array $clientUuids
     * @param bool  $force
     */
    public function __construct(array $clientUuids = [], bool $force = false)
    {
        $this->force = $force;
        $this->clientUuids = $clientUuids;
    }

    /**
     *
     */
    public function handle()
    {
        /** @var FavoriteAssortmentVariantResolverInterface $resolver */
        $resolver = app(FavoriteAssortmentVariantResolverInterface::class);
        $resolver->resolve(null, $this->clientUuids, $this->force);
    }
}
