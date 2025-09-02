import { type Component } from "solid-js";
import { NavBar } from "./components/navbar";

const App: Component = (props: { children: Element }) => {
  return (
    <>
      <NavBar />
      <main>{props.children}</main>
    </>
  );
};

export default App;
