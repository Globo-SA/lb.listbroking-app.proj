<?php

namespace ListBroking\AppBundle\Validation;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class OppositionListRequestValidation
 */
class WebhookRequestHandlerService implements RequestValidatorInterface
{
    private const PHONE_REQUEST_KEY = 'phone';
    private const EMAIL_REQUEST_KEY = 'email';

    /**
     * {@inheritdoc}
     */
    public function validate(Request $request): ValidatedRequestInterface
    {
        $content = json_decode($request->getContent(), true);

        $phone = isset($content[self::PHONE_REQUEST_KEY]) ? (string)$content[self::PHONE_REQUEST_KEY] : null;
        $email = isset($content[self::EMAIL_REQUEST_KEY]) ? $content[self::EMAIL_REQUEST_KEY] : null;

        return new UnsubscribeFromLeadcentreRequest($phone, $email);
    }
}