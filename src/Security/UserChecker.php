<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user): void
    {
        // nothing to do here
        if (!$user instanceof User) {
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // nothing to do here
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException("Veuillez confirmer l' éxistence de votre adresse email. Demandez un nouvel email de confirmation en cliquant <a href='/nouveau_lien/{$user->getId()}' class='link-emerald-500'>ici</a>.");
        }
    }
}
