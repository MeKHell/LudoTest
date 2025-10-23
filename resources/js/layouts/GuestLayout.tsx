import { NavBar } from '@/components/navbar';
import { Head } from '@inertiajs/react';
import { JSX } from 'react';

interface GuestLayoutProps {
    title?: string;
    children?: JSX.Element;
}

function GuestLayout(props: GuestLayoutProps) {
    return (
        <>
            <Head>
                <title>
                    {props.title ? `${props.title} - LudoTest` : 'LudoTest'}
                </title>
            </Head>
            <link rel="preconnect" href="https://fonts.bunny.net" />
            <link
                href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                rel="stylesheet"
            />

            <div className="flex min-h-screen w-full flex-col items-center bg-background lg:justify-center">
                <NavBar />
                <main className="w-full">{props.children}</main>
            </div>
        </>
    );
}

export default GuestLayout;
