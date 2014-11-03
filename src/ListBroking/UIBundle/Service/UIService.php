<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\UIBundle\Service;


use ListBroking\ClientBundle\Form\CampaignType;
use ListBroking\ClientBundle\Form\ClientType;
use ListBroking\ClientBundle\Service\ClientService;
use ListBroking\ExtractionBundle\Form\ExtractionType;
use ListBroking\ExtractionBundle\Service\ExtractionService;
use ListBroking\LeadBundle\Service\LeadService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UIService implements UIServiceInterface
{

    /**
     * @var ClientService
     */
    private $c_service;
    /**
     * @var ExtractionService
     */
    private $e_service;

    /**
     * @var LeadService
     */
    private $l_service;
    /**
     * @var FormFactory
     */
    private $form_factory;
    /**
     * @var CsrfTokenManager
     */
    private $csrf_token_manager;

    function __construct(
        ClientService $c_service,
        ExtractionService $e_service,
        LeadService $l_service,
        FormFactory $form_factory,
        CsrfTokenManagerInterface $csrf_token_manager
    )
    {
        $this->c_service = $c_service;
        $this->e_service = $e_service;
        $this->l_service = $l_service;
        $this->form_factory = $form_factory;
        $this->csrf_token_manager = $csrf_token_manager;
    }

    /**
     * Group leads by lock and count them
     * @return array
     */
    public function countByLock()
    {

        return $this->l_service->countByLock();
    }

    /**
     * Gets a list of entities using the services
     * provided in various bundles
     * @param $type
     * @param $parent_type
     * @param $parent_id
     * @throws \Exception
     * @return mixed
     */
    public function getEntityList($type, $parent_type, $parent_id)
    {
        if (empty($type))
        {
            throw new \Exception("Type can not be empty", 400);
        }

        $list = array();
        switch ($type)
        {
            case 'client':
                $list = $this->c_service->getClientList();
                break;
            case 'campaign':
                $tmp_list = $this->c_service->getCampaignList();
                foreach ($tmp_list as $key => $obj)
                {
                    if(!empty($parent_id) && $obj['client_id'] == $parent_id){
                        $client = $this->c_service->getClient($obj['client_id']);
                        $obj['name'] = $client['name'] . ' - ' . $obj['name'];

                        $list[] = $obj;
                    }
                }

                break;
            case 'extraction':
                $tmp_list = $this->e_service->getExtractionList();
                foreach ($tmp_list as $key => $obj)
                {
                    if(!empty($parent_id) && $obj['campaign_id'] == $parent_id){
                        $list[] = $obj;
                    }
                }
                break;
            case 'extraction_template':
                $list = $this->e_service->getExtractionTemplateList();
                break;
            default:
                throw new \Exception("Invalid List, {$type}", 400);
                break;
        }

        return $list;
    }

    /**
     * Generic way to submit multiple types of forms
     * @param $form_name
     * @param $request Request
     * @throws \Exception
     * @return string
     */
    public function submitForm($form_name, $request)
    {
        if (empty($form_name))
        {
            throw new \Exception("Form name can't be empty", 400);
        }

        $split = explode('_', $form_name);
        $type = $split[0];
        unset($split);

        $result = null;
        switch ($type)
        {
            case 'client':
                $form = $this->form_factory->createNamed($form_name, new ClientType());
                $form->handleRequest($request);

                if ($form->isValid())
                {
                    $client = $form->getData();
                    if ($client->getId())
                    {
                        $this->c_service->updateClient($client);
                    } else
                    {

                        $this->c_service->addClient($client);
                    }
                    $result = array(
                        "success" => true,
                        "id" => $client->getId(),
                        "msg" => "Form successfully saved!"
                    );
                } else
                {
                    $result = array(
                        "success" => false,
                        "errors" => $this->getErrorsAsArray($form),
                        "msg" => "Form has errors"
                    );
                }
                break;
            case 'campaign':
                $form = $this->form_factory->createNamed($form_name, new CampaignType());
                $form->handleRequest($request);

                if ($form->isValid())
                {
                    $campaign = $form->getData();

                    // Convert client id to Object
                    $client = $this->c_service->getClient($campaign->getClient(), true);
                    $campaign->setClient($client);

                    $this->c_service->addCampaign($campaign);

                    $result = array(
                        "success" => true,
                        "id" => $campaign->getId(),
                        "msg" => "Form successfully saved!"
                    );
                } else
                {
                    $result = array(
                        "success" => false,
                        "errors" => $this->getErrorsAsArray($form),
                        "msg" => "Form has errors"
                    );
                }
                break;
            case 'extraction':
                $form = $this->form_factory->createNamed($form_name, new ExtractionType());
                $form->handleRequest($request);

                if ($form->isValid())
                {
                    $extraction = $form->getData();

                    // Convert campaign id to Object
                    $campaign = $this->c_service->getCampaign($extraction->getCampaign(), true);
                    $extraction->setCampaign($campaign);

                    $this->e_service->addExtraction($extraction);

                    $result = array(
                        "success" => true,
                        "id" => $campaign->getId(),
                        "msg" => "Form successfully saved!"
                    );
                } else
                {
                    $result = array(
                        "success" => false,
                        "errors" => $this->getErrorsAsArray($form),
                        "msg" => "Form has errors"
                    );
                }
                break;
            default:
                throw new \Exception("Invalid form type, {$type}", 400);
                break;
        }

        // Generates a new csrf token
        $result['new_csrf'] = $this->generateNewCsrfToken($form_name);

        return $result;
    }

    private function getErrorsAsArray(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error)
        {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child)
        {
            if ($err = $this->getErrorsAsArray($child))
            {
                $errors[$form->getName()][$child->getName()] = $err;
            }
        }
        return $errors;
    }

    /**
     * Generates a new form view
     * @param $type
     * @param bool $view
     * @param null $data
     * @param $action
     * @return FormBuilderInterface|Form
     */
    function generateForm($type, $action = null, $data = null, $view = false)
    {
        $form = $this->form_factory->createBuilder($type, $data);
        if($action){
            $form->setAction($action);
        }

        if ($view)
        {
            return $form->getForm()->createView();
        }
        return $form->getForm();
    }

    /**
     * Generates a new CSRF token
     * @param $intention
     * @return mixed
     */
    function generateNewCsrfToken($intention)
    {
        return $this->csrf_token_manager->refreshToken($intention)->getValue();
    }
}