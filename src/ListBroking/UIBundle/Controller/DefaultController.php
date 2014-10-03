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
use ListBroking\LockBundle\Engine\LockEngine;
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

        $lock_filters = array(
            array( //NoLocksFilter
                'type' => 1,
                'filters' => array(
                    array(
                        'interval' =>  new \DateTime()
                    )
                )
            ),
            array( //ReservedLockType
                'type' => 2,
                'filters' => array(
                    array(
                        'interval' =>  new \DateTime('- 1 week')
                    )
                )
            ),
            array( //ClientLockType
                'type' => 3,
                'filters' =>  array(
                    array(
                        'client_id' => 2,
                        'interval' => new \DateTime('- 4 month')
                    ),
                    array(
                        'client_id' => 4,
                        'interval' => new \DateTime('- 5 week')
                    )
                )
            ),
            array( //CampaignFilter
                'type' => 4,
                'filters' => array(
                    array(
                        'client_id' => 4,
                        'campaign_id' => 2,
                        'interval' => new \DateTime('- 2 month')
                    )
                )
            ),
            array( //Category
                'type' => 5,
                'filters' =>  array(
                    array(
                        'category_id' => 2,
                        'interval' => new \DateTime('- 9 month')
                    ),
                )
            ),
            array( // SubCategoryLockType
                'type' => 6,
                'filters' => array(
                    array(
                        'category_id' => 2,
                        'sub_category_id' => 2,
                        'interval' => new \DateTime('- 8 month')
                    )
                )
            ),
        );

        $engine = $lock_service->startEngine();
        $qb = $engine->compileFilters($lock_filters);

        ladybug_dump($qb->getQuery()->getSQL());
        ladybug_dump_die($qb->getQuery()->getArrayResult());


        return $this->render('ListBrokingUIBundle:Default:samuel.html.twig', array());
    }
}
