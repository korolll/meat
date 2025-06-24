<?php

namespace App\Services\Framework\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Illuminate\Support\Str;

abstract class MigrationCSV extends Migration
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var bool
     */
    protected $needGenerateUuid = false;

    /**
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $this->data = $this->data();
    }

    /**
     * @return string
     */
    abstract protected function getTable();

    /**
     *
     */
    protected function generateUuid()
    {
        return Str::orderedUuid()->toString();
    }

    /**
     * @return void
     */
    public function up()
    {
        DB::table($this->getTable())->insert($this->data);
    }

    /**
     * @return void
     */
    public function down()
    {
        $ids = Arr::pluck($this->data, $this->getKeyName());

        DB::table($this->getTable())->whereIn($this->getKeyName(), $ids)->delete();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected function data()
    {
        $rows = array_map('str_getcsv', file($this->getFullFilename(), FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($rows);

        return array_map(function ($values) use ($keys) {
            $row = array_combine($keys, $values);

            foreach ($this->getTimestampColumns() as $col) {
                if (Arr::has($row, $col) === false) {
                    Arr::set($row, $col, now());
                }
            }

            if ($this->needGenerateUuid) {
                Arr::set($row, $this->getKeyName(), $this->generateUuid());
            }

            return $row;
        }, $rows);
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    protected function getFullFilename()
    {
        return database_path("migrations/csv/{$this->getFilename()}");
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getFilename()
    {
        $reflection = new ReflectionClass($this);

        return str_replace('.php', '.csv', basename($reflection->getFileName()));
    }

    /**
     * @return string
     */
    protected function getKeyName()
    {
        return 'id';
    }

    /**
     * @return array
     */
    protected function getTimestampColumns()
    {
        return ['created_at', 'updated_at'];
    }
}
