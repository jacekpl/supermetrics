<?php

namespace Tests\unit\module\SocialPosts\src\Service;

use PHPUnit\Framework\TestCase;
use SocialPost\Dto\FetchParamsTo;
use SocialPost\Hydrator\FictionalPostHydrator;
use SocialPost\Service\SocialPostService;
use Tests\unit\module\SocialPosts\src\Driver\TestFictionalDriver;

class SocialPostServiceTest extends TestCase
{
    public function testFetchPosts(): void
    {
        $socialPostService = new SocialPostService(new TestFictionalDriver(), new FictionalPostHydrator());
        $posts = $socialPostService->fetchPosts(new FetchParamsTo(1));
        $this->assertEquals(4, iterator_count($posts));
    }
}
