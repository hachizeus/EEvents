// Build: 2026-05-11
import {createRoot} from "react-dom/client";
import {createBrowserRouter, RouterProvider} from "react-router-dom";
import {router} from "./router";
import {App} from "./App";
import {queryClient} from "./utilites/queryClient";
import {dynamicActivateLocale, getClientLocale, getSupportedLocale} from "./locales.ts";
import React from "react";

class AppErrorBoundary extends React.Component<
    { children: React.ReactNode },
    { hasError: boolean; error: Error | null }
> {
    constructor(props: { children: React.ReactNode }) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error: Error) {
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, info: React.ErrorInfo) {
        console.error('[AppErrorBoundary] Uncaught error:', error, info);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div style={{ padding: '2rem', fontFamily: 'sans-serif' }}>
                    <h2>Something went wrong</h2>
                    <p>Please refresh the page. If the problem persists, contact support.</p>
                    <pre style={{ fontSize: '0.8rem', color: '#666' }}>
                        {this.state.error?.message}
                    </pre>
                    <button onClick={() => window.location.reload()}>Reload</button>
                </div>
            );
        }
        return this.props.children;
    }
}

async function initApp() {
    const rawLocale = getClientLocale();
    const locale = getSupportedLocale(rawLocale);
    await dynamicActivateLocale(locale);

    const browserRouter = createBrowserRouter(router, {
        // Start at current path — React Router handles navigation
    });

    const container = document.getElementById("app") as HTMLElement;

    createRoot(container).render(
        <AppErrorBoundary>
            <App queryClient={queryClient} locale={rawLocale} dehydratedState={undefined}>
                <RouterProvider router={browserRouter}/>
            </App>
        </AppErrorBoundary>
    );
}

initApp().catch((err) => {
    console.error('[initApp] Fatal error during app initialization:', err);
    const container = document.getElementById("app");
    if (container) {
        container.innerHTML = `<div style="padding:2rem;font-family:sans-serif">
            <h2>Failed to load application</h2>
            <p>${err?.message || 'Unknown error'}</p>
            <button onclick="window.location.reload()">Reload</button>
        </div>`;
    }
});
