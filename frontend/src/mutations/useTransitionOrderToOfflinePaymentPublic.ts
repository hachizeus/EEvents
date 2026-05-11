import {orderClientPublic} from "../api/order.client.ts";
import {IdParam} from "../types.ts";
import {useMutation} from "@tanstack/react-query";
import {getSessionIdentifierFromUrl} from "../utilites/sessionIdentifier.ts";

export const useTransitionOrderToOfflinePaymentPublic = () => {
    return useMutation({
        mutationFn: ({eventId, orderShortId}: {
            eventId: IdParam,
            orderShortId: IdParam,
        }) => {
            const sessionIdentifier = getSessionIdentifierFromUrl();
            return orderClientPublic.transitionToOfflinePayment(eventId, orderShortId, sessionIdentifier ?? undefined);
        }
    });
}
