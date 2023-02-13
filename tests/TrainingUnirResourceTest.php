<?php

namespace App\Tests;

use App\Test\CustomApiTestCase;
use DateTime;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class TrainingUnirResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateTraining(){
        $client = self::createClient();
        $data = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        
        // no authenticated user
        $client->request('POST', '/api/trainings', [
            'json' => [
                "topic" => "First Training",
                "duration" => 60,
                "date" => "2023-02-11 17:00",
                "warmPart" => "Warm up",
                "firstMainPart" => "Main 1",
                "secondMainPart" => "Main 2",
                "endPart" => "The End"
            ]
            ]);

        $this->assertResponseStatusCodeSame(401);

        // successfull create with no user input
        $client->request('POST', '/api/trainings', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "topic" => "First Training",
                "duration" => 60,
                "date" => "2023-02-11 17:00",
                "warmPart" => "Warm up",
                "firstMainPart" => "Main 1",
                "secondMainPart" => "Main 2",
                "endPart" => "The End"
            ]
            ]);
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'topic' => 'First Training'
        ]);
        $this->assertJsonContains([
            'coach' => ['@id' => "/api/users/1"]
        ]);

        // successfull create with other user in input
        $client->request('POST', '/api/trainings', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "topic" => "First Training",
                "duration" => 60,
                "date" => "2023-02-11 17:00",
                "warmPart" => "Warm up",
                "firstMainPart" => "Main 1",
                "secondMainPart" => "Main 2",
                "endPart" => "The End",
                "user" => "/api/users/5"
            ]
            ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'topic' => 'First Training'
        ]);
        $this->assertJsonContains([
            'coach' => ['@id' => "/api/users/1"]
        ]);

        // try to create training without required fields
        $client->request('POST', '/api/trainings', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "topic" => "First Training",
                "warmPart" => "Warm up",
                "firstMainPart" => "Main 1",
                "secondMainPart" => "Main 2",
            ]
            ]);

        $this->assertResponseStatusCodeSame(422);

        // create with negative duration
        $client->request('POST', '/api/trainings', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "topic" => "First Training",
                "duration" => -60,
                "date" => "2023-02-11 17:00",
                "warmPart" => "Warm up",
                "firstMainPart" => "Main 1",
                "secondMainPart" => "Main 2",
                "endPart" => "The End"
            ]
            ]);
        $this->assertResponseIsUnprocessable();

        // create with not multiple of 5 duration
        $client->request('POST', '/api/trainings', [
            'auth_bearer' => $data['authTokens']['token'],
            'json' => [
                "topic" => "First Training",
                "duration" => 52,
                "date" => "2023-02-11 17:00",
                "warmPart" => "Warm up",
                "firstMainPart" => "Main 1",
                "secondMainPart" => "Main 2",
                "endPart" => "The End"
            ]
            ]);
        
        $this->assertResponseIsUnprocessable();
    }

    public function testReadTraining(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $this->createTraining($dataUser1['user'], 'First User Training', 60);
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createTraining($user2, 'Second User Training', 90);

        // not auth user
        $client->request('GET', '/api/trainings/1');

        $this->assertResponseStatusCodeSame(401);
        
        // not found training
        $client->request('GET', '/api/trainings/5', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(404);

        // read not own training
        $client->request('GET', '/api/trainings/2', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(403);

        // successfull read own training
        $client->request('GET', '/api/trainings/1', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(200);
        // check that reading training show coach info
        $this->assertJsonContains([
            'coach' => ['@id' => "/api/users/1",
                        'Name' => 'user1',
                        'Surname' => 'user1sur']
        ]);
    }

    public function testDeleteTraining(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $this->createTraining($dataUser1['user'], 'First User Training', 60);
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createTraining($user2, 'Second User Training', 90);

        // not auth user
        $client->request('DELETE', '/api/trainings/1');

        $this->assertResponseStatusCodeSame(401);
        
        // not found training
        $client->request('DELETE', '/api/trainings/5', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(404);

        // delete not own training
        $client->request('DELETE', '/api/trainings/2', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(403);

        // successfull delete own training
        $client->request('DELETE', '/api/trainings/1', [
            'auth_bearer' => $dataUser1['authTokens']['token']
            ]);

        $this->assertResponseStatusCodeSame(204);

    }

    public function testUpdateTraining(){
        $client = self::createClient();
        $dataUser1 = $this->createUserAndLogIn($client, "user1@example.com", "Foofoo123");
        $this->createTraining($dataUser1['user'], 'First User Training', 60);
        $user2 = $this->createUser("user2@example.com", "Foofoo123");
        $this->createTraining($user2, 'Second User Training', 90);

        // not auth user
        $client->request('PUT', '/api/trainings/1', [
            'json' => ['topic' => 'New Topic']
        ]);

        $this->assertResponseStatusCodeSame(401);
        
        // not found training
        $client->request('PUT', '/api/trainings/5', [
            'auth_bearer' => $dataUser1['authTokens']['token'], 
            'json' => ['topic' => 'New Topic']
            ]);

        $this->assertResponseStatusCodeSame(404);

        // update not own training
        $client->request('PUT', '/api/trainings/2', [
            'auth_bearer' => $dataUser1['authTokens']['token'],
            'json' => ['topic' => 'New Topic']
            ]);

        $this->assertResponseStatusCodeSame(403);

        // successfull update own training
        $client->request('PUT', '/api/trainings/1', [
            'auth_bearer' => $dataUser1['authTokens']['token'],
            'json' => ['topic' => 'New Topic']
            ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'topic' => 'New Topic'
        ]);
    }

}