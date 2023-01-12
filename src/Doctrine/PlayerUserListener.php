<?php

namespace App\Doctrine;

use App\Entity\Player;
use Symfony\Bundle\SecurityBundle\Security;


class PlayerUserListener{

    private $security;

    public function __construct(Security $security){
       $this->security = $security; 
    }

    public function prePersist(Player $player){
        if($player->getUser()){
            return;
        }
        if($this->security->getUser()){
            $player->setUser($this->security->getUser());
        }
    }
}