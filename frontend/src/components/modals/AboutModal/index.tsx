import {Modal} from "../../common/Modal";
import {GenericModalProps} from "../../../types.ts";
import classes from "./AboutModal.module.scss";
import {
    IconBrandGithub,
    IconHeadset,
    IconInfoCircle,
    IconLicense,
    IconMail,
    IconExternalLink,
} from "@tabler/icons-react";
import {getConfig} from "../../../utilites/config.ts";
import {t} from "@lingui/macro";

const links = [
    {
        icon: IconHeadset,
        label: t`Support`,
        description: t`Get help with your account`,
        href: "https://elitjohnsdigital.com/support",
        color: "#16A249",
    },
    {
        icon: IconLicense,
        label: t`License Info`,
        description: t`AGPLv3 open-source license`,
        href: "https://github.com/elitjohnsdigital/E.Events/blob/main/LICENCE",
        color: "#0ea5e9",
    },
    {
        icon: IconBrandGithub,
        label: t`GitHub`,
        description: t`View source code & contribute`,
        href: "https://github.com/elitjohnsdigital",
        color: "#6366f1",
    },
    {
        icon: IconMail,
        label: t`Contact Us`,
        description: t`Reach out to our team`,
        href: "mailto:support@elitjohnsdigital.com",
        color: "#f59e0b",
    },
];

export const AboutModal = ({onClose}: GenericModalProps) => {
    const appName = getConfig("VITE_APP_NAME", "E Events") as string;

    return (
        <Modal onClose={onClose} opened heading={t`About & Support`}>
            <div className={classes.aboutContainer}>
                {/* Header */}
                <div className={classes.header}>
                    <div className={classes.appIcon}>
                        <IconInfoCircle size={28} />
                    </div>
                    <div className={classes.headerText}>
                        <h3 className={classes.appName}>{appName}</h3>
                        <p className={classes.appDesc}>
                            {t`Event management & ticketing platform`}
                        </p>
                    </div>
                </div>

                {/* License notice */}
                <div className={classes.licenseNotice}>
                    <p>
                        {t`${appName} is distributed under the`}{" "}
                        <a
                            href="https://github.com/elitjohnsdigital/E.Events/blob/main/LICENCE"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            AGPLv3 license
                        </a>
                        {t` with additional terms. For any support or licensing inquiries, please refer to the links below.`}
                    </p>
                </div>

                {/* Links grid */}
                <div className={classes.linksGrid}>
                    {links.map((link) => {
                        const Icon = link.icon;
                        return (
                            <a
                                key={link.label}
                                href={link.href}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={classes.linkCard}
                            >
                                <div
                                    className={classes.linkIcon}
                                    style={{"--link-color": link.color} as React.CSSProperties}
                                >
                                    <Icon size={18} />
                                </div>
                                <div className={classes.linkText}>
                                    <span className={classes.linkLabel}>{link.label}</span>
                                    <span className={classes.linkDesc}>{link.description}</span>
                                </div>
                                <IconExternalLink size={14} className={classes.externalIcon} />
                            </a>
                        );
                    })}
                </div>

                {/* Footer */}
                <div className={classes.footer}>
                    <span>Powered by Elitjohns Digital</span>
                    <span className={classes.dot}>·</span>
                    <a
                        href="https://elitjohnsdigital.com"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        elitjohnsdigital.com
                    </a>
                </div>
            </div>
        </Modal>
    );
};
