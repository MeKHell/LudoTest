import { For } from "solid-js";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "./ui/dropdown-menu";
import { Button } from "./ui/button";
import Globe from "lucide-solid/icons/globe";
import { DropdownMenuSubTriggerProps } from "@kobalte/core/dropdown-menu";

export function LanguageSelector(props: { class: string }) {
  const setLang = () =>{};
  const otherLanguages = () => ["de", "fr", "it"]
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
