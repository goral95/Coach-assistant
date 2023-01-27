<?php

namespace App\Test;

use DateTime;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CustomApiTestCase extends ApiTestCase{

    protected function createUser(string $email, string $password): User{
        $user = new User();
        $user->setEmail($email);
        $user->setName(substr($email, 0, strpos($email, '@')));
        $user->setSurname(substr($email, 0, strpos($email, '@')).'sur');
        $user->setLicense('x');
        $user->setBirthDate(new DateTime("now"));
        
        $user->setPassword(
            self::getContainer()->get('security.user_password_hasher')->hashPassword($user, $password)
        );

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function logIn(Client $client, string $email, string $password): ResponseInterface{
        $respone = $client->request('POST', '/api/login', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);
        $this->assertResponseIsSuccessful();
        return $respone;
    }

    protected function createUserAndLogIn(Client $client, string $email, string $password): User{
        $user = $this->createUser($email, $password);
        $this->logIn($client, $email, $password);
        return $user;
    }

    protected function refreshToken(Client $client, string $token): ResponseInterface{
        $respone = $client->request('POST', '/api/token/refresh', [
            'json' => [
                'refresh_token' => $token
            ],
        ]);

        return $respone;
    }

    protected function getEntityManager(){
        return self::getContainer()->get(ManagerRegistry::class)->getManager();
    }
}