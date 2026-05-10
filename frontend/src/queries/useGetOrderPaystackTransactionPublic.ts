import {useQuery} from "@tanstack/react-query";
import {orderClientPublic} from "../api/order.client.ts";
import {IdParam} from "../types.ts";

export const GET_ORDER_PAYSTACK_TRANSACTION_PUBLIC_QUERY_KEY = 'getOrderPaystackTransactionPublic';

export const useGetOrderPaystackTransactionPublic = (
    eventId: IdParam,
    orderShortId: IdParam,
    enabled: boolean
) => {
    return useQuery<{ status: string }>({
        queryKey: [GET_ORDER_PAYSTACK_TRANSACTION_PUBLIC_QUERY_KEY, eventId, orderShortId],

        queryFn: async () => {
            return await orderClientPublic.verifyPaystackTransaction(
                Number(eventId),
                String(orderShortId),
            );
        },

        enabled: enabled,
        retry: false,
    });
};
