<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AjaxAuthenticationHandlerListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{

    private $router;

    private $session;

    /**
     * @param    RouterInterface $router
     * @param    Session         $session
     */
    public function __construct (RouterInterface $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @param    Request        $request
     * @param    TokenInterface $token
     *
     * @return    Response
     */
    public function onAuthenticationSuccess (Request $request, TokenInterface $token)
    {
        if ( $request->isXmlHttpRequest() )
        {

            $array = array('success' => true); // data to return via JSON
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        if ( $this->session->get('_security.main.target_path') )
        {

            return new RedirectResponse($this->session->get('_security.main.target_path'));
        }

        return new RedirectResponse($this->router->generate('sonata_admin_dashboard'));
    }

    /**
     * @param    Request                 $request
     * @param    AuthenticationException $exception
     *
     * @return    Response
     */
    public function onAuthenticationFailure (Request $request, AuthenticationException $exception)
    {
        // if AJAX login
        if ( $request->isXmlHttpRequest() )
        {

            $array = array('success' => false, 'message' => $exception->getMessage());
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $request->getSession()
                ->set(Security::AUTHENTICATION_ERROR, $exception)
        ;

        return new RedirectResponse($this->router->generate('fos_user_security_login'));
    }
}