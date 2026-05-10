import {useParams} from "react-router";
import {useGetEventPublic} from "../../../../../../queries/useGetEventPublic.ts";
import {CheckoutContent} from "../../../../../layouts/Checkout/CheckoutContent";
import {HomepageInfoMessage} from "../../../../../common/HomepageInfoMessage";
import {t} from "@lingui/macro";
import {eventHomepagePath} from "../../../../../../utilites/urlHelper.ts";
import {LoadingMask} from "../../../../../common/LoadingMask";
import PaystackCheckoutForm from "../../../../../forms/PaystackCheckoutForm";
import {Event} from "../../../../../../types.ts";
import {useInitializePaystackTransaction} from "../../../../../../queries/useInitializePaystackTransaction.ts";

interface PaystackPaymentMethodProps {
    enabled: boolean;
    setSubmitHandler: (submitHandler: () => () => Promise<void>) => void;
}

export const PaystackPaymentMethod = ({enabled, setSubmitHandler}: PaystackPaymentMethodProps) => {
    const {eventId, orderShortId} = useParams();
    const {
        isFetched: isPaystackFetched,
        error: paystackError,
    } = useInitializePaystackTransaction(eventId, orderShortId);
    const {data: event} = useGetEventPublic(eventId);

    if (!enabled) {
        return (
            <CheckoutContent>
                <HomepageInfoMessage
                    status="warning"
                    message={t`Payments not available`}
                    subtitle={t`Paystack payments are not enabled for this event.`}
                    link={eventHomepagePath(event as Event)}
                    linkText={t`Return to Event`}
                />
            </CheckoutContent>
        );
    }

    if (paystackError && event) {
        return (
            <CheckoutContent>
                <HomepageInfoMessage
                    status="error"
                    /* @ts-ignore */
                    message={(paystackError as any).response?.data?.message || t`Something went wrong`}
                    subtitle={t`Please restart the checkout process.`}
                    link={eventHomepagePath(event)}
                    linkText={t`Return to Event`}
                />
            </CheckoutContent>
        );
    }

    if (!isPaystackFetched) {
        return <LoadingMask/>;
    }

    return <PaystackCheckoutForm setSubmitHandler={setSubmitHandler}/>;
};
