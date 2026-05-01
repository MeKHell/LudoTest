import GameController from '@/actions/App/Http/Controllers/GameController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useLang } from '@/hooks/useLang';
import AppLayout from '@/layouts/app-layout';
import { game_internal } from '@/routes';
import { router, usePage } from '@inertiajs/react';
import { Clock, ImageOff, Loader2, Search, Star, Users } from 'lucide-react';
import React, { useCallback, useEffect, useRef, useState } from 'react';

interface SearchResult {
    id: string | number;
    name: string;
    thumb_url: string | null;
    pub_year: number | null;
    is_local: boolean;
    source: string;
    external_id: string | null;
    score: number;
    min_players: number | null;
    max_players: number | null;
    box_time: number | null;
}

interface SearchPageProps {
    query: string;
}

function SearchPage({ query: initialQuery }: SearchPageProps) {
    const { t } = useLang();
    const { url } = usePage();
    const [query, setQuery] = useState(initialQuery || '');
    const [searchResults, setSearchResults] = useState<SearchResult[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [hasSearched, setHasSearched] = useState(false);
    const [isFetchingMore, setIsFetchingMore] = useState(false);
    const [hasMore, setHasMore] = useState(true);
    const [page, setPage] = useState(1);
    const [showLoader, setShowLoader] = useState(false);
    const observerTarget = useRef<HTMLDivElement>(null);

    const performSearch = useCallback(async (searchQuery: string, pageNum: number = 1) => {
        if (!searchQuery.trim()) return;

        if (pageNum === 1) {
            setIsLoading(true);
            setHasSearched(true);
        } else {
            setIsFetchingMore(true);
        }

        // Add 0.1s delay to loader visibility
        const loaderTimer = setTimeout(() => setShowLoader(true), 100);

        try {
            const response = await fetch(
                GameController.search({ query: { q: searchQuery, limit: 50, page: pageNum } }).url,
                { headers: { Accept: 'application/json' } }
            );

            const data = await response.json();
            
            if (pageNum === 1) {
                setSearchResults(data);
            } else {
                setSearchResults(prev => [...prev, ...data]);
            }
            
            setHasMore(data.length === 50);
        } catch (error) {
            console.error('Error fetching search results:', error);
        } finally {
            clearTimeout(loaderTimer);
            setShowLoader(false);
            setIsLoading(false);
            setIsFetchingMore(false);
        }
    }, []);

    // Intersection Observer for infinite scroll (3 rows trigger)
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && hasMore && !isFetchingMore) {
                    const nextPage = page + 1;
                    setPage(nextPage);
                    performSearch(query, nextPage);
                }
            },
            { threshold: 0.1, rootMargin: '200px' }
        );

        const currentTarget = observerTarget.current;
        if (currentTarget) observer.observe(currentTarget);

        return () => {
            if (currentTarget) observer.unobserve(currentTarget);
        };
    }, [hasMore, isFetchingMore, page, query, performSearch]);

    // Trigger search when query in URL changes
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const q = params.get('q');
        if (q) {
            setQuery(q);
            setPage(1);
            performSearch(q, 1);
        } else {
            setSearchResults([]);
            setHasSearched(false);
        }
    }, [performSearch, url]);

    // Handle search input
    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (query.trim()) {
            router.get(
                '/search',
                { q: query },
                {
                    preserveState: true,
                    preserveScroll: true,
                }
            );
        }
    };

    return (
        <div className="min-h-screen bg-background">
            <div className="container mx-auto px-4 py-8">
                {/* Search Header */}
                <div className="mb-8">
                    <h1 className="mb-4 text-3xl font-bold">
                        {t('search.title') || 'Search Games'}
                    </h1>

                    {/* Search Form */}
                    <form onSubmit={handleSearch} className="flex gap-2">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder={t('search.placeholder') || 'Search for games...'}
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                className="pl-10"
                                autoFocus
                            />
                        </div>
                        <Button type="submit" disabled={isLoading}>
                            {isLoading ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                t('search.search') || 'Search'
                            )}
                        </Button>
                    </form>

                    {/* Results Count */}
                    {hasSearched && !isLoading && (
                        <p className="mt-4 text-sm text-muted-foreground">
                            {searchResults.length
                                ? `${searchResults.length} ${t('search.results_found') || 'results found'} for "${query}"`
                                : `${t('search.no_results') || 'No results found'} for "${query}"`}
                        </p>
                    )}
                </div>

                {/* Results Grid */}
                {isLoading ? (
                    <div className="flex justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-primary" />
                    </div>
                ) : searchResults.length > 0 ? (
                    <div className="space-y-4">
                        {searchResults.map((result) => (
                            <Card
                                key={`${result.source}-${result.id}`}
                                className="group overflow-hidden transition-all hover:shadow-md cursor-pointer border-muted-foreground/20 hover:border-primary/50"
                                onClick={() => {
                                    if (result.is_local) {
                                        router.visit(game_internal.url(result.id));
                                    } else {
                                        router.visit(`/game/${result.source}/${result.id}?name=${encodeURIComponent(result.name)}`);
                                    }
                                }}
                            >
                                <CardContent className="p-4 flex gap-4 items-center relative">
                                    <div className="h-20 w-20 shrink-0 overflow-hidden rounded bg-muted flex items-center justify-center">
                                        {result.thumb_url ? (
                                            <img
                                                src={result.thumb_url}
                                                alt={result.name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <ImageOff className="h-8 w-8 text-muted-foreground" />
                                        )}
                                    </div>
                                    
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center justify-between gap-2 mb-1">
                                            <h3 className="text-lg font-semibold truncate group-hover:text-primary transition-colors">
                                                {result.name}
                                            </h3>
                                            <div className="flex items-center gap-2 shrink-0">
                                                {result.is_local && (
                                                    <Badge variant="secondary">
                                                        <Star className="w-3 h-3 mr-1 fill-yellow-400 text-yellow-400" />
                                                        {t('search.in_library') || 'In Library'}
                                                    </Badge>
                                                )}
                                                <div className="h-6 w-6 rounded-full overflow-hidden border border-border bg-white p-1 flex items-center justify-center shadow-sm" title={result.source.toUpperCase()}>
                                                    {result.source === 'local' ? (
                                                        <img src="/favicon.svg" alt="LudoTest" className="h-full w-full object-contain" />
                                                    ) : result.source === 'bgg' || result.source === 'bggv' ? (
                                                        <img src="https://boardgamegeek.com/favicon.ico" alt="BGG" className="h-full w-full object-contain" />
                                                    ) : (
                                                        <span className="text-[10px] font-bold">{result.source[0].toUpperCase()}</span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-3 text-sm text-muted-foreground">
                                            {result.pub_year && <span>{result.pub_year}</span>}
                                            <span className="text-muted-foreground/50">•</span>
                                            <span>{result.source.toUpperCase()}</span>
                                            
                                            {(result.min_players || result.box_time) && (
                                                <>
                                                    <span className="text-muted-foreground/50">•</span>
                                                    {result.min_players && (
                                                        <span className="flex items-center gap-1">
                                                            <Users className="w-3 h-3" />
                                                            {result.min_players}{result.max_players ? `-${result.max_players}` : ''}
                                                        </span>
                                                    )}
                                                    {result.box_time && (
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="w-3 h-3" />
                                                            {result.box_time} min
                                                        </span>
                                                    )}
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                        <div ref={observerTarget} className="h-20" />
                        {showLoader && (
                            <div className="flex justify-center py-4">
                                <Loader2 className="h-6 w-6 animate-spin text-primary" />
                            </div>
                        )}
                    </div>
                ) : (
                    hasSearched && !isLoading && (
                        <div className="flex flex-col items-center justify-center py-16">
                            <Search className="mb-4 h-16 w-16 text-muted-foreground" />
                            <h2 className="mb-2 text-xl font-semibold">
                                {t('search.no_results_title') || 'No games found'}
                            </h2>
                            <p className="text-muted-foreground">
                                {t('search.no_results_description') || 'Try adjusting your search terms'}
                            </p>
                        </div>
                    )
                )}
            </div>
        </div>
    );
}

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-expect-error
SearchPage.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>;

export default SearchPage;

