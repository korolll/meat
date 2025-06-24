<?php

namespace App\Services\Framework\Routing;

use App\Exceptions\SubstituteBindingsException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    /**
     * @param \Illuminate\Routing\Route $route
     * @return \Illuminate\Routing\Route
     * @throws \Throwable
     */
    public function substituteBindings($route)
    {
        try {
            return parent::substituteBindings($route);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SubstituteBindingsException($e->getMessage());
        }
    }

    /**
     * @param \Illuminate\Routing\Route $route
     * @return void
     * @throws \Throwable
     */
    public function substituteImplicitBindings($route)
    {
        try {
            parent::substituteImplicitBindings($route);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SubstituteBindingsException($e->getMessage());
        }
    }
}
