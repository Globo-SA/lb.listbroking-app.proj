<?php

namespace ListBroking\UIBundle\Controller;

use ListBroking\ClientBundle\Entity\Campaign;
use ListBroking\ClientBundle\Entity\Client;
use ListBroking\ClientBundle\Service\ClientService;
use ListBroking\CoreBundle\Entity\Category;
use ListBroking\CoreBundle\Entity\Country;
use ListBroking\CoreBundle\Entity\SubCategory;
use ListBroking\CoreBundle\Exception\EntityValidationException;
use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\LeadBundle\Entity\Contact;
use ListBroking\LeadBundle\Entity\Lead;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request){
        return $this->render('ListBrokingUIBundle:Default:index.html.twig', array());
    }

    public function tentugalAction(Request $request)
    {
        $core_service = $this->get('listbroking.core.service');
        $extraction_service = $this->get('listbroking.extraction.service');
        $lead_service = $this->get('listbroking.lead.service');
        $campaign_service = $this->get('listbroking.client.service');
        $country = $core_service->getCountry(7, true);
        $category = $core_service->getCategory(1, true);
        $campaign = $campaign_service->getCampaign(1, true);
        json_encode($category);

        $lead = new Lead();
        $lead->setCountry($country);
        $lead->setInOpposition(0);
        $lead->setIsMobile(0);
        $lead->setPhone('913226556');
        $lead_service->addLead($lead);

        $extraction = new Extraction();
        $extraction->setIsActive(1);
        $extraction->setPayout(1);
        $extraction->setQuantity(10);
        $extraction->setFilters('jsons');
        $extraction->setCampaign($campaign);
        $extraction->addLead($lead);
        $extraction_service->addExtraction($extraction);


        $form = $this->createForm('country_form', $country);
        if ($request->getMethod() == 'POST'){
            $form->handleRequest($request);

            if ($form->isValid()){
                $core_service->updateCountry($country);
            }
        }

//        try {
//            $country = new Country();
//            $country->setIsActive(0);
//            $country->setName('França');
//            $country->setIsoCode('FR');
//            $core_service->addCountry($country);
//        } catch (EntityValidationException $e) {
//            echo $e;
//        }

//        var_dump($core_service->getCountryList(false));
        return $this->render('ListBrokingUIBundle:Default:tentugal.html.twig', array());
    }


    public function samuelAction(Request $request)
    {

        $lock_service = $this->get('listbroking.lock.service');
        $locks = $lock_service->getLockList();
        $lock_filters = array(
            array( //ReservedLockType
                'type' => 1,
            ),
            array( //CategoryLockType
                'type' => 4,
                'category_id' => 2,
                'expiration_date' => 1411689600
            ),
            array( // SubCategoryLockType
                'type' => 5,
                'category_id' => 3,
                'sub_category_id' => 20,
                'expiration_date' => 1420070400
            ),
        );

        $engine = $lock_service->startEngine();

        $engine->compileFilters($lock_filters);

        return $this->render('ListBrokingUIBundle:Default:samuel.html.twig', array());
    }
}
