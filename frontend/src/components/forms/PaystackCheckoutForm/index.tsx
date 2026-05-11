import {useEffect, useRef, useState} from "react";
import {useParams} from "react-router";
import {Alert, Skeleton} from "@mantine/core";
import {t} from "@lingui/macro";
import {useGetOrderPublic} from "../../../queries/useGetOrderPublic.ts";
import {useInitializePaystackTransaction} from "../../../queries/useInitializePaystackTransaction.ts";
import {CheckoutContent} from "../../layouts/Checkout/CheckoutContent";
import {HomepageInfoMessage} from "../../common/HomepageInfoMessage";
import {eventCheckoutPath, eventHomepagePath} from "../../../utilites/urlHelper.ts";
import {Event} from "../../../types.ts";

declare global {
    interface Window {
        PaystackPop?: {
            setup: (options: PaystackPopOptions) => { openIframe: () => void };
        };
    }
}

interface PaystackPopOptions {
    key: string;
    email: string;
    amount: number;
    ref: string;
    currency?: string;
    onSuccess: (transaction: { reference: string }) => void;
    onCancel: () => void;
}

interface PaystackCheckoutFormProps {
    setSubmitHandler: (submitHandler: () => () => Promise<void>) => void;
}

export default function PaystackCheckoutForm({setSubmitHandler}: PaystackCheckoutFormProps) {
    const {eventId, orderShortId} = useParams();
    const {data: order, isFetched: isOrderFetched} = useGetOrderPublic(eventId, orderShortId, ['event']);
    const {data: paystackData, isFetched: isPaystackFetched, error: paystackError} = useInitializePaystackTransaction(eventId, orderShortId);
    const event = order?.event;
    const [message, setMessage] = useState<string | undefined>('');
    const [paystackLoaded, setPaystackLoaded] = useState(false);
    const scriptRef = useRef<HTMLScriptElement | null>(null);

    // Load Paystack inline script
    useEffect(() => {
        if (scriptRef.current) return;

        const script = document.createElement('script');
        script.src = 'https://js.paystack.co/v1/inline.js';
        script.async = true;
        script.onload = () => setPaystackLoaded(true);
        document.body.appendChild(script);
        scriptRef.current = script;

        return () => {
            if (scriptRef.current) {
                document.body.removeChild(scriptRef.current);
                scriptRef.current = null;
            }
        };
    }, []);

    const handleSubmit = async () => {
        if (!paystackData || !order || !paystackLoaded) {
            setMessage(t`Payment provider is not ready. Please try again.`);
            return;
        }

        if (!window.PaystackPop) {
            setMessage(t`Payment provider failed to load. Please refresh and try again.`);
            return;
        }

        return new Promise<void>((resolve, reject) => {
            const handler = window.PaystackPop!.setup({
                key: paystackData.public_key,
                email: order.email,
                amount: Math.round(order.total_gross * 100),
                ref: paystackData.reference,
                currency: order.currency?.toUpperCase(),
                onSuccess: () => {
                    // Redirect to payment return page - webhook will handle order completion
                    const sessionId = new URL(window.location.href).searchParams.get('session_identifier');
                    const returnPath = `/checkout/${eventId}/${orderShortId}/payment_return`;
                    window.location.href = window.location.origin + returnPath + (sessionId ? `?session_identifier=${sessionId}` : '');
                    resolve();
                },
                onCancel: () => {
                    setMessage(t`Payment was cancelled. Please try again.`);
                    resolve();
                },
            });

            handler.openIframe();
        });
    };

    useEffect(() => {
        if (setSubmitHandler) {
            setSubmitHandler(() => handleSubmit);
        }
    }, [setSubmitHandler, paystackData, order, paystackLoaded]);

    if (!isOrderFetched || !order?.payment_status) {
        return (
            <CheckoutContent>
                <Skeleton height={300} mb={20}/>
            </CheckoutContent>
        );
    }

    if (order?.payment_status === 'PAYMENT_RECEIVED') {
        return (
            <HomepageInfoMessage
                status="success"
                message={t`Payment received`}
                subtitle={t`This order has already been paid.`}
                linkText={t`View Order Details`}
                link={eventCheckoutPath(eventId, orderShortId, 'summary')}
            />
        );
    }

    if (order?.payment_status !== 'AWAITING_PAYMENT' && order?.payment_status !== 'PAYMENT_FAILED') {
        return (
            <HomepageInfoMessage
                status="expired"
                message={t`Page no longer available`}
                subtitle={t`This order page is no longer available.`}
                linkText={t`Back to Event`}
                link={eventHomepagePath(event as Event)}
            />
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

    return (
        <form id="payment-form">
            <h2>{t`Payment`}</h2>

            {(order?.payment_status === 'PAYMENT_FAILED' || window?.location.search.includes('payment_failed')) && (
                <Alert mb={20} color="red">{t`Your payment was unsuccessful. Please try again.`}</Alert>
            )}

            {message && <Alert mb={20}>{message}</Alert>}

            {!isPaystackFetched && <Skeleton height={60} mb={10}/>}

            {isPaystackFetched && (
                <Alert color="blue" mb={10}>
                    {t`Click "Pay" below to complete your payment securely via Paystack.`}
                </Alert>
            )}
        </form>
    );
}
