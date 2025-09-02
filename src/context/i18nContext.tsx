import { _languages, Dictionary, fetchTranslator } from "@/libs/i18n";
import { Language } from "@/types/d";
import {
  Accessor,
  createContext,
  createSignal,
  JSX,
  useContext,
} from "solid-js";
import * as i18n from "@solid-primitives/i18n";

export type LanguageContextType = {
  lang: Accessor<Language>;
  setLang: (lang: Language) => void;
  t: (code: keyof Dictionary, ...args: i18n.BaseTemplateArgs[]) => string;
  otherLanguages: Accessor<Language[]>;
};

const LanguageContext = createContext<LanguageContextType>();

export function I18nProvider(props: { lang: Language; children: JSX.Element }) {
  // eslint-disable-next-line solid/reactivity
  const [lang, setLang] = createSignal<Language>(props.lang);
  const translator = () => fetchTranslator(lang());
  const t = (code: keyof Dictionary, ...args: i18n.BaseTemplateArgs[]) =>
    translator()(code, ...args);
  const setDefaultLang = (lang: Language) => {
    setLang(() => lang);
    localStorage.setItem("locale", lang);
  };
  const otherLanguages = () =>
    _languages.filter((language) => language !== lang());
  const languageProvider = { lang, setLang: setDefaultLang, t, otherLanguages };

  return (
    <LanguageContext.Provider value={languageProvider}>
      {props.children}
    </LanguageContext.Provider>
  );
}
export function useI18n() {
  return useContext(LanguageContext);
}
