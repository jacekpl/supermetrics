<?php

namespace Tests\unit\module\Statistics\src\Calculator;

use DateTime;
use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\PostsPerUserPerMonthCalculator;
use Statistics\Dto\ParamsTo;

class PostsPerUserPerMonthCalculatorTest extends TestCase
{
    public function testPostsPerUserPerMonthCalculation(): void
    {
        $params = new ParamsTo();
        $params->setStartDate(new \DateTime('2023-03-01 00:00:00'));
        $params->setEndDate(new \DateTime('2023-06-30 23:59:59'));
        $params->setStatName('test');

        $perUserPerMonthCalculator = new PostsPerUserPerMonthCalculator();
        $perUserPerMonthCalculator->setParameters($params);
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_1', new DateTime('2023-03-24 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_2', new DateTime('2023-03-26 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_3', new DateTime('2023-03-29 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_1', new DateTime('2023-03-30 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_1', new DateTime('2023-03-31 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_2', new DateTime('2023-04-26 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_1', new DateTime('2023-04-26 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_1', new DateTime('2023-06-01 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_2', new DateTime('2023-06-02 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_3', new DateTime('2023-06-07 17:20:03')));
        $perUserPerMonthCalculator->accumulateData($this->newPost('user_4', new DateTime('2023-06-07 17:20:03')));
        $calculation = $perUserPerMonthCalculator->calculate();

        $this->assertSame('test', $calculation->getName());
        $children = $calculation->getChildren();
        $this->assertNotEmpty($children);
        $this->assertCount(4, $children);

        foreach($children as $child) {
            $this->assertEquals($this->postsPerUserResults($child->getSplitPeriod()), $child->getValue());
        }
    }

    /**
     * @return \SocialPost\Dto\SocialPostTo
     */
    private function newPost(string $authorId, DateTime $dateTime): SocialPostTo
    {
        $post = new SocialPostTo();
        $post->setAuthorId($authorId);
        $post->setDate($dateTime);

        return $post;
    }

    private function postsPerUserResults(string $userId): float
    {
        $results = [
            'user_1' => 1.25,
            'user_2' => 0.75,
            'user_3' => 0.5,
            'user_4' => 0.25,
        ];

        return $results[$userId];
    }
}
