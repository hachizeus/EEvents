import {t} from "@lingui/macro";
import classes from "./FloatingPoweredBy.module.scss";
import classNames from "classnames";
import React, {useMemo} from "react";
import {iHavePurchasedALicence} from "../../../utilites/helpers.ts";
import {getConfig} from "../../../utilites/config.ts";

export const PoweredByFooter = (
    props: React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement>
) => {
    if (iHavePurchasedALicence()) {
        return <></>;
    }

    const link = useMemo(() => {
        const url = new URL("https://elitjohnsdigital.com");
        url.searchParams.set("utm_source", "app-powered-by-footer");
        url.searchParams.set("utm_medium", "self-hosted-app");
        url.searchParams.set("utm_campaign", "powered-by");
        return url.toString();
    }, []);

    return (
        <div {...props} className={classNames(classes.poweredBy, props.className)}>
            <div className={classes.poweredByText}>
                {t`Powered by`}{" "}
                <a
                    href={link}
                    target="_blank"
                    title={"E Events — Powered by Elitjohns Digital"}
                >
                    Elitjohns Digital
                </a>{" "}
                🚀
            </div>
        </div>
    );
}
