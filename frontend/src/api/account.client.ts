import {api} from "./client.ts";
import {Account, GenericDataResponse, IdParam, User} from "../types.ts";

interface CreateAccountRequest {
    first_name: string;
    last_name: string;
    email: string;
    password?: string;
}

export interface PaystackSettingResponse {
    is_connected: boolean;
    public_key: string | null;
    secret_key_hint?: string;
}

export interface UpsertPaystackSettingRequest {
    public_key: string;
    secret_key: string;
}

export const accountClient = {
    create: async (account: CreateAccountRequest) => {
        const response = await api.post<GenericDataResponse<User>>('accounts', account);
        return response.data;
    },
    getAccount: async () => {
        const response = await api.get<GenericDataResponse<Account>>('accounts');
        return response.data;
    },
    updateAccount: async (account: Account) => {
        const response = await api.put<GenericDataResponse<Account>>('accounts', account);
        return response.data;
    },
    getPaystackSetting: async (accountId: IdParam) => {
        const response = await api.get<GenericDataResponse<PaystackSettingResponse>>(
            `accounts/${accountId}/paystack/settings`
        );
        return response.data;
    },
    upsertPaystackSetting: async (accountId: IdParam, payload: UpsertPaystackSettingRequest) => {
        const response = await api.post<GenericDataResponse<PaystackSettingResponse>>(
            `accounts/${accountId}/paystack/settings`,
            payload
        );
        return response.data;
    },
}
