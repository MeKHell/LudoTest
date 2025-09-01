import {
  _languages,
  Dictionary,
  fetchTranslator,
  translateDict,
} from "@/libs/i18n";
import { Language } from "@/types/d";
import {
  Accessor,
  createContext,
  createResource,
  createSignal,
  InitializedResource,
  JSX,
  Setter,
  useContext,
} from "solid-js";
import * as DeDict from "../locales/de.json";
import * as i18n from "@solid-primitives/i18n";

export type LanguageContextType = {
  lang: Accessor<Language>;
  setLang: Setter<Language>;
  t: InitializedResource<i18n.Translator<Dictionary, string>>;
  otherLanguages: Accessor<Language[]>;
};

const LanguageContext = createContext<LanguageContextType>();

export function I18nProvider(props: { lang: Language; children: JSX.Element }) {
  const [lang, setLang] = createSignal<Language>(props.lang);
  const [t] = createResource(lang, fetchTranslator, {
    initialValue: translateDict(DeDict),
  });
  const otherLanguages = () =>
    _languages.filter((language) => language !== lang());
  const languageProvider = { lang, setLang, t, otherLanguages };

  return (
    <LanguageContext.Provider value={languageProvider}>
      {props.children}
    </LanguageContext.Provider>
  );
}
export function useI18n() {
  return useContext(LanguageContext);
}
