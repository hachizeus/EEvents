<?php

namespace HiEvents\DomainObjects;

use HiEvents\DomainObjects\DTO\AccountApplicationFeeDTO;

class AccountDomainObject extends Generated\AccountDomainObjectAbstract
{
    private ?AccountConfigurationDomainObject $configuration = null;

    private ?AccountVatSettingDomainObject $accountVatSetting = null;

    private ?AccountMessagingTierDomainObject $messagingTier = null;

    public function getApplicationFee(): AccountApplicationFeeDTO
    {
        /** @var AccountConfigurationDomainObject $applicationFee */
        $applicationFee = $this->getConfiguration();

        return new AccountApplicationFeeDTO(
            $applicationFee->getPercentageApplicationFee(),
            $applicationFee->getFixedApplicationFee()
        );
    }

    public function getConfiguration(): ?AccountConfigurationDomainObject
    {
        return $this->configuration;
    }

    public function setConfiguration(AccountConfigurationDomainObject $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getAccountVatSetting(): ?AccountVatSettingDomainObject
    {
        return $this->accountVatSetting;
    }

    public function setAccountVatSetting(AccountVatSettingDomainObject $accountVatSetting): void
    {
        $this->accountVatSetting = $accountVatSetting;
    }

    public function getMessagingTier(): ?AccountMessagingTierDomainObject
    {
        return $this->messagingTier;
    }

    public function setMessagingTier(AccountMessagingTierDomainObject $messagingTier): void
    {
        $this->messagingTier = $messagingTier;
    }
}
