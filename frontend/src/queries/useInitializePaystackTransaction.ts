import {useQuery} from "@tanstack/react-query";
import {orderClientPublic} from "../api/order.client.ts";
import {IdParam} from "../types.ts";
import {getSessionIdentifierFromUrl} from "../utilites/sessionIdentifier.ts";

export const INITIALIZE_PAYSTACK_TRANSACTION_QUERY_KEY = 'initializePaystackTransaction';

export const useInitializePaystackTransaction = (eventId: IdParam, orderShortId: IdParam) => {
    const sessionIdentifier = getSessionIdentifierFromUrl();

    return useQuery({
        queryKey: [INITIALIZE_PAYSTACK_TRANSACTION_QUERY_KEY, eventId, orderShortId],

        queryFn: async () => {
            return await orderClientPublic.initializePaystackTransaction(
                Number(eventId),
                String(orderShortId),
                sessionIdentifier ?? undefined,
            );
        },

        retry: false,
        staleTime: 0,
        gcTime: 0,
    });
};
