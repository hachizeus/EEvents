<?php

namespace HiEvents\Http\Actions\Accounts\Paystack;

use HiEvents\DomainObjects\AccountDomainObject;
use HiEvents\DomainObjects\Enums\Role;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\Request\Account\UpsertAccountPaystackSettingRequest;
use HiEvents\Services\Application\Handlers\Account\Payment\Paystack\UpsertAccountPaystackSettingHandler;
use Illuminate\Http\JsonResponse;

class UpsertAccountPaystackSettingAction extends BaseAction
{
    public function __construct(
        private readonly UpsertAccountPaystackSettingHandler $handler,
    ) {
    }

    public function __invoke(int $accountId, UpsertAccountPaystackSettingRequest $request): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class, Role::ADMIN);

        $setting = $this->handler->handle(
            accountId: $this->getAuthenticatedAccountId(),
            publicKey: $request->validated('public_key'),
            secretKey: $request->validated('secret_key'),
        );

        return $this->jsonResponse([
            'is_connected' => true,
            'public_key' => $setting->getPublicKey(),
            'secret_key_hint' => '••••••••' . substr($request->validated('secret_key'), -4),
        ]);
    }
}
