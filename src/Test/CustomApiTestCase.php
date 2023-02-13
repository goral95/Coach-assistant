<?php

namespace App\Test;

use DateTime;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Symfony\Bundle\Test\Client;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Player;
use App\Entity\TrainingUnit;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CustomApiTestCase extends ApiTestCase{

    protected function createTraining(User $user, string $topic, int $duration): TrainingUnit{
        
        $training = new TrainingUnit();
        $training->setTopic($topic);
        $training->setDuration($duration);
        $training->setDate(new DateTime("now"));
        $training->setWarmPart($topic.' Warm');
        $training->setFirstMainPart($topic.' First Main');
        $training->setSecondMainPart($topic.' Second Main');
        $training->setEndPart($topic.' End');
        $training->setUser($user);

        $em = $this->getEntityManager();
        $em->persist($training);
        $em->flush();

        return $training;
    }

    protected function createPlayer(User $user, string $name, string $surname): Player{
        
        $player = new Player();
        $player->setName($name);
        $player->setSurname($surname);
        $player->setBirthDate(new DateTime("now"));
        $player->setFoot('x');
        $player->setCity('x');
        $player->setUser($user);

        $em = $this->getEntityManager();
        $em->persist($player);
        $em->flush();

        return $player;
    }

    protected function createPlayerWithDate(User $user, string $name, string $surname, Datetime $birthDate): Player{
        
        $player = new Player();
        $player->setName($name);
        $player->setSurname($surname);
        $player->setBirthDate($birthDate);
        $player->setFoot('x');
        $player->setCity('x');
        $player->setUser($user);

        $em = $this->getEntityManager();
        $em->persist($player);
        $em->flush();

        return $player;
    }

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

    protected function createUserAndLogIn(Client $client, string $email, string $password): Array{
        $user = $this->createUser($email, $password);
        $authTokens = $this->logIn($client, $email, $password);
        $response = ["user" => $user, "authTokens" => $authTokens->toArray()];
        return $response;
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