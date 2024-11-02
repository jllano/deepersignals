<?php

namespace App\Model;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class represents a user in the system.
 */
class User implements UserInterface
{   
    public const ROLE_USER = 'ROLE_USER';
    private $username;
    private $roles;

    public function __construct(string $username, array $roles)
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null; // Not needed for token authentication
    }

    public function getSalt(): ?string
    {
        return null; // Not needed for token authentication
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        // Not needed for token authentication
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}