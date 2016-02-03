<?php

namespace BBC\CliftonBundle\Security\Http;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\X509AuthenticationListener;
use Symfony\Component\HttpFoundation\Request;

class BbcX509AuthenticationListener extends X509AuthenticationListener
{
    /**
     * {@inheritdoc}
     */
    protected function getPreAuthenticatedData(Request $request)
    {
        $userKey = 'HTTP_SSLCLIENTCERTSUBJECT';

        if (!$request->server->has($userKey)) {
            throw new BadCredentialsException(sprintf('SSL credentials not found, expected %s to be found in server config', $userKey));
        }

        $userValue = $request->server->get($userKey);
        $matches = array();
        $result = preg_match('/^Email=([^,]+),/', $userValue, $matches);

        if (!array_key_exists(1, $matches)) {
            throw new BadCredentialsException(sprintf('Malformed SSL credentials, expected to find Email= in "%s"', $userValue));
        }

        // We want to treat desktop and developer certificates with the same
        // email, but with dodgy capitalisation as the same user
        $user = strtolower($matches[1]);
        return array($user, '');
    }
}
