<?php

namespace Tests\unit\module\App\src\Controller;

use PHPUnit\Framework\TestCase;
use SocialPost\Dto\FetchParamsTo;
use SocialPost\Hydrator\FictionalPostHydrator;
use SocialPost\Service\SocialPostService;
use Statistics\Builder\ParamsBuilder;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;
use Statistics\Enum\StatsEnum;
use Statistics\Service\StatisticsService;
use Tests\unit\module\SocialPosts\src\Driver\TestFictionalDriver;

class StatisticsControllerTest extends TestCase
{
    public function testStatistics(): void
    {
        $socialPostService = new SocialPostService(new TestFictionalDriver(), new FictionalPostHydrator());
        $posts = $socialPostService->fetchPosts(new FetchParamsTo(1));
        $statsService = new StatisticsService(new StatisticsCalculatorFactory());

        $params = ParamsBuilder::reportStatsParams(new \DateTime('2018-08-10'), new \DateTime('2018-08-10'));
        $stats = $statsService->calculateStats($posts, $params);

        foreach ($stats->getChildren() as $statisticsTo) {
            switch ($statisticsTo->getName()) {
                case StatsEnum::AVERAGE_POST_LENGTH:
                    $this->assertEquals(495.25, $statisticsTo->getValue());
                    break;
                case StatsEnum::MAX_POST_LENGTH:
                    $this->assertEquals(638.0, $statisticsTo->getValue());
                    break;
                case StatsEnum::TOTAL_POSTS_PER_WEEK:
                    $children = $statisticsTo->getChildren();
                    $this->assertEquals(1, count($children));
                    $this->assertEquals(4.0, $children[0]->getValue());
                    $this->assertEquals("Week 32, 2018", $children[0]->getSplitPeriod());
                    break;
                case StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH:
                    $children = $statisticsTo->getChildren();
                    $this->assertEquals(1, count($statisticsTo->getChildren()));
                    $this->assertEquals(1.0, $children[0]->getValue());
                    $this->assertEquals("Month August, 2018", $children[0]->getSplitPeriod());
                    break;
            }
        }
    }
}
