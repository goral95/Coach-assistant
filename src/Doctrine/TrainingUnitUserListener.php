<?php

namespace App\Doctrine;

use App\Entity\TrainingUnit;
use Symfony\Bundle\SecurityBundle\Security;


class TrainingUnitUserListener{

    private $security;

    public function __construct(Security $security){
       $this->security = $security; 
    }

    public function prePersist(TrainingUnit $trainingUnit){
        if($trainingUnit->getUser()){
            return;
        }
        if($this->security->getUser()){
            $trainingUnit->setUser($this->security->getUser());
        }
    }
}