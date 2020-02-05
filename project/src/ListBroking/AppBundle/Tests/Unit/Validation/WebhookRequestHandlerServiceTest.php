<?php

namespace ListBroking\AppBundle\Tests\Unit\Validation;

use ListBroking\AppBundle\Validation\UnsubscribeFromLeadcentreRequest;
use ListBroking\AppBundle\Validation\WebhookRequestHandlerService;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WebhookRequestHandlerServiceTest
 */
class WebhookRequestHandlerServiceTest extends TestCase
{
    /**
     * @var WebhookRequestHandlerService $webhookRequestHandlerService
     */
    private $webhookRequestHandlerService;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->webhookRequestHandlerService = new WebhookRequestHandlerService();
    }

    /**
     * Tests if all arguments are being passed
     */
    public function testValidate()
    {
        $request = Request::create('/api/webhook/leadcentre/unsubscribe', 'POST');
        $request->headers->add(['Content-type' => 'application/json']);
        $validatedBadRequest = $this->webhookRequestHandlerService->validate($request);
        Assert::assertFalse($validatedBadRequest->isValid());

        $phone = 919191919;
        $email = 'test@test.com';
        $goodRequest = Request::create(
            '/api/webhook/leadcentre/unsubscribe',
            'POST',
            [],
            [],
            [],
            [],
            sprintf('{"phone": %s, "email": "%s"}', $phone, $email)
        );
        $goodRequest->headers->add(['Content-type' => 'application/json']);

        /** @var UnsubscribeFromLeadcentreRequest $validatedGoodRequest */
        $validatedGoodRequest = $this->webhookRequestHandlerService->validate($goodRequest);
        Assert::assertTrue($validatedGoodRequest->isValid());
        Assert::assertEquals($validatedGoodRequest->getPhone(), $phone);
        Assert::assertEquals($validatedGoodRequest->getEmail(), $email);
        Assert::assertEmpty($validatedGoodRequest->getErrors());
    }
}
