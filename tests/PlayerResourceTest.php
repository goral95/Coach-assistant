<?php

namespace App\Tests;

use ApiPlatform\Api\QueryParameterValidator\Validator\Length;
use App\Test\CustomApiTestCase;
use DateTime;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class PlayerResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreatePlayer(){
        $client = self::createClient();
        $data = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        
        // no authenticated user
        $client->request('POST', '/api/players', [
            'json' => [
                "name" => "john",
                "surname" => "kowalski",
                "birthDate" => "2000-01-01",
                "foot" => "right",
                "position" => "attack",
                "city" => "Gliwice"
            ]
            ]);

        $this->assertResponseStatusCodeSame(401);

        // successfull create with no user input
        $response = $client->request('POST', '/api/players', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "name" => "john",
                "surname" => "kowalski",
                "birthDate" => "2000-01-01",
                "foot" => "right",
                "position" => "attack",
                "city" => "Gliwice"
            ]
            ]);
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'name' => 'john'
        ]);
        $this->assertJsonContains([
            'coach' => ['@id' => "/api/users/1"]
        ]);

        // successfull create with other user in input
        $client->request('POST', '/api/players', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "name" => "john",
                "surname" => "kowalski",
                "birthDate" => "2000-01-01",
                "foot" => "right",
                "position" => "attack",
                "city" => "Gliwice",
                "user" => "/api/users/5"
            ]
            ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'name' => 'john'
        ]);
        $this->assertJsonContains([
            'coach' => ['@id' => "/api/users/1"]
        ]);

        // try to create player without required fields
        $client->request('POST', '/api/players', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "birthDate" => "2000-01-01",
                "foot" => "right",
                "position" => "attack",
                "city" => "Gliwice",
            ]
            ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testReadPlayer(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $this->createPlayer($dataUser1['user'], 'jan', 'kowalski');
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createPlayer($user2, 'piotr', 'nowak');

        // not auth user
        $client->request('GET', '/api/players/1');

        $this->assertResponseStatusCodeSame(401);
        
        // not found player
        $client->request('GET', '/api/players/5', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(404);

        // read not own player
        $client->request('GET', '/api/players/2', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(403);

        // successfull read own player
        $client->request('GET', '/api/players/1', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(200);
        // check that reading player show coach info
        $this->assertJsonContains([
            'coach' => ['@id' => "/api/users/1",
                        'Name' => 'user1',
                        'Surname' => 'user1sur']
        ]);
    }

    public function testDeletePlayer(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $this->createPlayer($dataUser1['user'], 'jan', 'kowalski');
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createPlayer($user2, 'piotr', 'nowak');

        // not auth user
        $client->request('DELETE', '/api/players/1');

        $this->assertResponseStatusCodeSame(401);
        
        // not found player
        $client->request('DELETE', '/api/players/5', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(404);

        // delete not own player
        $client->request('DELETE', '/api/players/2', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(403);

        // successfull delete own player
        $client->request('DELETE', '/api/players/1', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(204);

    }

    public function testUpdatePlayer(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $this->createPlayer($dataUser1['user'], 'jan', 'kowalski');
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createPlayer($user2, 'piotr', 'nowak');

        // not auth user
        $client->request('PUT', '/api/players/1', [
            'json' => ['name' => 'newname']
        ]);

        $this->assertResponseStatusCodeSame(401);
        
        // not found player
        $client->request('PUT', '/api/players/5', [
            'auth_bearer' => $dataUser1['authTokens']['token'], 
            'json' => ['name' => 'newname']
            ]);

        $this->assertResponseStatusCodeSame(404);

        // update not own player
        $client->request('PUT', '/api/players/2', [
            'auth_bearer' => $dataUser1['authTokens']['token'],
            'json' => ['name' => 'newname']
            ]);

        $this->assertResponseStatusCodeSame(403);

        // successfull delete own player
        $client->request('PUT', '/api/players/1', [
            'auth_bearer' => $dataUser1['authTokens']['token'],
            'json' => ['name' => 'newname']
            ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'name' => 'newname'
        ]);
    }

    public function testReadAllUserPlayers(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createPlayerWithDate($dataUser1['user'], 'jan', 'kowalski', DateTime::createFromFormat('Y-m-d', '1999-08-20'));
        $this->createPlayerWithDate($dataUser1['user'], 'dawid', 'podsiadlo', DateTime::createFromFormat('Y-m-d', '1999-08-20'));
        $this->createPlayer($user2, 'piotr', 'nowak');
        $this->createPlayerWithDate($dataUser1['user'], 'jan', 'bond', DateTime::createFromFormat('Y-m-d', '2010-08-20'));

        // not auth user
        $client->request('GET', '/api/users/1/players');
        $this->assertResponseStatusCodeSame(401);

        // get other user players
        $client->request('GET', '/api/users/2/players', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);
        $this->assertResponseStatusCodeSame(403);

        // success default
        $response = $client->request('GET', '/api/users/1/players', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);
        $responseArray = $response->toArray();
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(3, $responseArray['hydra:totalItems']);
        $this->assertContains('kowalski', $responseArray['hydra:member'][0]);
        $this->assertContains('podsiadlo', $responseArray['hydra:member'][1]);
        $this->assertContains('bond', $responseArray['hydra:member'][2]);

        // success order by name asc
        $response = $client->request('GET', '/api/users/1/players/name-asc', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);
        $responseArray = $response->toArray();
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(3, $responseArray['hydra:totalItems']);
        $this->assertContains('podsiadlo', $responseArray['hydra:member'][0]);
        $this->assertContains('bond', $responseArray['hydra:member'][1]);
        $this->assertContains('kowalski', $responseArray['hydra:member'][2]);

        // success order by birthDate asc
        $response = $client->request('GET', '/api/users/1/players/birth-date-asc', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);
        $responseArray = $response->toArray();
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(3, $responseArray['hydra:totalItems']);
        $this->assertContains('podsiadlo', $responseArray['hydra:member'][0]);
        $this->assertContains('kowalski', $responseArray['hydra:member'][1]);
        $this->assertContains('bond', $responseArray['hydra:member'][2]);
    }


}