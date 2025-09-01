import { Suspense, type Component } from "solid-js";
import { NavBar } from "./components/navbar";

const App: Component = (props: { children: Element }) => {
  return (
    <>
      <NavBar />
      <main>
        <Suspense>{props.children}</Suspense>
      </main>
    </>
  );
};

export default App;
