<?php

namespace App\Console\Commands;

use App\Contracts\Management\Product\ByAssortmentProductMakerContract;
use App\Models\Assortment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MakeProductsByAssortmentMatricesCommand extends Command
{
    /**
     * Название команды
     *
     * @var string
     */
    protected $signature = 'make:products:assortment-matrices';

    /**
     * Описание команды
     *
     * @var string
     */
    protected $description = 'Создает продукты для пользователей по их асортиментой матрице';

    /**
     * @var ByAssortmentProductMakerContract
     */
    protected $productMaker;

    /**
     * MakeProductsByAssortmentMatricesCommand constructor.
     * @param ByAssortmentProductMakerContract $productMaker
     */
    public function __construct(ByAssortmentProductMakerContract $productMaker)
    {
        $this->productMaker = $productMaker;
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        User::each(function (User $user) {
            $user->assortmentMatrix()->where(function (Builder $query) use ($user) {
                $query->whereDoesntHave('products', function (Builder $query) use ($user)  {
                    /** @var Builder|Product $query */
                    $query->ownedBy($user);
                });
            })->each(function (Assortment $assortment) use ($user) {
                $this->productMaker->make($user, $assortment);
            });
        });
    }
}
