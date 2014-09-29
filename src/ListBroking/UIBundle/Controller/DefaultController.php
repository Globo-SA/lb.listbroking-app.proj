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
        json_encode($category);

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
