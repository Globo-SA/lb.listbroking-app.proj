<?php
/**
 * Created by PhpStorm.
 * User: rbarros
 * Date: 11/13/17
 * Time: 4:30 PM
 */

namespace ListBroking\AppBundle\Service\Authentication;


use Symfony\Component\HttpFoundation\Request;

interface FosUserAuthenticationServiceInterface
{

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function checkCredentials(Request $request);

}