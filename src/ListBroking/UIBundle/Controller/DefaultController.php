<?php

namespace ListBroking\UIBundle\Controller;

use Doctrine\ORM\AbstractQuery;
use ESO\Doctrine\ORM\QueryBuilder;
use ListBroking\ClientBundle\Entity\Campaign;
use ListBroking\ClientBundle\Entity\Client;
use ListBroking\ClientBundle\Service\ClientService;
use ListBroking\CoreBundle\Entity\Category;
use ListBroking\CoreBundle\Entity\Country;
use ListBroking\CoreBundle\Entity\SubCategory;
use ListBroking\CoreBundle\Exception\EntityValidationException;
use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\ExtractionBundle\Entity\ExtractionTemplate;
use ListBroking\LockBundle\Engine\LockEngine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;

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
        $e_service = $this->get('listbroking.extraction.service');

        $lock_filters = array(
            1 => array( //NoLocksFilter
                'filters' => array(
                    array(
                        'interval' =>  new \DateTime()
                    )
                )
            ),
            2 => array( //ReservedLockType
                'filters' => array(
                    array(
                        'interval' =>  new \DateTime('- 1 week')
                    )
                )
            ),
            3 => array( //ClientLockType
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
            4 => array( //CampaignFilter
                'filters' => array(
                    array(
                        'client_id' => 4,
                        'campaign_id' => 2,
                        'interval' => new \DateTime('- 2 month')
                    )
                )
            ),
            5 => array( //Category
                'filters' =>  array(
                    array(
                        'category_id' => 2,
                        'interval' => new \DateTime('- 9 month')
                    ),
                )
            ),
            6 => array( // SubCategoryLockType
                'filters' => array(
                    array(
                        'category_id' => 2,
                        'sub_category_id' => 2,
                        'interval' => new \DateTime('- 8 month')
                    )
                )
            ),
        );

        $contact_filters = array(
            1 => array( //BaseContactFilter
                'filters' =>
                    array(
                        array(
                        'field' => 'gender',
                        'opt' => 'equal',
                        'value' => array(53)
                        ),
                        array(
                        'field' => 'postalcode1',
                        'opt' => 'equal',
                        'value' => array(4100, 2100)
                        ),
                        array(
                        'field' => 'id',
                        'opt' => 'not_equal',
                        'value' => array(76802)
                        ),
                        array(
                            'field' => 'country',
                            'opt' => 'equal',
                            'value' => array(74,75,76)
                        ),
                        array(
                            'field' => 'birthdate',
                            'opt' => 'between',
                            'value' => array('1954-01-02', '1996-06-28')
                        )
                    )
            ),
        );

        $lead_filters = array(
            1 => array( //BaseLeadFilter
                'filters' =>
                    array(
                        array(
                        'field' => 'id',
                        'opt' => 'not_equal',
                        'value' => array(300443)
                        ),
                    )
            ),
        );

        $extraction['filters'] = array(
            'lock_filters' => $lock_filters,
            'contact_filters' => $contact_filters,
            'lead_filters' => $lead_filters
        );

        $e_service->setExtractionFilters(5,$extraction['filters']);

        return $this->render('ListBrokingUIBundle:Default:samuel.html.twig', array());
    }
}
