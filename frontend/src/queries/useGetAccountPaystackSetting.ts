import {useQuery, UseQueryOptions} from '@tanstack/react-query';
import {IdParam} from '../types.ts';
import {accountClient, PaystackSettingResponse} from '../api/account.client.ts';

export const GET_ACCOUNT_PAYSTACK_SETTING_KEY = 'getAccountPaystackSetting';

export const useGetAccountPaystackSetting = (
    accountId: IdParam,
    options?: Partial<UseQueryOptions<PaystackSettingResponse>>
) => {
    return useQuery<PaystackSettingResponse>({
        queryKey: [GET_ACCOUNT_PAYSTACK_SETTING_KEY, accountId],
        queryFn: async () => {
            const {data} = await accountClient.getPaystackSetting(accountId);
            return data;
        },
        enabled: !!accountId,
        ...options,
    });
};
