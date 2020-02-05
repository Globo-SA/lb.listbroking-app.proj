<?php

namespace ListBroking\AppBundle\Validation;

/**
 * Interface ValidatedRequestInterface
 */
interface ValidatedRequestInterface
{
    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return string[]
     */
    public function getErrors(): array ;

}
