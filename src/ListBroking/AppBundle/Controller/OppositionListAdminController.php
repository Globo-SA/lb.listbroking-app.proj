<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OppositionListAdminController extends Controller
{

    /**
     * List action
     * @return Response
     * @throws AccessDeniedException If access is not granted
     */
    public function listAction ()
    {
        if ( false === $this->admin->isGranted('LIST') )
        {
            throw new AccessDeniedException();
        }

        $a_service = $this->get('app');

        $import_form = $a_service->generateForm('opposition_list_import', null, null, true);

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()
                             ->createView()
        ;

        // set the theme for the current Admin Form
        $this->get('twig')
             ->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme())
        ;

        return $this->render($this->admin->getTemplate('list'), array(
            'import_form' => $import_form,
            'action'      => 'list',
            'form'        => $formView,
            'datagrid'    => $datagrid,
            'csrf_token'  => $this->getCsrfToken('sonata.batch'),
        ))
            ;
    }
}