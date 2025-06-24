<?php

namespace App\Services\Integrations\Atol;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class AtolTransferFile implements Arrayable
{
    /**
     * @var array
     */
    private $contents = [
        '##@@&&',
        '#',
    ];

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(PHP_EOL, $this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->contents;
    }

    /**
     * @param string $command
     * @param array $dataLines
     */
    protected function command(string $command, array $dataLines = []): void
    {
        $this->contents[] = Str::start($command, '$$$');

        $this->dataLines($dataLines);
    }

    /**
     * @param array|string $dataLine
     */
    protected function dataLine($dataLine): void
    {
        if (is_array($dataLine)) {
            $dataLine = implode(';', array_map([$this, 'prepareData'], $dataLine));
        }

        $this->contents[] = $dataLine;
    }

    /**
     * @param array $dataLines
     */
    protected function dataLines(array $dataLines): void
    {
        foreach ($dataLines as $dataLine) {
            $this->dataLine($dataLine);
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function prepareData($data): string
    {
        if (is_null($data)) {
            return '';
        }

        if (is_string($data)) {
            return str_replace(';', '¤', $data);
        }

        if (is_float($data) || is_double($data)) {
            return round($data, 2);
        }

        return $data;
    }
}
