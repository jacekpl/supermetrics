<?php

namespace Tests\unit\module\SocialPosts\src\Driver;

use SocialPost\Driver\SocialDriverInterface;
use Traversable;

class TestFictionalDriver implements SocialDriverInterface
{
    public function fetchPostsByPage(int $page): Traversable
    {
        $json = file_get_contents(__DIR__ . '/../../../../../data/social-posts-response.json');
        $posts = json_decode($json, true);
        yield from $posts['data']['posts'];
    }
}
