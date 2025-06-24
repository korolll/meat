<?php

/*
|--------------------------------------------------------------------------
| Дополнительный индекс для IDE
|--------------------------------------------------------------------------
|
| Файл составляется вручную, в репозиторий попал не случайно.
| Выручает, когда ide-helper/macros не справляются, а хочется красоты.
|
*/

namespace App\Services\Framework\Contracts\Auth {

    class TokenAuthenticatable
    {
        /**
         * @see TokenAuthenticatable::scopeHasAuthenticationToken()
         * @param string $token
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function hasAuthenticationToken($token)
        {
            return null;
        }
    }
}

namespace Illuminate\Database\Eloquent {

    class Builder
    {
        /**
         * @see \Illuminate\Database\Eloquent\SoftDeletingScope
         * @return int
         */
        public function restore()
        {
            return 0;
        }

        /**
         * @see \Illuminate\Database\Eloquent\SoftDeletingScope
         * @param bool $withTrashed
         * @return $this
         */
        public function withTrashed($withTrashed = true)
        {
            return $this;
        }

        /**
         * @see \Illuminate\Database\Eloquent\SoftDeletingScope
         * @return $this
         */
        public function withoutTrashed()
        {
            return $this;
        }

        /**
         * @see \Illuminate\Database\Eloquent\SoftDeletingScope
         * @return $this
         */
        public function onlyTrashed()
        {
            return $this;
        }
    }
}
