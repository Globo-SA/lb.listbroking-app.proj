<?php

namespace ListBroking\APIBundle\Controller;

use ListBroking\APIBundle\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;

class DefaultController extends Controller
{
    protected $api_service;

    public function testUploadAction(){
        $this->api_service  = $this->get('listbroking.api.service');
        $filename = $this->get('kernel')->getRootDir() . "/../web/uploads/teste.xls";
        $owner = 'adclick';
        $source = 4;
        $sub_category = 'mystic';
        $country = 'pt';
        $response = $this->api_service->setLeadsByCSV($filename, $owner, $source, $sub_category, $country);

        return $response;
    }
}
