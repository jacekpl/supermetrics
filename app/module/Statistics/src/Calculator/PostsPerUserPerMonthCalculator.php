<?php

namespace Statistics\Calculator;

use DateTimeImmutable;
use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class PostsPerUserPerMonthCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $totalPostsPerUser = [];

    /**
     * @var array
     */
    private $months = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();
        $this->totalPostsPerUser[$key] = ($this->totalPostsPerUser[$key] ?? 0) + 1;
        if(!in_array($postTo->getDate()->format('Ym'), $this->months)) {
            $this->months[] = $postTo->getDate()->format('Ym');
        }
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        sort($this->months);
        $earliestMonth = DateTimeImmutable::createFromFormat('Ym', $this->months[0]);
        $latestMonth = DateTimeImmutable::createFromFormat('Ym', $this->months[count($this->months) - 1]);
        $months = $latestMonth->diff($earliestMonth)->m + 1;

        $stats = new StatisticsTo();
        foreach ($this->totalPostsPerUser as $user => $posts) {
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($user)
                ->setValue(round($posts / $months, 2))
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
