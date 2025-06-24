<?php

namespace App\Services\Framework\Database\Migrations;

use Illuminate\Support\Facades\DB;

trait ManagesComments
{
    /**
     * @param string $table
     * @param string|array $column
     * @param string|null $comment
     */
    private function commentOnColumn(string $table, $column, ?string $comment = null): void
    {
        if (is_array($column)) {
            foreach ($column as $columnName => $columnComment) {
                $this->commentOnColumn($table, $columnName, $columnComment);
            }

            return;
        }

        $comment = $this->prepareComment($comment);

        DB::statement("COMMENT ON COLUMN {$table}.{$column} IS {$comment}");
    }

    /**
     * @param string $table
     * @param string|null $comment
     */
    private function commentOnTable(string $table, ?string $comment): void
    {
        $comment = $this->prepareComment($comment);

        DB::statement("COMMENT ON TABLE {$table} IS {$comment}");
    }

    /**
     * @param string|null $comment
     * @return string
     */
    private function prepareComment(?string $comment): string
    {
        if (is_null($comment)) {
            return 'NULL';
        }

        return "'" . str_replace("'", "''", $comment) . "'";
    }
}
