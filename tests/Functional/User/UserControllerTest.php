<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Bridge\Redis\Transport\Connection as RedisConnection;

final class UserControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->redisConnection()->cleanup();
    }

    public function test_create_user_endpoint__should_return_201_with_location_header(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'john@test.com', 'name' => 'John']),
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHasHeader('Location');
        $this->assertStringStartsWith('/api/users/', $client->getResponse()->headers->get('Location'));
    }

    public function test_create_user_endpoint__when_invalid_email__should_return_422_with_error(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'not-an-email', 'name' => 'John']),
        );

        $this->assertResponseStatusCodeSame(422);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_create_user_endpoint__when_duplicate_email__should_return_409(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'dup@test.com', 'name' => 'First']),
        );
        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'dup@test.com', 'name' => 'Second']),
        );

        $this->assertResponseStatusCodeSame(409);
    }

    public function test_get_user_endpoint__when_exists__should_return_user_data(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'mary@test.com', 'name' => 'Mary']),
        );
        $location = $client->getResponse()->headers->get('Location');

        $client->request('GET', $location);

        $this->assertResponseIsSuccessful();
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('mary@test.com', $body['email']);
        $this->assertSame('Mary', $body['name']);
        $this->assertSame('active', $body['status']);
        $this->assertArrayHasKey('id', $body);
    }

    public function test_get_user_endpoint__when_not_found__should_return_404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/00000000-0000-0000-0000-000000000000');

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_list_users_endpoint__should_return_array(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($body);
    }

    public function test_create_user_endpoint__should_queue_user_was_created_event(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'alice@test.com', 'name' => 'Alice']),
        );

        $this->assertResponseStatusCodeSame(201);

        $this->assertSame(1, $this->redisConnection()->getMessageCount(), 'UserWasCreated for Alice should be queued in Redis');
    }

    public function test_consuming_user_was_created_event__should_trigger_default_product_creation(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/users',
            server:  ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'bob@test.com', 'name' => 'Bob']),
        );

        $this->assertResponseStatusCodeSame(201);

        $pending = $this->redisConnection()->getMessageCount();

        $consumeCommand = (new Application(static::$kernel))->find('messenger:consume');
        (new CommandTester($consumeCommand))->execute([
            'receivers' => ['async'],
            '--limit'   => $pending,
        ]);

        $client->request('GET', '/api/products');
        $products = json_decode($client->getResponse()->getContent(), true);

        $found = array_filter($products, fn(array $p) => str_contains($p['name'], 'Bob'));
        $this->assertNotEmpty($found, 'Handler should have created a welcome product for Bob');
    }

    private function redisConnection(): RedisConnection
    {
        return RedisConnection::fromDsn($_ENV['MESSENGER_TRANSPORT_DSN']);
    }
}
