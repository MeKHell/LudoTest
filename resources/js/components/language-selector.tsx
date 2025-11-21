import { Globe } from 'lucide-react';
import { Button } from './ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from './ui/dropdown-menu';
import { update } from '@/actions/App/Http/Controllers/SetLang';
import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { useLang } from '@/hooks/useLang';



export function LanguageSelector(props: { className: string, asList?: boolean }) {
    const { locale, languages } = useLang();

    const form = useForm();
    const setLang = (lang: string) => {
        form.setData((old: any) => ({ ...old, lang }));
    }

    useEffect(() => {
        form.submit(update());
    }, [form.data]);

    const otherLanguages = () => languages.filter((x) => x !== locale);

    if (props.asList) {
        return (<div>            {
            otherLanguages().map((locale) => (
                <Button variant="outline" className={props.className} onClick={() => setLang(locale)}>
                    <Globe className="h-5 w-5" /> {locale.toLocaleUpperCase()}
                </Button>
            ))
        }</div>)
    }

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
