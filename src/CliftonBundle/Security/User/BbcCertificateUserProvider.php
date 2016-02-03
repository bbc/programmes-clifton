<?php

namespace BBC\CliftonBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class BbcCertificateUserProvider implements UserProviderInterface
{
    /**
     * @param string $username
     * @return BbcCertificateUser
     */
    public function loadUserByUsername($username)
    {
        return new BbcCertificateUser($username, ['ROLE_BBCSTAFF']);
    }

    /**
     * @param UserInterface $user
     * @return BbcCertificateUser
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof BbcCertificateUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'BBC\CliftonBundle\Security\User\BbcCertificateUser';
    }
}
