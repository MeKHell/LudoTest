import { createSignal, For, Show, Suspense, type Component } from "solid-js";
import { A, useLocation } from "@solidjs/router";
import { I18nProvider, useI18n } from "./context/i18nContext";
import { Button } from "./components/ui/button";

const App: Component = (props: { children: Element }) => {
  const location = useLocation();
  const { lang, setLang, t, languages } = useI18n();

  return (
    <>
      <nav class="bg-gray-200 text-gray-900 px-4">
        <ul class="flex items-center">
          <li class="py-2 px-4">
            <A href="/" class="no-underline hover:underline">
              Home
            </A>
          </li>
          <li class="py-2 px-4">
            <A href="/about" class="no-underline hover:underline">
              About
            </A>
          </li>
          <li class="py-2 px-4">
            <A href="/error" class="no-underline hover:underline">
              Error
            </A>
          </li>

          <li class="text-sm flex items-center space-x-1 ml-auto">
            <span>URL:</span>
            <input
              class="w-75px p-1 bg-white text-sm rounded-lg"
              type="text"
              readOnly
              value={location.pathname}
            />
            <For each={languages()}>
              {(language) => (
                <Show when={language !== lang()}>
                  {" "}
                  <Button variant="outline" onClick={() => setLang(() => language)}>
                    {language}
                  </Button>
                </Show>
              )}
            </For>
          </li>
        </ul>
      </nav>

      <main>
        <Suspense>{props.children}</Suspense>
      </main>
    </>
  );
};

export default App;
