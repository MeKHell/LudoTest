import { For } from 'solid-js'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from './ui/dropdown-menu'
import { Button } from './ui/button'
import Globe from 'lucide-solid/icons/globe'
import { DropdownMenuSubTriggerProps } from '@kobalte/core/dropdown-menu'
import axios from 'axios'
import { router } from 'inertia-adapter-solid'

export function LanguageSelector(props: { class: string }) {
  const setLang = (lang: string) => {
    axios
      .post('/lang', {
        lang: lang,
      })
      .then(() => router.reload())
  }
  const otherLanguages = () => ['de', 'fr', 'en']
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
  )
}
