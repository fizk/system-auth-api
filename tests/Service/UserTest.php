<?php

use PHPUnit\Framework\TestCase;
use MongoDB\Database;
use MongoDB\Client;
use MongoDB\BSON\{ObjectId};
use Auth\Model\User as UserModel;
use Auth\Service\User;

class UserTest extends TestCase
{
    private ?Database $client;

    public function testGetSuccess()
    {
        $this->client->selectCollection('user')
            ->insertMany([
                [
                    '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                    'email' => 'some@email.com',
                    'first_name' => 'first',
                    'last_name' => 'last',
                ],
            ]);

        $service = (new User())
            ->setDriver($this->client);

        $expected = (new UserModel())
            ->setEmail('some@email.com')
            ->setId('5f3c539b711e4cc306ac2b87')
            ->setFirstName('first')
            ->setLastName('last');
        $actual = $service->get('some@email.com');

        $this->assertEquals($expected, $actual);
    }

    public function testCreate()
    {
        $service = (new User())
            ->setDriver($this->client);
        $actual = $service->create('some@email.com', 'first', 'last');
        $this->assertIsString($actual);
    }

    protected function setUp(): void
    {
        $db = getenv('DB_DATABASE') ?: 'user';
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 27017;
        $user = getenv('DB_USER') ? rawurlencode(getenv('DB_USER')) : null;
        $pwd = getenv('DB_PASSWORD') ? rawurlencode(getenv('DB_PASSWORD')) : null;

        $this->client = (new Client(
            $user && $pwd
                ? "mongodb://{$user}:{$pwd}@{$host}:{$port}/{$db}"
                : "mongodb://{$host}:{$port}/{$db}"
        ))->selectDatabase($db);
    }

    protected function tearDown(): void
    {
        $this->client->dropCollection('user');
    }
}
