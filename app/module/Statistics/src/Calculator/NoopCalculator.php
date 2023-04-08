<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class NoopCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $totalPostsPerUserPerMonth = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getDate()->format('\M\o\n\t\h F, Y');
        $this->totalPostsPerUserPerMonth[$key][$postTo->getAuthorId()] = ($this->totalPostsPerUserPerMonth[$key][$postTo->getAuthorId()] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        foreach ($this->totalPostsPerUserPerMonth as $splitPeriod => $users) {
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($splitPeriod)
                ->setValue(round(array_sum($users) / count($users), 2))
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
