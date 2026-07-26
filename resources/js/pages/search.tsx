import GameController from '@/actions/App/Http/Controllers/GameController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useLang } from '@/hooks/useLang';
import AppLayout from '@/layouts/app-layout';
import { game_internal } from '@/routes';
import { router, usePage } from '@inertiajs/react';
import { Bookmark, BookmarkCheck, Clock, ImageOff, Loader2, Search, Star, Users } from 'lucide-react';
import React, { useCallback, useEffect, useRef, useState } from 'react';

interface SearchResult {
    id: string | number;
    name: string;
    thumb_url: string | null;
    pub_year: number | null;
    is_local: boolean;
    in_user_library: boolean;
    list_types: string[];
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

type TranslateFn = (key: string, replaces?: Record<string, string | number> | string) => string;

function ResultCard({
    result,
    t,
    onAddToLibrary,
}: {
    result: SearchResult;
    t: TranslateFn;
    onAddToLibrary: (e: React.MouseEvent, gameId: number, listType: 'owned' | 'wishlist') => void;
}) {
    return (
        <Card
            className="group cursor-pointer overflow-hidden border-muted-foreground/20 transition-all hover:border-primary/50 hover:shadow-md"
            onClick={() => {
                if (result.is_local) {
                    router.visit(game_internal.url(result.id));
                } else {
                    router.visit(`/game/${result.source}/${result.id}?name=${encodeURIComponent(result.name)}`);
                }
            }}
        >
            <CardContent className="relative flex items-center gap-4 p-4">
                <div className="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded bg-muted">
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

                <div className="min-w-0 flex-1">
                    <div className="mb-1 flex items-center justify-between gap-2">
                        <h3 className="truncate text-lg font-semibold transition-colors group-hover:text-primary">
                            {result.name}
                        </h3>
                        <div className="flex shrink-0 items-center gap-2">
                            {/* Saved in LudoTest DB (not the same as the user's personal library) */}
                            {result.is_local && (
                                <Badge variant="secondary">
                                    {t('search.in_database') || 'In Database'}
                                </Badge>
                            )}
                            {result.list_types.includes('owned') && (
                                <Badge variant="default">
                                    {t('search.owned') || 'Owned'}
                                </Badge>
                            )}
                            {result.list_types.includes('wishlist') && (
                                <Badge variant="outline">
                                    {t('search.wishlist') || 'Wishlist'}
                                </Badge>
                            )}
                            <div
                                className="flex h-6 w-6 items-center justify-center overflow-hidden rounded-full border border-border bg-white p-1 shadow-sm"
                                title={result.source.toUpperCase()}
                            >
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
                                        <Users className="h-3 w-3" />
                                        {result.min_players}
                                        {result.max_players ? `-${result.max_players}` : ''}
                                    </span>
                                )}
                                {result.box_time && (
                                    <span className="flex items-center gap-1">
                                        <Clock className="h-3 w-3" />
                                        {result.box_time} min
                                    </span>
                                )}
                            </>
                        )}
                    </div>

                    {/* Personal library actions — only for games already in the local DB */}
                    {result.is_local && (
                        <div className="mt-2 flex gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={(e) => onAddToLibrary(e, Number(result.id), 'owned')}
                            >
                                {result.list_types.includes('owned') ? (
                                    <BookmarkCheck className="mr-1 h-3 w-3" />
                                ) : (
                                    <Bookmark className="mr-1 h-3 w-3" />
                                )}
                                {t('search.add_owned') || 'Owned'}
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                onClick={(e) => onAddToLibrary(e, Number(result.id), 'wishlist')}
                            >
                                <Star
                                    className={`mr-1 h-3 w-3 ${result.list_types.includes('wishlist') ? 'fill-current' : ''}`}
                                />
                                {t('search.add_wishlist') || 'Wishlist'}
                            </Button>
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

function SearchPage({ query: initialQuery }: SearchPageProps) {
    const { t } = useLang();
    const { url } = usePage();
    const [query, setQuery] = useState(initialQuery || '');
    const [searchResults, setSearchResults] = useState<SearchResult[]>([]);
    const [randomGames, setRandomGames] = useState<SearchResult[]>([]);
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

        const loaderTimer = setTimeout(() => setShowLoader(true), 100); // Delay loader so fast responses don't flash

        try {
            const response = await fetch(
                GameController.search({ query: { q: searchQuery, limit: 50, page: pageNum } }).url,
                { headers: { Accept: 'application/json' } }
            );

            const data = await response.json();
            // Library fields are only present for local games; default them for external hits.
            const normalized = data.map((item: SearchResult) => ({
                ...item,
                in_user_library: item.in_user_library ?? false,
                list_types: item.list_types ?? [],
            }));

            if (pageNum === 1) {
                setSearchResults(normalized);
            } else {
                setSearchResults(prev => [...prev, ...normalized]);
            }

            setHasMore(normalized.length === 50);
        } catch (error) {
            console.error('Error fetching search results:', error);
        } finally {
            clearTimeout(loaderTimer);
            setShowLoader(false);
            setIsLoading(false);
            setIsFetchingMore(false);
        }
    }, []);

    // Add to or remove from owned/wishlist. POST creates, DELETE /library/{game}/{list} removes.
    const addToLibrary = (e: React.MouseEvent, gameId: number, listType: 'owned' | 'wishlist') => {
        e.stopPropagation(); // Don't also open the game page

        const alreadyInList = (results: SearchResult[]) =>
            results.some((r) => r.id === gameId && r.is_local && r.list_types.includes(listType));

        const isRemoving =
            alreadyInList(searchResults) || alreadyInList(randomGames);

        const applyListChange = (r: SearchResult): SearchResult => {
            if (r.id !== gameId || !r.is_local) return r;
            const listTypes = isRemoving
                ? r.list_types.filter((type) => type !== listType)
                : [...new Set([...r.list_types, listType])];
            return {
                ...r,
                list_types: listTypes,
                in_user_library: listTypes.length > 0,
            };
        };

        const options = {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setSearchResults((prev) => prev.map(applyListChange));
                setRandomGames((prev) => prev.map(applyListChange));
            },
        };

        if (isRemoving) {
            router.delete(`/api/library/${gameId}/${listType}`, options);
        } else {
            router.post('/api/library', { game_id: gameId, list_type: listType }, options);
        }
    };

    // Infinite scroll: load the next page when the sentinel nears the viewport
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

    // Re-run search when the ?q= query string changes (including from the form submit)
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

    // When no search is active, show a (server-cached) random pick of local games
    useEffect(() => {
        fetch('/api/random', { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((data: SearchResult[]) =>
                setRandomGames(
                    data.map((item) => ({
                        ...item,
                        in_user_library: item.in_user_library ?? false,
                        list_types: item.list_types ?? [],
                    })),
                ),
            )
            .catch((error) => console.error('Error fetching random games', error));
    }, []);

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
                {/* Search header + form */}
                <div className="mb-8">
                    <h1 className="mb-4 text-3xl font-bold">
                        {t('search.title') || 'Search Games'}
                    </h1>

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

                    {hasSearched && !isLoading && (
                        <p className="mt-4 text-sm text-muted-foreground">
                            {searchResults.length
                                ? `${searchResults.length} ${t('search.results_found') || 'results found'} for "${query}"`
                                : `${t('search.no_results') || 'No results found'} for "${query}"`}
                        </p>
                    )}
                </div>

                {isLoading ? (
                    <div className="flex justify-center py-12">
                        <Loader2 className="h-8 w-8 animate-spin text-primary" />
                    </div>
                ) : searchResults.length > 0 ? (
                    <div className="space-y-4">
                        {searchResults.map((result) => (
                            <ResultCard
                                key={`${result.source}-${result.id}`}
                                result={result}
                                t={t}
                                onAddToLibrary={addToLibrary}
                            />
                        ))}
                        <div ref={observerTarget} className="h-20" />
                        {showLoader && (
                            <div className="flex justify-center py-4">
                                <Loader2 className="h-6 w-6 animate-spin text-primary" />
                            </div>
                        )}
                    </div>
                ) : hasSearched ? (
                    <div className="flex flex-col items-center justify-center py-16">
                        <Search className="mb-4 h-16 w-16 text-muted-foreground" />
                        <h2 className="mb-2 text-xl font-semibold">
                            {t('search.no_results_title') || 'No games found'}
                        </h2>
                        <p className="text-muted-foreground">
                            {t('search.no_results_description') || 'Try adjusting your search terms'}
                        </p>
                    </div>
                ) : (
                    /* No active search: show a random pick from the local database */
                    randomGames.length > 0 && (
                        <div className="space-y-4">
                            <h2 className="text-xl font-semibold">
                                {t('search.discover') || 'Discover games'}
                            </h2>
                            {randomGames.map((result) => (
                                <ResultCard
                                    key={`${result.source}-${result.id}`}
                                    result={result}
                                    t={t}
                                    onAddToLibrary={addToLibrary}
                                />
                            ))}
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
