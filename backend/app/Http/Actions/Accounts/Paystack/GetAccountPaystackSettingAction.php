<?php

namespace HiEvents\Http\Actions\Accounts\Paystack;

use HiEvents\DomainObjects\AccountDomainObject;
use HiEvents\DomainObjects\Enums\Role;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Application\Handlers\Account\Payment\Paystack\GetAccountPaystackSettingHandler;
use Illuminate\Http\JsonResponse;

class GetAccountPaystackSettingAction extends BaseAction
{
    public function __construct(
        private readonly GetAccountPaystackSettingHandler $handler,
    ) {
    }

    public function __invoke(int $accountId): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class, Role::ADMIN);

        $setting = $this->handler->handle($this->getAuthenticatedAccountId());

        if (!$setting) {
            return $this->jsonResponse([
                'is_connected' => false,
                'public_key' => null,
                'secret_key_hint' => null,
            ]);
        }

        // Show last 6 chars of the public key as a hint (public keys are safe to show in full)
        return $this->jsonResponse([
            'is_connected' => true,
            'public_key' => $setting->getPublicKey(),
            // Secret key is encrypted — just show a masked placeholder
            'secret_key_hint' => '••••••••••••••••',
        ]);
    }
}
