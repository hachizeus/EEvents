import {i18n} from "@lingui/core";

export type SupportedLocales =
    "en"
    | "de"
    | "fr"
    | "it"
    | "nl"
    | "pt"
    | "es"
    | "zh-cn"
    | "pt-br"
    | "vi"
    | "zh-hk"
    | "tr"
    | "hu"
    | "pl"
    | "se"
    | "sw";

export const availableLocales = ["en", "de", "fr", "it", "nl", "pt", "es", "zh-cn", "zh-hk", "pt-br", "vi", "tr", "hu", "pl", "se", "sw"];

export const localeToFlagEmojiMap: Record<SupportedLocales, string> = {
    en: '🇬🇧',
    de: '🇩🇪',
    fr: '🇫🇷',
    it: '🇮🇹',
    nl: '🇳🇱',
    pt: '🇵🇹',
    es: '🇪🇸',
    "zh-cn": '🇨🇳',
    "zh-hk": '🇭🇰',
    "pt-br": '🇧🇷',
    vi: '🇻🇳',
    tr: '🇹🇷',
    hu: '🇭🇺',
    pl: '🇵🇱',
    se: '🇸🇪',
    sw: '🇰🇪',
};

export const localeToNameMap: Record<SupportedLocales, string> = {
    en: `English`,
    de: `German`,
    fr: `French`,
    it: `Italian`,
    nl: `Dutch`,
    pt: `Portuguese`,
    es: `Spanish`,
    "zh-cn": `Chinese`,
    "zh-hk": `Cantonese`,
    "pt-br": `Portuguese (Brazil)`,
    vi: `Vietnamese`,
    tr: `Turkish`,
    hu: `Hungarian`,
    pl: `Polish`,
    se: `Swedish`,
    sw: `Swahili`,
};

export const getLocaleName = (locale: SupportedLocales) => {
    return localeToNameMap[locale];
}

export const getClientLocale = () => {
    if (typeof window !== "undefined") {
        const storedLocale = document
            .cookie
            .split(";")
            .find((c) => c.includes("locale="))
            ?.split("=")[1];

        if (storedLocale) {
            return getSupportedLocale(storedLocale);
        }

        return getSupportedLocale(window.navigator.language);
    }

    return "en";
};

export async function dynamicActivateLocale(locale: string) {
    try {
        locale = availableLocales.includes(locale) ? locale : "en";
        const module = (await import(`./locales/${locale}.po`));
        i18n.load(locale, module.messages);
        i18n.activate(locale);
    } catch (error) {
        console.warn(`Failed to load .po file for locale: ${locale}. Attempting to load .js file.`);
        try {
            const module = (await import(`./locales/${locale}.js`));
            const messages = module.messages ?? module.default?.messages ?? module;
            i18n.load(locale, messages);
            i18n.activate(locale);
        } catch (fallbackError) {
            console.error(`Error loading locale: ${locale}. Falling back to default locale 'en'.`, fallbackError);
            const defaultModule = (await import(`./locales/en.js`));
            const defaultMessages = defaultModule.messages ?? defaultModule.default?.messages ?? defaultModule;
            i18n.load("en", defaultMessages);
            i18n.activate("en");
        }
    }
}

export const getSupportedLocale = (userLocale: string) => {
    const normalizedLocale = userLocale.toLowerCase();

    if (availableLocales.includes(normalizedLocale)) {
        return normalizedLocale;
    }

    const mainLanguage = normalizedLocale.split('-')[0];
    const mainLocale = availableLocales.find(locale => locale.startsWith(mainLanguage));
    if (mainLocale) {
        return mainLocale;
    }

    return "en";
};
