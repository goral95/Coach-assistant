<?php

namespace App\Security\Voter;

use App\Entity\TrainingUnit;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AttendanceVoter extends Voter
{
    public const ATTENDANCE_GET = 'ATTENDANCE_GET';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::ATTENDANCE_GET])
            && $subject instanceof TrainingUnit;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ATTENDANCE_GET:
                if($subject->getUser() === $user){
                    return true;
                }
                return false;
        }
        
        return false;
    }
}
