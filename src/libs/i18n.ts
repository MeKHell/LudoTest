import * as deDict from "../locales/de.json";
import * as frDict from "../locales/fr.json";
import * as enDict from "../locales/en.json";
import { Language } from "@/types/d";
import * as i18n from "@solid-primitives/i18n";

export type Dictionary = typeof deDict;

export const _languages: Language[] = ["de", "fr", "en"];
const defaultLanguage: Language = "de";

const dictionaries = {
  de: deDict,
  fr: frDict,
  en: enDict,
};

export function translateDict(dict: Dictionary) {
  return i18n.translator(() => dict, i18n.resolveTemplate);
}
export function fetchTranslator(
  lang: Language,
): i18n.Translator<Dictionary, string> {
  return translateDict(dictionaries[lang]);
}

export function getDefaultLanguage() {
  const savedLocale = localStorage.getItem("locale");
  if (savedLocale && _languages.includes(savedLocale as Language)) {
    return savedLocale as Language;
  }
  return defaultLanguage;
}
