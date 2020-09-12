<?php

use Auth\Model\Token;
use PHPUnit\Framework\TestCase;

use MongoDB\Database;
use MongoDB\Client;
use MongoDB\BSON\{ObjectId, UTCDateTime};
use Auth\Service\RefreshToken;

class RefreshTokenTest extends TestCase
{
    private ?Database $client;

    public function testGetSuccess()
    {
        $date = new UTCDateTime();
        $this->client->selectCollection('token')
        ->insertMany([
            [
                '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                'email' => 'some@email.com',
                'token' => 'long-and-weird-token-value',
                'created' => $date
            ],
        ]);

        $service = (new RefreshToken())
            ->setDriver($this->client)
            ;

        $expected = (new Token())
            ->setEmail('some@email.com')
            ->setToken('long-and-weird-token-value')
            ->setCreated($date->toDateTime());
        $actual = $service->get('long-and-weird-token-value');

        $this->assertEquals($expected, $actual);
    }

    public function testGetNotFound()
    {
        $date = new UTCDateTime();
        $this->client->selectCollection('token')
        ->insertMany([
            [
                '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                'email' => 'some@email.com',
                'token' => 'long-and-weird-token-value',
                'created' => $date
            ],
        ]);

        $service = (new RefreshToken())
            ->setDriver($this->client)
            ;

        $expected = null;
        $actual = $service->get('this-token-does=not-exist');

        $this->assertEquals($expected, $actual);
    }

    public function testCreateNewToken()
    {
        (new RefreshToken())
            ->setDriver($this->client)
            ->build('some@email.com');

        $actual = count($this->client->selectCollection('token')->find()->toArray());
        $expected = 1;

        $this->assertEquals($expected, $actual);
    }

    public function testUpdateToken()
    {
        $date = new UTCDateTime();
        $this->client->selectCollection('token')
        ->insertMany([
            [
                '_id' => new ObjectId('5f3c539b711e4cc306ac2b87'),
                'email' => 'some@email.com',
                'token' => 'long-and-weird-token-value',
                'created' => $date
            ],
        ]);
        (new RefreshToken())
            ->setDriver($this->client)
            ->build('some@email.com');

        $actual = count($this->client->selectCollection('token')->find()->toArray());
        $expected = 1;

        $this->assertEquals($expected, $actual);
    }

    public function testTokenIsString()
    {
        $actual = (new RefreshToken())
            ->setDriver($this->client)
            ->build('some@email.com');

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
        $this->client->dropCollection('token');
    }
}
