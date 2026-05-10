import {useMutation, useQueryClient} from '@tanstack/react-query';
import {IdParam} from '../types.ts';
import {accountClient, UpsertPaystackSettingRequest} from '../api/account.client.ts';
import {GET_ACCOUNT_PAYSTACK_SETTING_KEY} from '../queries/useGetAccountPaystackSetting.ts';

export const useUpsertAccountPaystackSetting = (accountId: IdParam) => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (payload: UpsertPaystackSettingRequest) =>
            accountClient.upsertPaystackSetting(accountId, payload),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: [GET_ACCOUNT_PAYSTACK_SETTING_KEY, accountId],
            });
        },
    });
};
