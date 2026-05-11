<?php

namespace HiEvents\Http\Actions\Common\Webhooks;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\ResponseCodes;
use HiEvents\Services\Application\Handlers\Order\Payment\Paystack\IncomingWebhookHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PaystackIncomingWebhookAction extends BaseAction
{
    public function __construct(
        private readonly IncomingWebhookHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $signature = $request->header('x-paystack-signature', '');
            $payload = $request->getContent();

            $this->handler->handle($payload, $signature);
        } catch (Throwable $exception) {
            logger()?->error('Paystack webhook error: ' . $exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
            ]);
            return $this->noContentResponse(ResponseCodes::HTTP_BAD_REQUEST);
        }

        return $this->noContentResponse();
    }
}
