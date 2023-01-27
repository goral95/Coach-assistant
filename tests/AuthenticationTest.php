<?php
// tests/AuthenticationTest.php

namespace App\Tests;

use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class AuthenticationTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testLogin(): void
    {
        $client = self::createClient();

        $user = $this->createUser('user@gmail.com','Foofoo123');
        
        // retrieve a token
        $response = $this->logIn($client, 'user@gmail.com','Foofoo123');

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        // test not authorized
        $client->request('GET', '/api/users');
        $this->assertResponseStatusCodeSame(401);

        // test authorized
        $client->request('GET', '/api/users', ['auth_bearer' => $json['token']]);
        $this->assertResponseIsSuccessful();
    }

    public function testRefreshToken(): void
    {
        $client = self::createClient();

        $user = $this->createUser('user@gmail.com','Foofoo123');
        
        // retrieve a token
        $response = $this->logIn($client, 'user@gmail.com','Foofoo123');

        $json = $response->toArray();
        $this->assertArrayHasKey('refresh_token', $json);

        $responeRefresh = $this->refreshToken($client, $json['refresh_token']);
        $jsonRefresh = $responeRefresh->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $jsonRefresh);

        $client->request('GET', '/api/users', ['auth_bearer' => $jsonRefresh['token']]);
        $this->assertResponseIsSuccessful();  
    }

    public function testLogout(): void
    {
        $client = self::createClient();

        $user = $this->createUser('user@gmail.com','Foofoo123');
        
        // retrieve a token
        $response = $this->logIn($client, 'user@gmail.com','Foofoo123');

        $json = $response->toArray();

        $client->request('POST', '/api/logout', [
            'json' => [
                'refresh_token' => $json['refresh_token']
            ],
        ]);
        $this->assertResponseIsSuccessful();
        

        $this->refreshToken($client, $json['refresh_token']);
        $this->assertResponseStatusCodeSame(401);  
    }


}