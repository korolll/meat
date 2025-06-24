<?php

namespace App\Services\Database;

use App\Contracts\Database\ToQueryTransformerContract;

/**
 * Трансформирует пользовательские запросы в to_tsquery() формат.
 *
 * Пример:
 *
 * >> скажи, как у тебя дела?
 * << скажи & как & у & тебя & дела:*
 */
class PhraseToQueryTransformer implements ToQueryTransformerContract
{
    /**
     * @param string $query
     * @return null|string
     */
    public function transform(string $query): ?string
    {
        $query = mb_strtolower($query);

        if (empty($query = $this->convertToAlphanumeric($query))) {
            return null;
        };

        return $this->useWildcardSearch(
            $this->glueWordsUsingAnd(
                $this->extractWords($query)
            )
        );
    }

    /**
     * @param string $query
     * @return string
     */
    protected function convertToAlphanumeric(string $query): string
    {
        return preg_replace('/[^\w\s]/ui', '', $query);
    }

    /**
     * @param string $query
     * @return array
     */
    protected function extractWords(string $query): array
    {
        return array_filter(explode(' ', $query));
    }

    /**
     * @param array $words
     * @return string
     */
    protected function glueWordsUsingAnd(array $words): string
    {
        return implode(' & ', $words);
    }

    /**
     * @param string $query
     * @return string
     */
    protected function useWildcardSearch(string $query): string
    {
        return $query . ':*';
    }
}
