<?php

declare(strict_types=1);

namespace App\Tests\Functional\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductControllerTest extends WebTestCase
{
    public function test_create_product_endpoint__should_return_201_with_location_header(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['name' => 'Widget', 'price_amount' => 999, 'price_currency' => 'EUR']),
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHasHeader('Location');
        $this->assertStringStartsWith('/api/products/', $client->getResponse()->headers->get('Location'));
    }

    public function test_create_product_endpoint__when_empty_name__should_return_422_with_error(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['name' => '', 'price_amount' => 100, 'price_currency' => 'EUR']),
        );

        $this->assertResponseStatusCodeSame(422);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_create_product_endpoint__when_negative_price__should_return_422(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['name' => 'Widget', 'price_amount' => -1, 'price_currency' => 'EUR']),
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function test_get_product_endpoint__when_exists__should_return_product_data(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/products',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['name' => 'Widget Pro', 'price_amount' => 1999, 'price_currency' => 'USD']),
        );
        $location = $client->getResponse()->headers->get('Location');

        $client->request('GET', $location);

        $this->assertResponseIsSuccessful();
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Widget Pro', $body['name']);
        $this->assertSame(1999, $body['price_amount']);
        $this->assertSame('USD', $body['price_currency']);
        $this->assertArrayHasKey('id', $body);
    }

    public function test_get_product_endpoint__when_not_found__should_return_404_with_error(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products/00000000-0000-0000-0000-000000000000');

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_list_products_endpoint__should_return_array(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($body);
    }
}
