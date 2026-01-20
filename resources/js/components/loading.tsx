import { ReactNode } from 'react';
import { LoaderCircle } from 'lucide-react';
import { useLang } from '@/hooks/useLang';

export default function Loading(): ReactNode{
    const {t} = useLang();
    return (
        <div className="flex w-full justify-around">
            <div className="text-xl font-semibold">
                <LoaderCircle className="mt-10 mr-3 mb-5 size-20 animate-spin" />
                {t('menu.loading')}
            </div>
        </div>
    );
}
