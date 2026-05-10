import {t} from '@lingui/macro';
import {Text} from '@mantine/core';

const EU_COUNTRIES = [
    'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
    'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
    'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
];

interface VatNoticeProps {
    accountCountry?: string;
}

export const getVatInfo = (accountCountry?: string) => {
    if (!accountCountry || !EU_COUNTRIES.includes(accountCountry.toUpperCase())) {
        return {
            isEU: false,
            isIreland: false,
            showVatForm: false,
        };
    }

    const isIreland = accountCountry.toUpperCase() === 'IE';

    return {
        isEU: true,
        isIreland,
        showVatForm: !isIreland,
    };
};

export const VatNotice = ({accountCountry}: VatNoticeProps) => {
    const vatInfo = getVatInfo(accountCountry);

    if (!vatInfo.isEU) {
        return null;
    }

    if (vatInfo.isIreland) {
        return (
            <Text size="xs" c="dimmed" mt="sm">
                {t`Irish VAT at 23% will be applied to platform fees (domestic supply).`}
            </Text>
        );
    }

    // Other EU countries
    return (
        <Text size="xs" c="dimmed" mt="sm">
            {t`VAT may be applied to platform fees depending on your VAT registration status. Please complete the VAT information section below.`}
        </Text>
    );
};
