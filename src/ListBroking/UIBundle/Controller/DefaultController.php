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

        $category = $core_service->getCategory(1);


        $sub_category = new SubCategory();
        $sub_category->setIsActive(1);
        $sub_category->setName('Grandes');

        $category->addSubCategory($sub_category);

        $core_service->updateCategory($category);

//        $form = $this->createForm('country_form', $country);
//        if ($request->getMethod() == 'POST'){
//            $form->handleRequest($request);
//
//            if ($form->isValid()){
//                $core_service->updateCountry($country);
//            }
//        }

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
        /** @var ClientService $client_service */
        $client_service = $this->get('listbroking.client.service');
        $lock_service = $this->get('listbroking.lock.service');

        //$core_service = $this->get('listbroking.core.service');


        //$client = $client_service->getClient(4);

        $category = $client_service->getClient(4);

//        $client = new Client();
//        $client->setIsActive(1);
//        $client->setName("Adclick");
//        $client->setAccountName("Samuel Castro");
//        $client->setEmailAddress("samuel.castro@adclick.pt");
//        $client->setPhone("+351 914i384503");
//
//        $campaign = new Campaign();
//        $campaign->setIsActive(1);
//        $campaign->setName("Metlife Global");
//        $campaign->setDescription("A great and cool campaign form awesome stuff");
//
//        $client->addCampaign($campaign);
//
//        $client_service->addClient($client);

        return $this->render('ListBrokingUIBundle:Default:samuel.html.twig', array());
    }
}
