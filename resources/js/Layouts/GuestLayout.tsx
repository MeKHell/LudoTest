import { JSX } from "solid-js"
import { Title } from "@solidjs/meta"
import { NavBar } from "@/components/navbar";

interface GuestLayoutProps {
    title?: string;
    children?: JSX.Element;
}

function GuestLayout(props: GuestLayoutProps) {
    return (
        <>
            <Title>{props.title ? `${props.title} - LudoTest` : 'LudoTest'}</Title>
            <link rel="preconnect" href="https://fonts.bunny.net" />
            <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            
            <div class="flex min-h-screen flex-col items-center lg:justify-center lg:p-8 bg-background">
                <NavBar />
      <main>{props.children}</main>
 
            </div>
        </>
    );
}

export default GuestLayout
