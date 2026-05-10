import {Button, PasswordInput, TextInput, Collapse, UnstyledButton} from "@mantine/core";
import {NavLink, useLocation} from "react-router";
import {useMutation} from "@tanstack/react-query";
import {notifications} from '@mantine/notifications';
import {authClient} from "../../../../api/auth.client.ts";
import {LoginData, LoginResponse} from "../../../../types.ts";
import {useForm} from "@mantine/form";
import {redirectToPreviousUrl} from "../../../../api/client.ts";
import classes from "./Login.module.scss";
import {t} from "@lingui/macro";
import {useEffect, useState} from "react";
import {ChooseAccountModal} from "../../../modals/ChooseAccountModal";
import {useSendTicketLookupEmail} from "../../../../mutations/useSendTicketLookupEmail.ts";
import {showError} from "../../../../utilites/notifications.tsx";
import {IconTicket, IconChevronDown} from "@tabler/icons-react";

const Login = () => {
    const location = useLocation();
    const form = useForm({
        initialValues: {
            email: '',
            password: '',
            account_id: '',
        }
    });
    const [showChooseAccount, setShowChooseAccount] = useState(false);
    const [ticketLookupOpen, setTicketLookupOpen] = useState(false);

    const ticketLookupForm = useForm({
        initialValues: {
            email: '',
        }
    });
    const [ticketLookupSuccess, setTicketLookupSuccess] = useState(false);

    const {mutate: loginUser, isPending, data} = useMutation({
        mutationFn: (userData: LoginData) => authClient.login(userData),

        onSuccess: (response: LoginResponse) => {
            if (response.token) {
                redirectToPreviousUrl();
                return;
            }

            if (response.accounts.length > 1) {
                setShowChooseAccount(true);
                return;
            }
        },

        onError: () => {
            notifications.show({
                message: t`Please check your email and password and try again`,
                color: 'red',
                position: 'top-center',
            });
        }
    });

    const ticketLookupMutation = useSendTicketLookupEmail();

    useEffect(() => {
        form.values.account_id && loginUser(form.values);
    }, [form.values.account_id]);

    const handleTicketLookup = (values: { email: string }) => {
        ticketLookupMutation.mutate(values.email, {
            onSuccess: () => {
                setTicketLookupSuccess(true);
            },
            onError: () => {
                showError(t`Something went wrong. Please try again.`);
            }
        });
    };

    return (
        <>
            <div className={classes.topBar}>
                <span>New here?</span>
                <NavLink to={`/auth/register${location.search}`}>
                    Create an account
                </NavLink>
            </div>

            <div className={classes.heading}>
                <h2>{t`Sign in to your account`}</h2>
                <p>{t`Enter your credentials to continue`}</p>
            </div>

            <div className={classes.loginCard}>
                <form onSubmit={form.onSubmit((values) => loginUser(values))}>
                    <TextInput {...form.getInputProps('email')}
                               label={t`Email address`}
                               placeholder="you@example.com"
                               required
                    />
                    <PasswordInput {...form.getInputProps('password')}
                                   label={t`Password`}
                                   placeholder={t`Enter your password`}
                                   required
                                   mt="md"
                    />
                    <div className={classes.forgotLink}>
                        <NavLink to={`/auth/forgot-password`}>
                            {t`Forgot password?`}
                        </NavLink>
                    </div>
                    <Button color="primary" type="submit" fullWidth loading={isPending} disabled={isPending} mt="md">
                        {isPending ? t`Signing in...` : t`Sign in`}
                    </Button>
                </form>
            </div>

            <div className={classes.divider}>or</div>

            <div className={classes.ticketLookup}>
                <UnstyledButton
                    className={classes.ticketLookupTrigger}
                    onClick={() => setTicketLookupOpen(!ticketLookupOpen)}
                    data-expanded={ticketLookupOpen}
                >
                    <IconTicket size={16} />
                    <span>{t`Just looking for your tickets?`}</span>
                    <IconChevronDown
                        size={15}
                        className={classes.chevron}
                        data-expanded={ticketLookupOpen}
                    />
                </UnstyledButton>

                <Collapse in={ticketLookupOpen}>
                    <div className={classes.ticketLookupContent}>
                        {ticketLookupSuccess ? (
                            <div className={classes.successMessage}>
                                <p>{t`Check your inbox! If tickets are associated with this email, you'll receive a link to view them.`}</p>
                                <UnstyledButton
                                    className={classes.resetLink}
                                    onClick={() => {
                                        setTicketLookupSuccess(false);
                                        ticketLookupForm.reset();
                                    }}
                                >
                                    {t`Try another email`}
                                </UnstyledButton>
                            </div>
                        ) : (
                            <form onSubmit={ticketLookupForm.onSubmit(handleTicketLookup)}>
                                <div className={classes.ticketLookupForm}>
                                    <TextInput
                                        {...ticketLookupForm.getInputProps('email')}
                                        type="email"
                                        placeholder={t`Enter your email address`}
                                        required
                                        className={classes.ticketEmailInput}
                                    />
                                    <Button
                                        type="submit"
                                        color="primary"
                                        loading={ticketLookupMutation.isPending}
                                        disabled={ticketLookupMutation.isPending}
                                    >
                                        {t`Send link`}
                                    </Button>
                                </div>
                            </form>
                        )}
                    </div>
                </Collapse>
            </div>

            {(showChooseAccount && data) && <ChooseAccountModal onAccountChosen={(accountId) => {
                form.setFieldValue('account_id', accountId as string);
            }
            } accounts={data.accounts}/>}
        </>
    )
}

export default Login;
