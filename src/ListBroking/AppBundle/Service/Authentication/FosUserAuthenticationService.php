<?php

namespace ListBroking\AppBundle\Service\Authentication;

use FOS\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class FosUserAuthenticationService
 */
class FosUserAuthenticationService implements FosUserAuthenticationServiceInterface
{

    /**
     * @var UserManager $userManager
     */
    private $userManager;

    /**
     * FosUserAuthenticationService constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }


    /**
     * Wrapper around the authenticate function to throw an exception when request isn't authenticated
     *
     * @param Request $request
     *
     * @return void
     *
     * @throws AccessDeniedException
     */
    public function checkCredentials(Request $request)
    {
        $username = $request->get('username');
        $token    = $request->get('token');

        if ($username === null || $token === null){
            throw new AccessDeniedException();
        }

        if (! $this->authenticate($username, $token) )
        {
            throw new AccessDeniedException();
        }
    }

    /**
     * Simple API user authentication by username, token
     * and role ROLE_API_USER
     *
     * @param string $username
     * @param string $token
     *
     * @return bool
     */
    private function authenticate (string $username, string $token)
    {
        $user = $this->userManager->findUserBy(['username' => $username, 'token' => $token]);

        return $user && $user->hasRole('ROLE_API_USER');
    }

}