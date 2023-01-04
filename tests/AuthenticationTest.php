<?php
// tests/AuthenticationTest.php

namespace App\Tests;

use App\Entity\User;
use DateTimeInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DateTime;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class AuthenticationTest extends ApiTestCase
{
    use ReloadDatabaseTrait;

    public function testLogin(): void
    {
        $client = self::createClient();
        $container = self::getContainer();

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword(
            $container->get('security.user_password_hasher')->hashPassword($user, '$3CR3T')
        );
        $user->setName("Luki");
        $user->setSurname("Luki2");
        $user->setLicense('a');
        $user->setBirthDate(new DateTime("now"));

        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        // retrieve a token
        $response = $client->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@example.com',
                'password' => '$3CR3T',
            ],
        ]);

        $json = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        // test not authorized
        $client->request('GET', '/api');
        $this->assertResponseStatusCodeSame(401);

        // test authorized
        $client->request('GET', '/api', ['auth_bearer' => $json['token']]);
        $this->assertResponseIsSuccessful();
    }
}