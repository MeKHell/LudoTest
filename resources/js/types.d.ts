/// <reference types="vite/client" />

import type { AxiosStatic } from 'axios';

declare global {
  interface Window {
    axios: AxiosStatic;
  }

  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    // Add more env variables as needed
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
  }
}