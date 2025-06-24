<?php

namespace App\Services\Management\Promos\DiverseFood;

use Illuminate\Console\Command;

class CalculatePromoDiverseFoodCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'promo-diverse-food:calculate';

    /**
     * @var string
     */
    protected $description = 'Выполняет расчет скидок по акции "Разнообразное питание" на текущий месяц';

    /**
     * @return void
     */
    public function handle()
    {
        /** @var DiscountCalculatorInterface $calculator */
        $calculator = app(DiscountCalculatorInterface::class);
        $calculator->calculate();
    }
}
