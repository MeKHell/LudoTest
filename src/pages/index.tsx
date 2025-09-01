import { useI18n } from "@/context/i18nContext";
import { createSignal } from "solid-js";

const [count, setCount] = createSignal(0);
export default function Home() {
  const { t } = useI18n();

  return (
    <section class="p-8">
      <h1 class="text-2xl font-bold">{t()("home")}</h1>
      <p class="mt-4">This is the home page.</p>

      <div class="flex items-center space-x-2">
        <button
          type="button"
          class="border-2 rounded-lg px-2"
          onClick={() => setCount(count() - 1)}
        >
          -
        </button>

        <output class="p-10px">Count: {count()}</output>

        <button
          type="button"
          class="border-2 rounded-lg px-2"
          onClick={() => setCount(count() + 1)}
        >
          +
        </button>
      </div>
    </section>
  );
}
