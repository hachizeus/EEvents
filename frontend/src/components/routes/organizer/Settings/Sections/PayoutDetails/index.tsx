import {t} from "@lingui/macro";
import {Button, Select, Stack, TextInput, Alert} from "@mantine/core";
import {useForm} from "@mantine/form";
import {useParams} from "react-router";
import {IconInfoCircle} from "@tabler/icons-react";
import {HeadingCard} from "../../../../../common/HeadingCard";
import {Card} from "../../../../../common/Card";
import {showSuccess} from "../../../../../../utilites/notifications.tsx";
import {useGetOrganizerSettings} from "../../../../../../queries/useGetOrganizerSettings.ts";
import {useUpdateOrganizerSettings} from "../../../../../../mutations/useUpdateOrganizerSettings.ts";
import {useFormErrorResponseHandler} from "../../../../../../hooks/useFormErrorResponseHandler.tsx";
import {useEffect} from "react";

const PAYOUT_METHODS = [
    {value: 'bank', label: 'Bank Transfer'},
    {value: 'mpesa_till', label: 'M-Pesa Till Number'},
    {value: 'mpesa_paybill', label: 'M-Pesa Paybill'},
    {value: 'mpesa_send_money', label: 'M-Pesa Send Money (Phone Number)'},
    {value: 'pochi_la_biashara', label: 'Pochi la Biashara'},
];

const PayoutDetails = () => {
    const {organizerId} = useParams();
    const settingsQuery = useGetOrganizerSettings(organizerId);
    const updateMutation = useUpdateOrganizerSettings();
    const formErrorHandler = useFormErrorResponseHandler();

    const form = useForm({
        initialValues: {
            payout_method: '',
            // Bank fields
            bank_name: '',
            account_number: '',
            account_name: '',
            // M-Pesa / Pochi fields
            phone_number: '',
            // Till / Paybill fields
            till_number: '',
            paybill_number: '',
            account_reference: '',
            // Common
            full_name: '',
        },
    });

    useEffect(() => {
        if (settingsQuery.isFetched && settingsQuery.data?.payout_details) {
            const pd = settingsQuery.data.payout_details;
            form.setValues({
                payout_method: pd.payout_method ?? '',
                bank_name: pd.bank_name ?? '',
                account_number: pd.account_number ?? '',
                account_name: pd.account_name ?? '',
                phone_number: pd.phone_number ?? '',
                till_number: pd.till_number ?? '',
                paybill_number: pd.paybill_number ?? '',
                account_reference: pd.account_reference ?? '',
                full_name: pd.full_name ?? '',
            });
        }
    }, [settingsQuery.isFetched]);

    const method = form.values.payout_method;

    const handleSubmit = form.onSubmit((values) => {
        // Only send relevant fields for the selected method
        const payload: Record<string, string> = {payout_method: values.payout_method};

        if (method === 'bank') {
            payload.bank_name = values.bank_name;
            payload.account_number = values.account_number;
            payload.account_name = values.account_name;
        } else if (method === 'mpesa_send_money' || method === 'pochi_la_biashara') {
            payload.phone_number = values.phone_number;
            payload.full_name = values.full_name;
        } else if (method === 'mpesa_till') {
            payload.till_number = values.till_number;
            payload.full_name = values.full_name;
        } else if (method === 'mpesa_paybill') {
            payload.paybill_number = values.paybill_number;
            payload.account_reference = values.account_reference;
            payload.full_name = values.full_name;
        }

        updateMutation.mutate(
            {organizerId, organizerSettings: {payout_details: payload}},
            {
                onSuccess: () => showSuccess(t`Payout details saved`),
                onError: (error) => formErrorHandler(form, error),
            }
        );
    });

    return (
        <>
            <HeadingCard
                heading={t`Payout Details`}
                subHeading={t`How you'd like to receive your event revenue`}
            />
            <Card>
                <Alert icon={<IconInfoCircle size={16}/>} color="blue" variant="light" mb="md">
                    {t`The platform admin will use these details to send your payout after each event. Make sure they are correct.`}
                </Alert>

                <form onSubmit={handleSubmit}>
                    <Stack gap="md">
                        <Select
                            label={t`Payout Method`}
                            placeholder={t`Select how you want to be paid`}
                            data={PAYOUT_METHODS}
                            required
                            {...form.getInputProps('payout_method')}
                        />

                        {/* Bank Transfer */}
                        {method === 'bank' && (
                            <>
                                <TextInput
                                    label={t`Bank Name`}
                                    placeholder={t`e.g. Equity Bank, KCB, Co-op Bank`}
                                    required
                                    {...form.getInputProps('bank_name')}
                                />
                                <TextInput
                                    label={t`Account Number`}
                                    placeholder={t`Your bank account number`}
                                    required
                                    {...form.getInputProps('account_number')}
                                />
                                <TextInput
                                    label={t`Account Name`}
                                    placeholder={t`Name on the bank account`}
                                    required
                                    {...form.getInputProps('account_name')}
                                />
                            </>
                        )}

                        {/* M-Pesa Send Money / Pochi la Biashara */}
                        {(method === 'mpesa_send_money' || method === 'pochi_la_biashara') && (
                            <>
                                <TextInput
                                    label={method === 'pochi_la_biashara' ? t`Pochi la Biashara Number` : t`M-Pesa Phone Number`}
                                    placeholder="e.g. 0712345678"
                                    required
                                    {...form.getInputProps('phone_number')}
                                />
                                <TextInput
                                    label={t`Full Name (as registered on M-Pesa)`}
                                    placeholder={t`Your full name`}
                                    required
                                    {...form.getInputProps('full_name')}
                                />
                            </>
                        )}

                        {/* M-Pesa Till Number */}
                        {method === 'mpesa_till' && (
                            <>
                                <TextInput
                                    label={t`Till Number`}
                                    placeholder={t`Your M-Pesa till number`}
                                    required
                                    {...form.getInputProps('till_number')}
                                />
                                <TextInput
                                    label={t`Business / Account Name`}
                                    placeholder={t`Name on the till`}
                                    required
                                    {...form.getInputProps('full_name')}
                                />
                            </>
                        )}

                        {/* M-Pesa Paybill */}
                        {method === 'mpesa_paybill' && (
                            <>
                                <TextInput
                                    label={t`Paybill Number`}
                                    placeholder={t`Your M-Pesa paybill number`}
                                    required
                                    {...form.getInputProps('paybill_number')}
                                />
                                <TextInput
                                    label={t`Account Reference`}
                                    placeholder={t`Account number / reference for the paybill`}
                                    required
                                    {...form.getInputProps('account_reference')}
                                />
                                <TextInput
                                    label={t`Business Name`}
                                    placeholder={t`Name on the paybill`}
                                    required
                                    {...form.getInputProps('full_name')}
                                />
                            </>
                        )}

                        {method && (
                            <Button
                                type="submit"
                                loading={updateMutation.isPending}
                                w="fit-content"
                            >
                                {t`Save Payout Details`}
                            </Button>
                        )}
                    </Stack>
                </form>
            </Card>
        </>
    );
};

export default PayoutDetails;
