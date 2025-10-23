import { Globe } from 'lucide-react';
import { Button } from './ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from './ui/dropdown-menu';

export function LanguageSelector(props: { className: string }) {
    const setLang = (lang: string) => {
        console.log(lang);
    };
    const otherLanguages = () => ['de', 'fr', 'en'];
    return (
        <div className={props.className}>
            <DropdownMenu modal={false}>
                <DropdownMenuTrigger>
                    <Button variant="outline" size="icon" {...props}>
                        <Globe className="h-5 w-5" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="min-w-fit" align="start">
                    {otherLanguages().map((locale) => (
                        <DropdownMenuItem onClick={() => setLang(locale)}>
                            {locale.toLocaleUpperCase()}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
