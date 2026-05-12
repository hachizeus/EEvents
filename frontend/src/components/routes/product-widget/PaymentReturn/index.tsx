import {usePollGetOrderPublic} from "../../../../queries/usePollGetOrderPublic.ts";
import {useNavigate, useParams} from "react-router";
import {useEffect, useRef, useState} from "react";
import classes from './PaymentReturn.module.scss';
import {t} from "@lingui/macro";
import {useGetOrderPaystackTransactionPublic} from "../../../../queries/useGetOrderPaystackTransactionPublic.ts";
import {CheckoutContent} from "../../../layouts/Checkout/CheckoutContent";
import {eventCheckoutPath} from "../../../../utilites/urlHelper.ts";
import {HomepageInfoMessage} from "../../../common/HomepageInfoMessage";
import {isSsr} from "../../../../utilites/helpers.ts";
import {trackEvent, AnalyticsEvents} from "../../../../utilites/analytics.ts";

/**
 * This component is responsible for handling the return from the payment provider.
 * Paystack should send a webhook to the backend to update the order status to 'COMPLETED'
 * However, if this fails, we will poll the order status to check if the payment has been processed.
 * This is a rare occurrence, but we should handle it gracefully.
 * It will also make local development easier in times when the webhook is not configured correctly.
 **/
export const PaymentReturn = () => {
    const [shouldPoll, setShouldPoll] = useState(true);
    const {eventId, orderShortId} = useParams();
    const {data: order} = usePollGetOrderPublic(eventId, orderShortId, shouldPoll, ['event']);
    const navigate = useNavigate();
    const [attemptManualConfirmation, setAttemptManualConfirmation] = useState(false);
    const paymentTransactionQuery = useGetOrderPaystackTransactionPublic(eventId, orderShortId, attemptManualConfirmation);
    const [cannotConfirmPayment, setCannotConfirmPayment] = useState(false);
    const hasTrackedPurchase = useRef(false);

    useEffect(
        () => {
            const timeout = setTimeout(() => {
                setShouldPoll(false);
                setAttemptManualConfirmation(true);
            }, 2000); // 2 seconds — verify quickly via Paystack API

            return () => {
                clearTimeout(timeout);
            };
        },
        []
    );

    useEffect(() => {
        if (!attemptManualConfirmation) return;
        if (paymentTransactionQuery.isLoading) return;

        if (paymentTransactionQuery.data?.status === 'succeeded') {
            if (!hasTrackedPurchase.current && order) {
                hasTrackedPurchase.current = true;
                const totalCents = Math.round((order.total_gross || 0) * 100);
                trackEvent(AnalyticsEvents.PURCHASE_COMPLETED_PAID, { value: totalCents });
            }
            navigate(eventCheckoutPath(eventId, orderShortId, 'summary'));
        } else if (paymentTransactionQuery.isError || paymentTransactionQuery.isFetched) {
            setCannotConfirmPayment(true);
        }
    }, [paymentTransactionQuery.isLoading, paymentTransactionQuery.data, paymentTransactionQuery.isError, paymentTransactionQuery.isFetched, attemptManualConfirmation]);

    useEffect(() => {
        if (isSsr() || !order) {
            return;
        }

        if (order?.status === 'COMPLETED') {
            if (!hasTrackedPurchase.current) {
                hasTrackedPurchase.current = true;
                const totalCents = Math.round((order.total_gross || 0) * 100);
                trackEvent(AnalyticsEvents.PURCHASE_COMPLETED_PAID, { value: totalCents });
            }
            navigate(eventCheckoutPath(eventId, orderShortId, 'summary'));
        }
        if (order?.payment_status === 'PAYMENT_FAILED' || (typeof window !== 'undefined' && window?.location.search.includes('failed'))) {
            navigate(eventCheckoutPath(eventId, orderShortId, 'payment') + '?payment_failed=true');
        }
    }, [order]);

    return (
        <CheckoutContent>
            <div className={classes.container}>
                {!cannotConfirmPayment && (
                    <HomepageInfoMessage
                        status="processing"
                        message={(
                            <>
                                {(!shouldPoll && paymentTransactionQuery.isFetched) && t`We could not process your payment. Please try again or contact support.`}
                                {(!shouldPoll && !paymentTransactionQuery.isFetched) && t`Almost there! We're just waiting for your payment to be processed. This should only take a few seconds.`}
                                {shouldPoll && t`We're processing your order. Please wait...`}
                            </>
                        )}
                    />
                )}

                {cannotConfirmPayment && (
                    <HomepageInfoMessage
                        status="error"
                        message={t`We were unable to confirm your payment. Please try again or contact support.`}
                    />
                )}
            </div>
        </CheckoutContent>
    );
}

export default PaymentReturn;
