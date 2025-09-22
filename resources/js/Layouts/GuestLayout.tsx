import { JSX } from "solid-js"
import { Title } from "@solidjs/meta"

interface GuestLayoutProps {
    title?: string;
    children?: JSX.Element;
}

function GuestLayout(props: GuestLayoutProps) {
    return (
        <>
            <Title>{props.title ? `${props.title} - Laravel` : 'Laravel'}</Title>
            <link rel="preconnect" href="https://fonts.bunny.net" />
            <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            
            <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
                {props.children}
            </div>
        </>
    );
}

export default GuestLayout
