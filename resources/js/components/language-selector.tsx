import { Button } from './ui/button';
import { update } from '@/actions/App/Http/Controllers/SetLang';
import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { useLang } from '@/hooks/useLang';



export function LanguageSelector() {
    const { locale, languages } = useLang();

    const form = useForm({lang: ''});
    const setLang = (lang: string) => {
        form.setData(() => ({ lang }));
    }


    useEffect(() => {
        form.submit(update());
    }, [form.data]);

    return (
        <div className="flex">
            {languages.map((lang) => (
                <Button
                    variant={lang === locale ? 'secondary' : 'outline'}
                    onClick={() => {
                        if (lang !== locale){
                            setLang(lang);
                    }}}
                    className='mr-1'
                >
                    {lang.toLocaleUpperCase()}
                </Button>
            ))}
        </div>
    );
}
