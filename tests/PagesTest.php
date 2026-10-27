<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PagesTest extends WebTestCase
{
    public function testMainPagesReturnSuccessfulResponses(): void
    {
        $client = static::createClient();

        $urls = [
            '/product',
            '/category',
            '/supplier',
            '/warehouse',
            '/stock/movement',
        ];

        foreach ($urls as $url) {
            $client->request('GET', $url);

            self::assertResponseIsSuccessful(
                sprintf('Page %s did not return a successful response.', $url)
            );
        }
    }
}