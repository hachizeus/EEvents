import {t} from "@lingui/macro";
import {HeadingCard} from "../../../../../common/HeadingCard";
import {useGetAccount} from "../../../../../../queries/useGetAccount.ts";
import {useGetAccountPaystackSetting} from "../../../../../../queries/useGetAccountPaystackSetting.ts";
import {useUpsertAccountPaystackSetting} from "../../../../../../mutations/useUpsertAccountPaystackSetting.ts";
import {useFormErrorResponseHandler} from "../../../../../../hooks/useFormErrorResponseHandler.tsx";
import {LoadingMask} from "../../../../../common/LoadingMask";
import {
    Alert,
    Anchor,
    Badge,
    Button,
    Grid,
    Group,
    PasswordInput,
    Stack,
    Text,
    TextInput,
    ThemeIcon,
    Title,
} from "@mantine/core";
import {useForm} from "@mantine/form";
import {IconAlertCircle, IconCheck, IconExternalLink, IconKey, IconPlugConnected} from '@tabler/icons-react';
import {Card} from "../../../../../common/Card";
import {formatCurrency} from "../../../../../../utilites/currency.ts";
import {getConfig} from "../../../../../../utilites/config.ts";
import {showSuccess} from "../../../../../../utilites/notifications.tsx";
import classes from "../../ManageAccount.module.scss";
import paymentClasses from "./PaymentSettings.module.scss";
import {useState} from "react";

interface FeePlanDisplayProps {
    configuration?: {
        name: string;
        application_fees: {
            percentage: number;
            fixed: number;
            currency: string;
        };
        is_system_default: boolean;
    };
}

const formatPercentage = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value / 100);
};

const FeePlanDisplay = ({configuration}: FeePlanDisplayProps) => {
    if (!configuration) return null;

    return (
        <div className={paymentClasses.paymentInfo}>
            <Title mb={10} order={3}>{t`Platform Fees`}</Title>
            <Text size="sm" c="dimmed" mb="lg">
                {getConfig("VITE_APP_NAME", "E.Events")} {t`charges platform fees to maintain and improve our services. These fees are automatically deducted from each transaction.`}
            </Text>
            <Card variant="lightGray">
                <Title order={4} mb="sm">{configuration.name}</Title>
                <Grid>
                    {configuration.application_fees.percentage > 0 && (
                        <Grid.Col span={{base: 12, sm: 6}}>
                            <Text size="sm">
                                {t`Transaction Fee:`}{' '}
                                <Text span fw={600}>
                                    {formatPercentage(configuration.application_fees.percentage)}
                                </Text>
                            </Text>
                        </Grid.Col>
                    )}
                    {configuration.application_fees.fixed > 0 && (
                        <Grid.Col span={{base: 12, sm: 6}}>
                            <Text size="sm">
                                {t`Fixed Fee:`}{' '}
                                <Text span fw={600}>
                                    {formatCurrency(
                                        configuration.application_fees.fixed,
                                        configuration.application_fees.currency || 'USD'
                                    )}
                                </Text>
                            </Text>
                        </Grid.Col>
                    )}
                </Grid>
            </Card>
        </div>
    );
};

const PaystackConnect = ({accountId}: { accountId: number }) => {
    const [showForm, setShowForm] = useState(false);
    const settingQuery = useGetAccountPaystackSetting(accountId);
    const upsertMutation = useUpsertAccountPaystackSetting(accountId);
    const formErrorHandler = useFormErrorResponseHandler();

    const form = useForm({
        initialValues: {
            public_key: '',
            secret_key: '',
        },
        validate: {
            public_key: (v) =>
                !v ? t`Public key is required` :
                    !v.startsWith('pk_') ? t`Public key must start with pk_` : null,
            secret_key: (v) =>
                !v ? t`Secret key is required` :
                    !v.startsWith('sk_') ? t`Secret key must start with sk_` : null,
        },
    });

    const handleSubmit = form.onSubmit((values) => {
        upsertMutation.mutate(values, {
            onSuccess: () => {
                showSuccess(t`Paystack keys saved successfully`);
                form.reset();
                setShowForm(false);
            },
            onError: (error) => formErrorHandler(form, error),
        });
    });

    const setting = settingQuery.data;
    const isConnected = setting?.is_connected === true;

    return (
        <div className={paymentClasses.paymentInfo}>
            <Group justify="space-between" mb="md" align="flex-start">
                <Title order={3}>{t`Payment Processing`}</Title>
                {isConnected && (
                    <Badge color="green" variant="light" leftSection={<IconCheck size={12}/>}>
                        {t`Connected`}
                    </Badge>
                )}
            </Group>

            {settingQuery.isLoading && <LoadingMask/>}

            {!settingQuery.isLoading && isConnected && !showForm && (
                <>
                    <Group gap="xs" mb="sm">
                        <ThemeIcon size="sm" variant="light" radius="xl" color="green">
                            <IconCheck size={14}/>
                        </ThemeIcon>
                        <Text size="sm" fw={500}>{t`Paystack is connected`}</Text>
                    </Group>

                    <Card variant="lightGray" mb="md">
                        <Stack gap="xs">
                            <Group gap="xs">
                                <IconKey size={14} color="var(--mantine-color-dimmed)"/>
                                <Text size="sm" c="dimmed">{t`Public Key:`}</Text>
                                <Text size="sm" ff="monospace">{setting.public_key}</Text>
                            </Group>
                            <Group gap="xs">
                                <IconKey size={14} color="var(--mantine-color-dimmed)"/>
                                <Text size="sm" c="dimmed">{t`Secret Key:`}</Text>
                                <Text size="sm" ff="monospace">{setting.secret_key_hint}</Text>
                            </Group>
                        </Stack>
                    </Card>

                    <Group gap="sm">
                        <Button
                            variant="light"
                            size="sm"
                            leftSection={<IconPlugConnected size={16}/>}
                            onClick={() => setShowForm(true)}
                        >
                            {t`Update Keys`}
                        </Button>
                        <Anchor
                            href="https://dashboard.paystack.com/#/settings/developer"
                            target="_blank"
                            size="sm"
                        >
                            <Group gap="xs" wrap="nowrap">
                                <Text span>{t`Paystack Dashboard`}</Text>
                                <IconExternalLink size={14}/>
                            </Group>
                        </Anchor>
                    </Group>
                </>
            )}

            {!settingQuery.isLoading && !isConnected && !showForm && (
                <>
                    <Text size="sm" c="dimmed" mb="lg">
                        {t`Connect your Paystack account to start accepting payments for your events. You'll need your API keys from the Paystack dashboard.`}
                    </Text>

                    <Group gap="sm" mb="md">
                        <Button
                            variant="light"
                            size="sm"
                            leftSection={<IconPlugConnected size={16}/>}
                            onClick={() => setShowForm(true)}
                        >
                            {t`Connect Paystack`}
                        </Button>
                        <Anchor
                            href="https://dashboard.paystack.com/#/settings/developer"
                            target="_blank"
                            size="sm"
                        >
                            <Group gap="xs" wrap="nowrap">
                                <Text span>{t`Get API Keys`}</Text>
                                <IconExternalLink size={14}/>
                            </Group>
                        </Anchor>
                    </Group>

                    <Alert color="blue" icon={<IconAlertCircle size={16}/>} variant="light">
                        {t`You can find your API keys in the Paystack dashboard under Settings → API Keys & Webhooks.`}
                    </Alert>
                </>
            )}

            {showForm && (
                <form onSubmit={handleSubmit}>
                    <Stack gap="md">
                        <Alert color="blue" icon={<IconAlertCircle size={16}/>} variant="light">
                            {t`Enter your Paystack API keys. Use test keys (pk_test_... / sk_test_...) for testing, and live keys (pk_live_... / sk_live_...) for production.`}
                        </Alert>

                        <TextInput
                            label={t`Public Key`}
                            placeholder="pk_test_xxxxxxxxxxxxxxxxxxxx"
                            description={t`Starts with pk_test_ or pk_live_`}
                            {...form.getInputProps('public_key')}
                        />

                        <PasswordInput
                            label={t`Secret Key`}
                            placeholder="sk_test_xxxxxxxxxxxxxxxxxxxx"
                            description={t`Starts with sk_test_ or sk_live_ — stored securely encrypted`}
                            {...form.getInputProps('secret_key')}
                        />

                        <Group gap="sm">
                            <Button
                                type="submit"
                                loading={upsertMutation.isPending}
                                leftSection={<IconCheck size={16}/>}
                            >
                                {isConnected ? t`Update Keys` : t`Save & Connect`}
                            </Button>
                            <Button
                                variant="subtle"
                                color="gray"
                                onClick={() => {
                                    form.reset();
                                    setShowForm(false);
                                }}
                            >
                                {t`Cancel`}
                            </Button>
                        </Group>
                    </Stack>
                </form>
            )}
        </div>
    );
};

const PaymentSettings = () => {
    const accountQuery = useGetAccount();
    const account = accountQuery.data;

    return (
        <>
            <HeadingCard
                heading={t`Payment Settings`}
                subHeading={t`Payment processing is handled by the platform. Your events are ready to accept payments.`}
            />

            <Card className={classes.tabContent}>
                <LoadingMask/>
                {account && (
                    <Grid gutter="xl">
                        <Grid.Col span={{base: 12, md: 6}}>
                            <div>
                                <Group gap="xs" mb="sm">
                                    <ThemeIcon size="sm" variant="light" radius="xl" color="green">
                                        <IconCheck size={14}/>
                                    </ThemeIcon>
                                    <Text size="sm" fw={500}>{t`Paystack is connected`}</Text>
                                    <Badge color="green" variant="light" leftSection={<IconCheck size={12}/>}>
                                        {t`Active`}
                                    </Badge>
                                </Group>
                                <Text size="sm" c="dimmed">
                                    {t`Payment processing is managed by the platform. All ticket sales are processed securely via Paystack.`}
                                </Text>
                            </div>
                        </Grid.Col>
                        <Grid.Col span={{base: 12, md: 6}}>
                            {account.configuration && (
                                <FeePlanDisplay configuration={account.configuration}/>
                            )}
                        </Grid.Col>
                    </Grid>
                )}
            </Card>
        </>
    );
};

export default PaymentSettings;
