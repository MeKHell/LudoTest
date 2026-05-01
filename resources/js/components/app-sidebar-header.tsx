import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { useLang } from '@/hooks/useLang';

export function AppSidebarHeader() {
    const [query, setQuery] = useState('');
    const { t } = useLang();

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (query.trim()) {
            router.get('/search', { q: query });
            setQuery('');
        }
    };

    return (
        <header className="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs />
            </div>
            
            <div className="flex-1 max-w-sm ml-4 hidden md:block">
                <form onSubmit={handleSearch} className="relative">
                    <Search className="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="search"
                        placeholder={t('search.placeholder')}
                        className="pl-9 h-9 bg-muted/50 border-none focus-visible:ring-1 focus-visible:ring-primary"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                    />
                </form>
            </div>
        </header>
    );
}
