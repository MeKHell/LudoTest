import { Link } from '@inertiajs/react';
import { Menu, Spade } from 'lucide-react';
import { JSX, useEffect, useState } from 'react';
import { LanguageSelector } from './language-selector';
import { ThemeToggle } from './theme-toggle';
import { Button } from './ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from './ui/dropdown-menu';

export function NavBar() {
    const content: {
        url: string;
        element: JSX.Element;
        additional_style?: string;
    }[] = [
        {
            url: '/',
            element: <div>home</div>,
            additional_style:
                'text-foreground h-full rounded hover:bg-accent transition-colors text-lg px-2',
        },
        {
            url: '/games',
            element: <div>games</div>,
            additional_style:
                'text-foreground h-full rounded hover:bg-accent transition-colors text-lg px-2',
        },
        {
            url: '/login',
            element: (
                <div className="flex justify-between">
                    <Button
                        variant="outline"
                        size="sm"
                        className="text-md mx-2"
                    >
                        login
                    </Button>
                    <div className="flex md:hidden">
                        <ThemeToggle />
                        <LanguageSelector className="ml-1" />
                    </div>
                </div>
            ),
        },
    ];

    const [show, setShow] = useState<boolean>(true);
    const [lastScrollY, setLastScrollY] = useState<number>(0);

    useEffect(() => {
        const controlNavbar = () => {
            if (window.scrollY > lastScrollY) {
                setShow(() => false);
            } else {
                setShow(() => true);
            }
            setLastScrollY(() => window.scrollY);
        };
        window.addEventListener('scroll', controlNavbar);
        return () => window.removeEventListener('scroll', controlNavbar);
    }, [lastScrollY]);

    return (
        <div
            className={`${show ? 'translate-y-0' : '-translate-y-full'} fixed top-0 z-50 h-14 w-full border-b bg-card/50 backdrop-blur-sm duration-300`}
        >
            <div className="container mx-auto h-full max-w-full flex-nowrap px-4">
                <div className="flex h-full justify-between">
                    <div className="flex h-full grow-0 items-center">
                        <Spade className="h-8 w-8 text-primary" />
                        <h1 className="ml-1 text-2xl font-bold text-primary">
                            LudoTest
                        </h1>
                    </div>

                    <nav className="flex h-full max-w-lg grow items-stretch px-3">
                        <div className="hidden h-full shrink-0 grow flex-nowrap items-stretch justify-around md:flex">
                            {content.map((elem) => (
                                <Link
                                    as="a"
                                    key={elem.url}
                                    href={elem.url}
                                    className={`flex grow items-center justify-center ${elem.additional_style}`}
                                >
                                    {elem.element}
                                </Link>
                            ))}
                        </div>
                    </nav>
                    <div className="flex grow-0 items-center justify-between">
                        <nav className="flex h-full items-center md:hidden">
                            <DropdownMenu>
                                <DropdownMenuTrigger>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        className="text-md mx-2"
                                    >
                                        <Menu className="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent className="w-screen">
                                    {content.map((elem) => (
                                        <DropdownMenuItem
                                            key={elem.url}
                                            className="w-full"
                                        >
                                            <Link
                                                as="a"
                                                href={elem.url}
                                                className={`w-full`}
                                            >
                                                {elem.element}
                                            </Link>
                                        </DropdownMenuItem>
                                    ))}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </nav>

                        <ThemeToggle className="hidden md:flex" />
                        <LanguageSelector className="ml-1 hidden md:flex" />
                    </div>
                </div>
            </div>
        </div>
    );
}
