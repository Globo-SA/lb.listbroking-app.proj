<?php
/**
 * Created by PhpStorm.
 * User: rbarros
 * Date: 11/8/17
 * Time: 1:30 PM
 */

namespace ListBroking\AppBundle\Validation;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RequestValidatorInterface
 */
interface RequestValidatorInterface
{

    /**
     * Validates a request
     *
     * @param Request $request
     *
     * @return ValidatedRequestInterface
     */
    public function validate(Request $request): ValidatedRequestInterface;

}