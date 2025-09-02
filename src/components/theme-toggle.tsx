import Sun from "lucide-solid/icons/sun";
import Moon from "lucide-solid/icons/moon";
import { Button } from "./ui/button";
import { useColorMode } from "@kobalte/core";

export function ThemeToggle(props: { class?: string }) {
  const { colorMode, setColorMode } = useColorMode();
  return (
    <Button
      variant="outline"
      size="icon"
      class={props.class}
      onClick={[setColorMode, colorMode() === "light" ? "dark" : "light"]}
    >
      <Sun class="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
      <Moon class="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
    </Button>
  );
}
