<?php

namespace ListBroking\AppBundle\Validation;

/**
 * Class UnsubscribeFromLeadcentreRequest
 */
class UnsubscribeFromLeadcentreRequest implements ValidatedRequestInterface
{

    /**
     * @var string $phone
     */
    private $phone;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string[] $errors
     */
    private $errors;

    /**
     * UnsubscribeFromLeadcentreRequest constructor.
     *
     * @param null|string $phone
     * @param null|string $email
     */
    public function __construct(?string $phone, ?string $email)
    {
        $this->phone  = $phone;
        $this->email  = $email;
        $this->errors = [];
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return is_string($this->phone);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        if (!is_string($this->phone)){
            $this->errors[] = 'Phone is required';
        }

        return $this->errors;
    }
}
