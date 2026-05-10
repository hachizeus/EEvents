import {createRoot} from "react-dom/client";
import {createBrowserRouter, RouterProvider} from "react-router-dom";
import {router} from "./router";
import {App} from "./App";
import {queryClient} from "./utilites/queryClient";
import {dynamicActivateLocale, getClientLocale, getSupportedLocale} from "./locales.ts";

async function initApp() {
    // Load locale before rendering to avoid showing raw message IDs
    const rawLocale = getClientLocale();
    const locale = getSupportedLocale(rawLocale);
    await dynamicActivateLocale(locale);

    const browserRouter = createBrowserRouter(router);

    const container = document.getElementById("app") as HTMLElement;

    createRoot(container).render(
        <App queryClient={queryClient} locale={rawLocale} dehydratedState={undefined}>
            <RouterProvider router={browserRouter}/>
        </App>
    );
}

initApp();
