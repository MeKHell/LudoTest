/* @refresh reload */
import "./index.css";

import { render } from "solid-js/web";

import App from "./App";
import { Router } from "@solidjs/router";
import routes from "~solid-pages";
import { I18nProvider } from "./context/i18nContext";

const root = document.getElementById("root");

if (import.meta.env.DEV && !(root instanceof HTMLElement)) {
  throw new Error(
    "Root element not found. Did you forget to add it to your index.html? Or maybe the id attribute got misspelled?",
  );
}

render(
  () => (
    <I18nProvider lang="de">
      <Router root={(props) => <App>{props.children}</App>}>{routes}</Router>
    </I18nProvider>
  ),
  root,
);
