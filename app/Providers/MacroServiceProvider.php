<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->macroBuilder();
        $this->macroCollection();
    }

    /**
     * @return void
     */
    protected function macroBuilder()
    {
        /**
         * @param string $virtualColumn
         * @param string $alias
         * @param array $parameters
         * @return $this|\Illuminate\Database\Query\Builder|mixed
         * @instantiated
         */
        Builder::macro('addVirtualColumn', function ($virtualColumn, string $alias, array $parameters = []) {
            /** @var Builder $this */
            return call_user_func([$virtualColumn, 'apply'], $this, $alias, ...$parameters);
        });
    }

    /**
     * @return void
     */
    protected function macroCollection()
    {
        /**
         * @param array $options
         * @return bool
         * @throws \Throwable
         * @instantiated
         */
        Collection::macro('saveOrFail', function (array $options = []) {
            return DB::transaction(function () use ($options) {
                /** @var Collection $this */
                $this->each(function (Model $model) use ($options) {
                    $model->save($options);
                });

                return true;
            });
        });

        /**
         * @return \Illuminate\Database\Eloquent\Collection
         * @instantiated
         */
        Collection::macro('toEloquent', function () {
            /** @var Collection $this */
            return \Illuminate\Database\Eloquent\Collection::wrap($this);
        });
    }
}
