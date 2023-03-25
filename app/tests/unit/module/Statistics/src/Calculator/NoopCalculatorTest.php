<?php

namespace Tests\unit\module\Statistics\src\Calculator;

use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Calculator\NoopCalculator;
use Statistics\Dto\ParamsTo;

class NoopCalculatorTest extends TestCase
{
    public function testCalculation(): void
    {
        $post1 = new SocialPostTo();
        $post1->setAuthorId("user_1");
        $post1->setDate(new \DateTime('2023-03-24 17:20:03'));

        $post2 = new SocialPostTo();
        $post2->setAuthorId("user_1");
        $post2->setDate(new \DateTime('2023-03-26 17:20:03'));

        $post3 = new SocialPostTo();
        $post3->setAuthorId("user_2");
        $post3->setDate(new \DateTime('2023-03-29 17:20:03'));

        $post4 = new SocialPostTo();
        $post4->setAuthorId("user_1");
        $post4->setDate(new \DateTime('2023-04-26 17:20:03'));

        $params = new ParamsTo();
        $params->setStartDate(new \DateTime('2023-03-01 00:00:00'));
        $params->setEndDate(new \DateTime('2023-04-30 23:59:59'));
        $params->setStatName('test');

        $noopCalculator = new NoopCalculator();
        $noopCalculator->setParameters($params);
        $noopCalculator->accumulateData($post1);
        $noopCalculator->accumulateData($post2);
        $noopCalculator->accumulateData($post3);
        $noopCalculator->accumulateData($post4);
        $calculation = $noopCalculator->calculate();

        $this->assertSame('test', $calculation->getName());
        $children = $calculation->getChildren();
        $this->assertNotEmpty($children);
        $this->assertCount(2, $children);

        $this->assertEquals(1.5, $children[0]->getValue());
        $this->assertEquals((new \DateTime('2023-03-01'))->format('\M\o\n\t\h F, Y'), $children[0]->getSplitPeriod());

        $this->assertEquals(1.0, $children[1]->getValue());
        $this->assertEquals((new \DateTime('2023-04-01'))->format('\M\o\n\t\h F, Y'), $children[1]->getSplitPeriod());
    }

    public function testCalculationFromFile(): void
    {
        $json = file_get_contents(__DIR__ . '/../../../../../data/social-posts-response.json');
        $postsArray = json_decode($json, true);
        $hydrator = new FictionalPostHydrator();
        $posts = array_map(function (array $post) use ($hydrator) {
            return $hydrator->hydrate($post);
        }, $postsArray['data']['posts']);


        $params = new ParamsTo();
        $params->setStartDate(new \DateTime('2018-08-01 00:00:00'));
        $params->setEndDate(new \DateTime('2018-08-31 23:59:59'));
        $params->setStatName('test');

        $noopCalculator = new NoopCalculator();
        $noopCalculator->setParameters($params);

        foreach ($posts as $post) {
            $noopCalculator->accumulateData($post);
        }

        $calculation = $noopCalculator->calculate();

        $this->assertSame('test', $calculation->getName());
        $children = $calculation->getChildren();
        $this->assertNotEmpty($children);
        $this->assertCount(1, $children);
        $this->assertEquals(1.0, $children[0]->getValue());
        $this->assertEquals("Month August, 2018", $children[0]->getSplitPeriod());
    }
}
