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


use ListBroking\ClientBundle\Form\ClientType;
use ListBroking\ClientBundle\Service\ClientService;
use ListBroking\ExtractionBundle\Service\ExtractionService;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UIService implements UIServiceInterface {

    /**
     * @var ClientService
     */
    private $c_service;
    /**
     * @var ExtractionService
     */
    private $e_service;
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
        FormFactory $form_factory,
        CsrfTokenManager $csrf_token_manager

    )
    {
        $this->c_service = $c_service;
        $this->e_service = $e_service;
        $this->form_factory = $form_factory;
        $this->csrf_token_manager = $csrf_token_manager;
    }


    /**
     * Gets a list of entities using the services
     * provided in various bundles
     * @param $type
     * @param $parent
     * @param $parent_id
     * @throws \Exception
     * @internal param $name
     * @return mixed
     */
    public function getEntityList($type, $parent, $parent_id){

        if(empty($type)){
            throw new \Exception("Type can not be empty", 400);
        }

        if(empty($parent)){
            switch($type){
                case 'client':
                    $list = $this->c_service->getClientList();
                    break;
                case 'campaign':
                    $list = $this->c_service->getCampaignList();
                    break;
                case 'extraction':
                    $list = $this->e_service->getExtractionList();
                    break;
                default:
                    throw new \Exception("Invalid List, {$type}", 400);
                    break;
            }
        }else{
            switch($parent){
                case 'client':
                    $parents = $this->c_service->getClientList();
                    break;
                case 'campaign':
                    $parents = $this->c_service->getCampaignList();
                    break;
                case 'extraction':
                    $parents = $this->e_service->getExtractionList();
                    break;
                default:
                    throw new \Exception("Invalid List parent, {$parent}", 400);
                    break;
            }

            $list = array();

            foreach($parents as $parent){
                // If there's a parent an id must be given
                // of nothing will be added to the list
                if(!empty($parent_id) && $parent_id == $parent['id']){
                    foreach($parent[$type . 's'] as $obj){
                        $list[] = $obj;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Generic way to submit multiple types of forms
     * @param $name
     * @param $request Request
     * @throws \Exception
     * @return string
     */
    public function submitForm($name, $request)
    {
        if(empty($name)){
            throw new \Exception("Form name can't be empty", 400);
        }

        $split = explode('_', $name);
        $type = $split[0];
        unset($split);

        $result = null;
        switch($type){
            case 'client':
                $form = $this->form_factory->createNamed($name, new ClientType());
                $form->handleRequest($request);

                if($form->isValid()){
                   $client = $form->getData();
                    $this->c_service->addClient($client);

                    $result = array(
                        "success" => true,
                        "id" => $client->getId(),
                        "msg" => "Form successfully saved!"
                    );
                }else{
                    $result = array(
                        "success" => false,
                        "errors" => $this->getErrorsAsArray($form),
                        "msg" => "Form has errors"
                    );
                }


                break;
            case 'campaign':
                break;
            case 'extraction':
                break;
            default:
                throw new \Exception("Invalid form type, {$type}", 400);
                break;
        }

        // Generates a new csrf token
        $result['new_csrf'] = $this->generateNewCsrfToken($type);

        return $result;
    }

    private function getErrorsAsArray(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if ($err = $this->getErrorsAsArray($child)) {
                $errors[$form->getName()][$child->getName()] = $err;
            }
        }
        return $errors;
    }

    /**
     * Generates a new form view
     * @param $name
     * @param $type
     * @return mixed
     */
    function generateFormView($name, $type)
    {

        return $this->form_factory->createNamed($name, $type)->createView();
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