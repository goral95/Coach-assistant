<?php

namespace App\Tests;

use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testPasswordValidation():void
    {   
        $client = self::createClient();

        // Password must have minimum 6 characters
        $client->request('POST', '/api/users', [
            'json' => [
                "email" => "user@example.com",
                "password" => "foo",
                "Name" => "x",
                "Surname" => "y",
                "License" => "a",
                "BirthDate" => "2023-01-27"
            ]
            ]);
        $this->assertResponseIsUnprocessable();

        // Password must have maximum 20 characters
        $client->request('POST', '/api/users', [
            'json' => [
                "email" => "user@example.com",
                "password" => "foofoofoofoofoofoofoofoofoofoofoo",
                "Name" => "x",
                "Surname" => "y",
                "License" => "a",
                "BirthDate" => "2023-01-27"
            ]
            ]);
        $this->assertResponseIsUnprocessable();

        // Password must have digit, big letter and small letter
        $client->request('POST', '/api/users', [
            'json' => [
                "email" => "user@example.com",
                "password" => "foofoofoo",
                "Name" => "x",
                "Surname" => "y",
                "License" => "a",
                "BirthDate" => "2023-01-27"
            ]
            ]);
        $this->assertResponseIsUnprocessable();

        // Password must have digit, big letter and small letter
        $client->request('POST', '/api/users', [
            'json' => [
                "email" => "user@example.com",
                "password" => "Foofoofoo",
                "Name" => "x",
                "Surname" => "y",
                "License" => "a",
                "BirthDate" => "2023-01-27"
            ]
            ]);
        $this->assertResponseIsUnprocessable();

        // Password must have digit, big letter and small letter
        $client->request('POST', '/api/users', [
            'json' => [
                "email" => "user@example.com",
                "password" => "foofoofoo1",
                "Name" => "x",
                "Surname" => "y",
                "License" => "a",
                "BirthDate" => "2023-01-27"
            ]
            ]);
        $this->assertResponseIsUnprocessable();

        // Password is correct
        $client->request('POST', '/api/users', [
            'json' => [
                "email" => "user@example.com",
                "password" => "Foofoofoo1",
                "Name" => "x",
                "Surname" => "y",
                "License" => "a",
                "BirthDate" => "2023-01-27"
            ]
            ]);
        $this->assertResponseIsSuccessful();
        
      }

      public function testUserUpdate(){
        $client = self::createClient();

        $user1 = $this->createUser("user1@example.com", "Foofoo123");
        $user2 = $this->createUser("user2@example.com", "Foofoo123");

        // Must be logged in to update user
        $client->request('PUT', '/api/users/'.$user1->getId(), [
            'json' => ['name' => 'newname']
        ]);
        $this->assertResponseStatusCodeSame(401);

        $response = $this->logIn($client, "user1@example.com", "Foofoo123");
        $json = $response->toArray();

        // Can't update other user fields
        $client->request('PUT', '/api/users/'.$user2->getId(), 
        ['auth_bearer' => $json['token'],
        'json' => ['name' => 'newname']]
        );
        $this->assertResponseStatusCodeSame(403);

        
        // Success update
        $client->request('PUT', '/api/users/'.$user1->getId(), 
        ['auth_bearer' => $json['token'],
        'json' => ['Name' => 'newname']]
        );
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'Name' => 'newname'
        ]);
      }
}