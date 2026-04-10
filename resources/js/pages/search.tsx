import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useLang } from '@/hooks/useLang';
import AppLayout from '@/layouts/app-layout';
import { game } from '@/routes';
import { type Game } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ImageOff, Loader2, Search, Star } from 'lucide-react';
import React, { useCallback, useEffect, useRef, useState } from 'react';

interface SearchPageProps {
    results: {
        data: Game[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        next_page_url: string | null;
    };
    query: string;
}

function SearchPage({ results: initialResults, query: initialQuery }: SearchPageProps) {
    const { t } = useLang();
    const { url } = usePage();
    const [query, setQuery] = useState(initialQuery || '');
    const [searchResults, setSearchResults] = useState(initialResults?.data || []);
    const [currentPage, setCurrentPage] = useState(initialResults?.current_page || 1);
    const [hasMore, setHasMore] = useState(initialResults?.next_page_url !== null);
    const [isLoading, setIsLoading] = useState(false);
    const [isFetching, setIsFetching] = useState(false);
    const observerTarget = useRef<HTMLDivElement>(null);

    // Handle search input
    const handleSearch = useCallback(
        (e: React.FormEvent) => {
            e.preventDefault();
            if (query.trim()) {
                router.get(
                    url,
                    { q: query },
                    {
                        preserveState: false,
                        preserveScroll: false,
                    }
                );
            }
        },
        [query, url]
    );

    // Load more results
    const loadMore = useCallback(async () => {
        if (isFetching || !hasMore || !initialResults?.next_page_url) return;

        setIsFetching(true);
        try {
            const nextPage = currentPage + 1;
            const response = await fetch(
                `${url}?q=${encodeURIComponent(initialQuery)}&page=${nextPage}`,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                }
            );

            const data = await response.json();

            if (data.results?.data) {
                setSearchResults((prev) => [...prev, ...data.results.data]);
                setCurrentPage(data.results.current_page);
                setHasMore(data.results.next_page_url !== null);
            }
        } catch (error) {
            console.error('Error loading more results:', error);
        } finally {
            setIsFetching(false);
        }
    }, [isFetching, hasMore, currentPage, url, initialQuery, initialResults?.next_page_url]);

    // Intersection Observer for infinite scroll
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && hasMore && !isFetching) {
                    loadMore();
                }
            },
            { threshold: 0.1 }
        );

        const currentTarget = observerTarget.current;
        if (currentTarget) {
            observer.observe(currentTarget);
        }

        return () => {
            if (currentTarget) {
                observer.unobserve(currentTarget);
            }
        };
    }, [hasMore, isFetching, loadMore]);

    // Reset results when props change
    useEffect(() => {
        setSearchResults(initialResults?.data || []);
        setCurrentPage(initialResults?.current_page || 1);
        setHasMore(initialResults?.next_page_url !== null);
    }, [initialResults]);

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
                    {initialQuery && (
                        <p className="mt-4 text-sm text-muted-foreground">
                            {initialResults?.total
                                ? `${initialResults.total} ${t('search.results_found') || 'results found'} for "${initialQuery}"`
                                : `${t('search.no_results') || 'No results found'} for "${initialQuery}"`}
                        </p>
                    )}
                </div>

                {/* Results Grid */}
                {searchResults.length > 0 ? (
                    <div className="space-y-4">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {searchResults.map((gameData) => (
                                <Card
                                    key={gameData.id}
                                    className="overflow-hidden transition-shadow hover:shadow-lg cursor-pointer"
                                    onClick={() => router.visit(game.get(gameData.id).url)}
                                >
                                    <div className="aspect-video w-full overflow-hidden bg-muted">
                                        {gameData.image_url ? (
                                            <img
                                                src={gameData.image_url}
                                                alt={gameData.name}
                                                className="h-full w-full object-cover transition-transform hover:scale-105"
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center">
                                                <ImageOff className="h-16 w-16 text-muted-foreground" />
                                            </div>
                                        )}
                                    </div>
                                    <CardContent className="p-4">
                                        <h3 className="mb-2 line-clamp-1 text-lg font-semibold">
                                            {gameData.name}
                                        </h3>

                                        <div className="mb-3 flex items-center gap-2">
                                            <div className="flex items-center gap-1">
                                                <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                <span className="text-sm font-medium">
                                                    {gameData.rating?.toFixed(1) || 'N/A'}
                                                </span>
                                            </div>
                                            {gameData.pub_year && (
                                                <Badge variant="outline" className="text-xs">
                                                    {gameData.pub_year}
                                                </Badge>
                                            )}
                                        </div>

                                        <div className="flex flex-wrap gap-2 text-xs text-muted-foreground">
                                            {gameData.min_players && gameData.max_players && (
                                                <span>
                                                    👥 {gameData.min_players}-{gameData.max_players} {t('search.players') || 'players'}
                                                </span>
                                            )}
                                            {gameData.box_time && (
                                                <span>
                                                    ⏱️ {gameData.box_time} {t('search.min') || 'min'}
                                                </span>
                                            )}
                                        </div>

                                        {gameData.designers && gameData.designers.length > 0 && (
                                            <p className="mt-2 line-clamp-1 text-xs text-muted-foreground">
                                                {t('search.by') || 'By'} {gameData.designers.slice(0, 2).join(', ')}
                                            </p>
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        {/* Loading indicator for infinite scroll */}
                        <div ref={observerTarget} className="py-8 text-center">
                            {isFetching && (
                                <div className="flex items-center justify-center gap-2">
                                    <Loader2 className="h-6 w-6 animate-spin text-primary" />
                                    <span className="text-muted-foreground">
                                        {t('search.loading_more') || 'Loading more results...'}
                                    </span>
                                </div>
                            )}
                            {!hasMore && searchResults.length > 0 && (
                                <p className="text-muted-foreground">
                                    {t('search.no_more_results') || 'No more results to load'}
                                </p>
                            )}
                        </div>
                    </div>
                ) : (
                    initialQuery && (
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
