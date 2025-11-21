import { Link, usePage } from '@inertiajs/react';
import { ChevronsUpDown, Menu, Spade } from 'lucide-react';
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
import { SharedData } from '@/types';
import { UserMenuContent } from './user-menu-content';
import { UserInfo } from './user-info';
import { useLang } from '@/hooks/useLang';

export function NavBar() {
    const { auth } = usePage<SharedData>().props;
    const { t } = useLang();

    const content: {
        url: string;
        name: string;
        additional_style?: string;
    }[] = [
            {
                url: '/',
                name: t("menu.home"),
                additional_style:
                    '',
            },
            {
                url: '/games',
                name: t("menu.games"),
                additional_style:
                    '',
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
            <div className="container mx-auto h-full max-w-11/12 flex-nowrap px-4">
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
                                    <div className='</div>'> {elem.name} </div>
                                </Link>
                            ))}
                        </div>
                    </nav>
                    <div className="flex grow-0 items-center justify-between">


                        <DropdownMenu modal={false}>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="lg"
                                    className="min-w-48 group text-sidebar-accent-foreground"
                                    data-test="sidebar-menu-button"
                                >
                                    <UserInfo user={auth.user} />
                                    <ChevronsUpDown className="ml-auto size-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                className=" min-w-56 rounded-lg"
                                align="start"
                                side='bottom'
                            >
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <nav className="flex ml-2 h-full items-center md:hidden">
                            <DropdownMenu modal={false}>
                                <DropdownMenuTrigger>
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        className="text-md mx-2"
                                    >
                                        <Menu className="h-5 w-5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent className="min-w-40" side="bottom" align="end">
                                    {content.map((elem) => (
                                        <DropdownMenuItem
                                            key={elem.url}
                                            className="text-lg"
                                        >
                                            <Link
                                                as="a"
                                                href={elem.url}
                                                className={`w-full`}
                                            >
                                                <div className='ml-1 text-foreground flex rounded hover:bg-accent transition-colors text-lg font-semibold'>{elem.name} </div>
                                            </Link>
                                        </DropdownMenuItem>
                                    ))}

                                    <DropdownMenuItem >
                                        <LanguageSelector className="ml-1 flex" asList />
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </nav>

                        <ThemeToggle className="ml-1 hidden md:flex" />
                        <LanguageSelector className="ml-1 hidden md:flex" />
                    </div>
                </div>
            </div >
        </div >
    );
}
