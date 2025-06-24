<?php

namespace App\Services\Debug;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

class DebugDataCollector
{
    protected array $debugData = [];

    protected bool $queryLogEnabled = false;

    public function __construct()
    {
    }

    public function measure(string $message, callable $callback, array $extra = [])
    {
        $start = microtime(true);
        try {
            return $callback();
        } finally {
            $time = microtime(true) - $start;
            $this->addLog('measure', [
                'message' => $message,
                'extra' => $extra,
                'time' => $time * 1000
            ]);
        }
    }

    public function enableQueryLog(): void
    {
        if ($this->queryLogEnabled) {
            return;
        }

        $this->queryLogEnabled = true;
        DB::listen(function (QueryExecuted $query) {
            $this->addLog('query', [
                'query' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });
    }

    protected function addLog(string $type, array $data)
    {
        $this->debugData[$type][] = $data;
    }

    public function getDebugData(): array
    {
        return $this->debugData;
    }
}
