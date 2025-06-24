<?php

namespace App\Services\Management\Promos\DiverseFood\ClientStatisticReport;

use Spatie\DataTransferObject\DataTransferObject;


class ReportRow extends DataTransferObject
{
    /**
     * @var string
     */
    public string $client_uuid;

    /**
     * @var int
     */
    public int $count_purchases = 0;

    /**
     * @var int
     */
    public int $count_rating_scores = 0;
}
