<?php

namespace BBC\CliftonBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class BbcCertificateUser implements UserInterface, EquatableInterface
{
    /**
     * @var
     */
    private $username;

    /**
     * @var array
     */
    private $roles;

    /**
     * @param mixed $username
     * @param array $roles
     */
    public function __construct($username, array $roles)
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Required by the UserInferface but certificate users don't have a password
     *
     * @return string
     */
    public function getPassword()
    {
        return '';
    }

     /**
     * Required by the UserInferface but certificate users don't have a password
     * and thus they don't need it to be salted
     *
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Required by the UserInferface but as we don't store any user info - only
     * read it from the certificate subject there is nothing to erase
     *
     * @return null
     */
    public function eraseCredentials()
    {
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        return $user instanceof BbcCertificateUser && $user->getUsername() === $this->getUsername();
    }
}
