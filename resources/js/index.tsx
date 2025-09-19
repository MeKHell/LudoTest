/* @refresh reload */
import "./index.css";

import { Suspense } from "solid-js";
import { render } from "solid-js/web";

import App from "./App";
import { Router } from "@solidjs/router";
import routes from "~solid-pages";
import { I18nProvider } from "./context/i18nContext";
import { ColorModeProvider, ColorModeScript } from "@kobalte/core/color-mode";
import { getDefaultLanguage } from "./libs/i18n";

const root = document.getElementById("root");

if (import.meta.env.DEV && !(root instanceof HTMLElement)) {
  throw new Error(
    "Root element not found. Did you forget to add it to your index.html? Or maybe the id attribute got misspelled?",
  );
}

render(
  () => (
    <I18nProvider lang={getDefaultLanguage()}>
      <Suspense>
        <ColorModeScript />
        <ColorModeProvider>
          <Router root={(props) => <App>{props.children}</App>}>
            {routes}
          </Router>
        </ColorModeProvider>
      </Suspense>
    </I18nProvider>
  ),
  root,
);
