import {useQuery} from "@tanstack/react-query";
import {orderClientPublic} from "../api/order.client.ts";
import {IdParam} from "../types.ts";
import {getSessionIdentifierFromUrl} from "../utilites/sessionIdentifier.ts";

export const GET_ORDER_PAYSTACK_TRANSACTION_PUBLIC_QUERY_KEY = 'getOrderPaystackTransactionPublic';

export const useGetOrderPaystackTransactionPublic = (
    eventId: IdParam,
    orderShortId: IdParam,
    enabled: boolean
) => {
    const sessionIdentifier = getSessionIdentifierFromUrl();

    return useQuery<{ status: string }>({
        queryKey: [GET_ORDER_PAYSTACK_TRANSACTION_PUBLIC_QUERY_KEY, eventId, orderShortId],

        queryFn: async () => {
            return await orderClientPublic.verifyPaystackTransaction(
                Number(eventId),
                String(orderShortId),
                sessionIdentifier ?? undefined,
            );
        },

        enabled: enabled,
        retry: false,
    });
};
