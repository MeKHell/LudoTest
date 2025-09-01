import { For } from "solid-js";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "./ui/dropdown-menu";
import { Button } from "./ui/button";
import Globe from "lucide-solid/icons/globe";
import { useI18n } from "@/context/i18nContext";
import { DropdownMenuSubTriggerProps } from "@kobalte/core/dropdown-menu";

export function LanguageSelector(props: { class: string }) {
  const { otherLanguages, setLang } = useI18n();
  return (
    <div class={props.class}>
      <DropdownMenu placement="bottom-start">
        <DropdownMenuTrigger
          as={(props: DropdownMenuSubTriggerProps) => (
            <Button variant="outline" size="icon" {...props}>
              <Globe class="h-5 w-5" />
            </Button>
          )}
        />
        <DropdownMenuContent>
          <For each={otherLanguages()}>
            {(locale) => (
              <DropdownMenuItem onClick={[setLang, locale]}>
                {locale.toLocaleUpperCase()}
              </DropdownMenuItem>
            )}
          </For>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  );
}
