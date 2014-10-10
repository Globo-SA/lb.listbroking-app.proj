<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\UIBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ExtractionController {

    private $router;
    private $twig;
    private $session;

    function __construct($router, $twig, Session $session)
    {
        $this->router = $router;
        $this->twig = $twig;
        $this->session = $session;
    }

    public function indexAction(Request $request){

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:index.html.twig',
            array()
        ));
    }
    public function configurationAction(Request $request){

        return new Response($this->twig->render(
            'ListBrokingUIBundle:Extraction:configuration.html.twig',
            array()
        ));
    }
} 