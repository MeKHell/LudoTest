import Spade from "lucide-solid/icons/spade";
import { LanguageSelector } from "./language-selector";
import { A } from "@solidjs/router";
import { Button } from "./ui/button";
import { useI18n } from "@/context/i18nContext";
import { ThemeToggle } from "./theme-toggle";
import { For, JSX } from "solid-js";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "./ui/dropdown-menu";
import Menu from "lucide-solid/icons/menu";
import { DropdownMenuSubTriggerProps } from "@kobalte/core/dropdown-menu";

export function NavBar() {
  const { t } = useI18n();
  const content: {
    url: string;
    element: JSX.Element;
    additional_style?: string;
  }[] = [
    {
      url: "/",
      element: <div>{t()("home")}</div>,
      additional_style:
        "text-foreground h-full rounded hover:bg-accent transition-colors text-lg px-2",
    },
    {
      url: "/games",
      element: <div>{t()("games")}</div>,
      additional_style:
        "text-foreground h-full rounded hover:bg-accent transition-colors text-lg px-2",
    },
    {
      url: "/login",
      element: (
        <div class="flex justify-between">
          <Button variant="outline" size="sm" class="mx-2 text-md">
            {t()("login")}
          </Button>
          <div class="flex md:hidden">
            <ThemeToggle />
            <LanguageSelector class="ml-1" />
          </div>
        </div>
      ),
    },
  ];
  return (
    <div class="border-b bg-card/50 backdrop-blur-sm sticky top-0 z-50 h-14">
      <div class="container mx-auto px-4 flex-nowrap h-full">
        <div class="flex items-stretch justify-between h-full w-full">
          <div class="flex items-center h-full grow-0 ">
            <Spade class="h-8 w-8 text-primary" />
            <h1 class="text-2xl font-bold text-primary">LudoTest</h1>
          </div>

          <nav class="flex items-stretch shrink-0 h-full mx-3">
            <div class="hidden md:flex items-stretch justify-around flex-nowrap shrink-0 h-full">
              <For each={content}>
                {(elem) => (
                  <A
                    href={elem.url}
                    class={`flex items-center ${elem.additional_style}`}
                  >
                    {elem.element}
                  </A>
                )}
              </For>
            </div>
          </nav>
          <div class="flex items-center grow-0 justify-between">
            <nav class="flex md:hidden h-full items-center">
              <DropdownMenu placement="bottom">
                <DropdownMenuTrigger
                  as={(props: DropdownMenuSubTriggerProps) => (
                    <Button
                      variant="outline"
                      size="icon"
                      class="mx-2 text-md"
                      {...props}
                    >
                      <Menu class="w-5 h-5" />
                    </Button>
                  )}
                />
                <DropdownMenuContent class="w-screen">
                  <For each={content}>
                    {(elem) => (
                      <DropdownMenuItem class="w-full">
                        <A href={elem.url} class={`w-full`}>
                          {elem.element}
                        </A>
                      </DropdownMenuItem>
                    )}
                  </For>
                </DropdownMenuContent>
              </DropdownMenu>
            </nav>

            <ThemeToggle class="hidden md:flex" />
            <LanguageSelector class="hidden md:flex ml-1" />
          </div>
        </div>
      </div>
    </div>
  );
}
