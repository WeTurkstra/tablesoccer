<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class AppUser implements UserInterface
{
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return 'app_user';
    }
}
