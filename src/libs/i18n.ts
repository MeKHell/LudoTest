import * as deDict from "../locales/de.json";
import { Language } from "@/types/d";
import * as i18n from "@solid-primitives/i18n"

export type Dictionary = typeof deDict;

export const _languages: Language[] = ["de", "fr", "en"]

export function translateDict(dict: Dictionary){
    return i18n.translator(() => dict, i18n.resolveTemplate)
}
export async function fetchTranslator(lang: Language): Promise<i18n.Translator<Dictionary, string>> {
  return translateDict(await import(`../locales/${lang}.json`));
}
