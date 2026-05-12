import React, {FC, PropsWithChildren, useCallback} from "react";
import {MantineProvider} from "@mantine/core";
import {Notifications} from "@mantine/notifications";
import {i18n} from "@lingui/core";
import {I18nProvider} from "@lingui/react";
import {ModalsProvider} from "@mantine/modals";
import {HydrationBoundary, QueryClient, QueryClientProvider} from "@tanstack/react-query";
import {Helmet, HelmetProvider} from "react-helmet-async";
import {generateColors} from '@mantine/colors-generator';

import "@mantine/core/styles/global.css";
import "@mantine/core/styles.css";
import "@mantine/notifications/styles.css";
import "@mantine/tiptap/styles.css";
import "@mantine/dropzone/styles.css";
import '@mantine/dates/styles.css';
import "@mantine/charts/styles.css";
import "./styles/global.scss";
import {isSsr} from "./utilites/helpers.ts";
import {StartupChecks} from "./StartupChecks.tsx";
import {ThirdPartyScripts} from "./components/common/ThirdPartyScripts";
import {getConfig} from "./utilites/config.ts";
import {CookieConsentBanner} from "./components/common/CookieConsentBanner";
import {isConsentPending, setConsentState, updateGoogleConsentMode} from "./utilites/trackingPixels/consent";

declare global {
    interface Window {
        hievents: Record<string, string>;
        eevents: Record<string, string>;
    }
}

export const App: FC<
    PropsWithChildren<{
        queryClient: QueryClient;
        locale: string;
        helmetContext?: any;
        dehydratedState?: unknown;
    }>
> = (props) => {
    const showGlobalConsentBanner = getConfig('VITE_COOKIE_CONSENT_ENABLED') === 'true'
        && !isSsr() && isConsentPending();

    const handleGlobalConsent = useCallback((granted: boolean) => {
        setConsentState(granted ? 'granted' : 'denied');
        updateGoogleConsentMode(granted);
        window.dispatchEvent(new CustomEvent('hi_consent_change', {detail: {granted}}));
    }, []);

    return (
        <React.StrictMode>
            <MantineProvider
                theme={{
                    colors: {
                        primary: generateColors(getConfig("VITE_APP_PRIMARY_COLOR", "#40296C") as string),
                        secondary: generateColors(getConfig("VITE_APP_SECONDARY_COLOR", "#3d0b44") as string),
                    },
                    primaryColor: "primary",
                    fontFamily: "Inter, sans-serif",
                    headings: {
                        fontFamily: "Sora, sans-serif",
                    },
                    primaryShade: 8,
                    defaultRadius: "md",
                    components: {
                        Button: {
                            defaultProps: {
                                radius: "md",
                            },
                        },
                        TextInput: {
                            defaultProps: {
                                radius: "md",
                            },
                        },
                        PasswordInput: {
                            defaultProps: {
                                radius: "md",
                            },
                        },
                        Select: {
                            defaultProps: {
                                radius: "md",
                            },
                        },
                        Card: {
                            defaultProps: {
                                radius: "lg",
                            },
                        },
                    },
                }}
            >
                <HelmetProvider context={props.helmetContext}>
                    <I18nProvider i18n={i18n}>
                        <QueryClientProvider client={props.queryClient}>
                            <HydrationBoundary state={props.dehydratedState}>
                                <StartupChecks/>
                                <ThirdPartyScripts/>
                                <ModalsProvider>
                                    <Helmet>
                                        <title>{getConfig("VITE_APP_NAME", "E.Events")}</title>
                                        <link rel="icon"
                                              type="image/png"
                                              sizes="768x768"
                                              href={getConfig("VITE_APP_FAVICON", "/logos/2.png")}
                                        />
                                    </Helmet>
                                    {props.children}
                                </ModalsProvider>
                                <Notifications/>
                                {showGlobalConsentBanner && (
                                    <CookieConsentBanner onConsent={handleGlobalConsent}/>
                                )}
                            </HydrationBoundary>
                        </QueryClientProvider>
                    </I18nProvider>
                </HelmetProvider>
            </MantineProvider>
        </React.StrictMode>
    );
};
