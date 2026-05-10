import {Button, Checkbox, PasswordInput, SimpleGrid, TextInput} from "@mantine/core";
import {hasLength, isEmail, matchesField, useForm} from "@mantine/form";
import {RegisterAccountRequest} from "../../../../types.ts";
import {useFormErrorResponseHandler} from "../../../../hooks/useFormErrorResponseHandler.tsx";
import {useRegisterAccount} from "../../../../mutations/useRegisterAccount.ts";
import {NavLink, useLocation, useNavigate} from "react-router";
import {t, Trans} from "@lingui/macro";
import classes from "./Register.module.scss";
import {getClientLocale} from "../../../../locales.ts";
import {useEffect} from "react";
import {getUserCurrency} from "../../../../utilites/currency.ts";
import {getConfig} from "../../../../utilites/config.ts";
import {captureUtmData, getStoredUtmData, clearStoredUtmData} from "../../../../utilites/utm.ts";

export const Register = () => {
    const navigate = useNavigate();
    const location = useLocation();

    const form = useForm({
        validateInputOnBlur: true,
        initialValues: {
            first_name: '',
            last_name: '',
            email: '',
            password: '',
            password_confirmation: '',
            timezone: typeof window !== 'undefined'
                ? Intl.DateTimeFormat().resolvedOptions().timeZone
                : 'UTC',
            locale: getClientLocale(),
            invite_token: '',
            currency_code: getUserCurrency(),
            marketing_opt_in: false,
        },
        validate: {
            password: hasLength({min: 8}, t`Password must be at least 8 characters`),
            password_confirmation: matchesField('password', t`Passwords are not the same`),
            email: isEmail(t`Please check your email is valid`),
        },
    });
    const errorHandler = useFormErrorResponseHandler();
    const mutate = useRegisterAccount();

    const registerUser = (data: RegisterAccountRequest) => {
        const utmData = getStoredUtmData();
        const registrationData = utmData ? {...data, ...utmData} : data;

        mutate.mutate({registerData: registrationData}, {
            onSuccess: () => {
                clearStoredUtmData();
                navigate(`/welcome${location.search}`);
            },
            onError: (error: any) => {
                errorHandler(form, error, error.response?.data?.message);
            },
        });
    }

    useEffect(() => {
        captureUtmData();

        const searchParams = new URLSearchParams(location.search);
        const token = searchParams.get('invite_token');

        if (token) {
            form.setFieldValue('invite_token', token);
        }
    }, [location.search]);

    return (
        <>
            <div className={classes.topBar}>
                <span>Already have an account?</span>
                <NavLink to={`/auth/login${location.search}`}>
                    {t`Sign in`}
                </NavLink>
            </div>

            <div className={classes.heading}>
                <h2>{t`Create your account`}</h2>
                <p>{t`Start selling tickets in minutes — no credit card required`}</p>
            </div>

            <div className={classes.registerCard}>
                <form onSubmit={form.onSubmit((values) => registerUser(values as RegisterAccountRequest))}>

                    <p className={classes.sectionLabel}>{t`Account details`}</p>

                    <TextInput
                        mb={0}
                        {...form.getInputProps('email')}
                        label={t`Email address`}
                        placeholder={'you@example.com'}
                        required
                    />

                    <p className={classes.sectionLabel}>{t`Your name`}</p>

                    <SimpleGrid verticalSpacing={{base: "md", sm: 0}} cols={{base: 1, sm: 2}} mb="md">
                        <TextInput
                            {...form.getInputProps('first_name')}
                            label={t`First name`}
                            placeholder={t`John`}
                            required
                        />
                        <TextInput
                            {...form.getInputProps('last_name')}
                            label={t`Last name`}
                            placeholder={t`Smith`}
                        />
                    </SimpleGrid>

                    <p className={classes.sectionLabel}>{t`Set a password`}</p>

                    <SimpleGrid verticalSpacing={{base: "md", sm: 0}} cols={{base: 1, sm: 2}} mb="md">
                        <PasswordInput
                            {...form.getInputProps('password')}
                            label={t`Password`}
                            placeholder={t`Min. 8 characters`}
                            required
                        />
                        <PasswordInput
                            {...form.getInputProps('password_confirmation')}
                            label={t`Confirm password`}
                            placeholder={t`Repeat password`}
                            required
                        />
                    </SimpleGrid>

                    <TextInput
                        style={{display: 'none'}}
                        {...form.getInputProps('timezone')}
                        type="hidden"
                    />

                    <Checkbox
                        mb="md"
                        {...form.getInputProps('marketing_opt_in', {type: 'checkbox'})}
                        label={<Trans>Receive product updates from {getConfig("VITE_APP_NAME", "E.Events")}.</Trans>}
                    />

                    <Button color="primary" type="submit" fullWidth disabled={mutate.isPending}>
                        {mutate.isPending ? t`Creating account...` : t`Create account`}
                    </Button>
                </form>
                <footer>
                    <Trans>
                        By registering you agree to our <NavLink target={'_blank'}
                                                                 to={getConfig("VITE_TOS_URL", "https://elitjohnsdigital.com/terms") as string}>Terms
                        of Service</NavLink> and <NavLink
                        target={'_blank'}
                        to={getConfig("VITE_PRIVACY_URL", 'https://elitjohnsdigital.com/privacy') as string}>Privacy Policy</NavLink>.
                    </Trans>
                </footer>
            </div>
        </>
    )
}

export default Register;
