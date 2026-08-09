import GameController from '@/actions/App/Http/Controllers/GameController';
import CommentManager from '@/components/CommentManager';
import Loading from '@/components/loading';
import { RatingList } from '@/components/RatingList';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { VersionTable } from '@/components/version-table';
import { useBreadcrumbContext } from '@/hooks/useBreadcrumbs';
import { useLang } from '@/hooks/useLang';
import AppLayout from '@/layouts/app-layout';
import { fetchJson } from '@/lib/fetch-json';
import { game_internal, home } from '@/routes';
import { type Game } from '@/types';
import { usePage } from '@inertiajs/react';
import { Calendar, Clock, ImageOff, Images, Star, Users } from 'lucide-react';
import React, { useCallback, useEffect, useMemo, useState } from 'react';

function Game({ id }: { id: string }) {
    const { url } = usePage();
    const { t, locale } = useLang();
    const { setBreadcrumbs } = useBreadcrumbContext();
    const keepShort = useCallback(
        (arr: string[] | undefined, empty_string: string) => {
            if (!arr) return empty_string;
            if (arr.length > 3)
                return `${arr.slice(0, 2).join(', ')} ${t('game.many_others')}`;
            return arr.join(', ');
        },
        [t],
    );
    const [gameData, setGameData] = useState<Game>();
    const [versionsData, setVersionsData] = useState<Game[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [loadFailed, setLoadFailed] = useState(false);

    const shortDesigners: string = useMemo(
        () => keepShort(gameData?.designers, t('game.no_designer')),
        [gameData?.designers, keepShort, t],
    );
    const shortPublishers: string = useMemo(
        () => keepShort(gameData?.publishers, t('game.no_publisher')),
        [gameData?.publishers, keepShort, t],
    );

    const languages = useMemo(
        () => [
            ...new Set(
                versionsData.flatMap((x) =>
                    (x.languages ?? []).map((y) => y.code),
                ),
            ),
        ],
        [versionsData],
    );

    useEffect(() => {
        const original = [
            { title: t('game.game'), href: home.url() },
            { title: gameData?.name ?? t('game.loading'), href: url },
        ];
        // Remove automatic parent insertion to prevent version views from defaulting to parent data
        setBreadcrumbs(original);
    }, [gameData, setBreadcrumbs, t, url]);

    // Fetches the api
    useEffect(() => {
        setIsLoading(true);
        setLoadFailed(false);
        fetchJson<{ game: Game; versions: Game[] }>(GameController.get(id).url)
            .then((data) => {
                setGameData(data.game);
                setVersionsData(Array.isArray(data.versions) ? data.versions : []);
            })
            .catch((error) => {
                console.error('Failed to load game', error);
                setGameData(undefined);
                setVersionsData([]);
                setLoadFailed(true);
            })
            .finally(() => setIsLoading(false));
    }, [id]);

    if (isLoading) {
        return <Loading />;
    }

    if (loadFailed || !gameData) {
        return (
            <div className="container mx-auto px-4 py-16 text-center text-muted-foreground">
                {t('menu.load_failed')}
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-background">
            <div className="bg-muted/30">
                <div className="container mx-auto px-4 py-8">
                    <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                        {/* Game Image */}
                        <div className="lg:col-span-1">
                            <div className="aspect-square overflow-hidden rounded-lg">
                                {gameData.image_url ? (
                                    <img
                                        src={gameData.image_url}
                                        alt={gameData.name}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex size-full items-center justify-center">
                                            <ImageOff className="size-64 stroke-secondary block" />
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Game Info */}
                        <div className="lg:col-span-2">
                            <div className="mb-4 flex items-start justify-between">
                                <div>
                                    <h1 className="mb-2 text-3xl font-bold">
                                        {gameData.name}
                                    </h1>
                                    <p className="mb-4 text-muted-foreground">
                                        {`${t('game.by')} ${shortDesigners} • `}
                                        {`${shortPublishers} • ${gameData.pub_year || '-'}`}
                                    </p>
                                </div>
                                <Badge
                                    variant="outline"
                                    className="px-3 py-1 text-lg"
                                >
                                    {languages
                                        .map((lang) =>
                                            lang.toLocaleUpperCase(locale),
                                        )
                                        .join(', ')}
                                </Badge>
                            </div>

                            {/* Rating Overview */}
                            <div className="mb-6 flex items-center gap-6">
                                <div className="flex items-center gap-2">
                                    <div className="flex items-center">
                                        {[...Array(5)].map((_, i) => (
                                            <Star
                                                key={i}
                                                className={`h-6 w-6 ${
                                                    i <
                                                    Math.floor(
                                                        gameData.rating ?? 0,
                                                    )
                                                        ? 'fill-yellow-400 text-yellow-400'
                                                        : 'text-muted-foreground'
                                                }`}
                                            />
                                        ))}
                                    </div>
                                    <span className="text-2xl font-bold">
                                        {gameData.rating == 0
                                            ? t('game.no_review')
                                            : gameData.rating}
                                    </span>
                                </div>
                                <div className="text-muted-foreground">
                                    {t('game.reviews')}
                                </div>
                            </div>

                            {/* Game Stats */}
                            <div className="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                                <div className="rounded-lg bg-card p-4 text-center">
                                    <Users className="mx-auto mb-2 h-6 w-6 text-primary" />
                                    <div className="font-semibold">
                                        {gameData.min_players} -{' '}
                                        {gameData.max_players}
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        {t('game.players')}
                                    </div>
                                </div>
                                <div className="rounded-lg bg-card p-4 text-center">
                                    <Clock className="mx-auto mb-2 h-6 w-6 text-primary" />
                                    <div className="font-semibold">
                                        {gameData.box_time || '-'}
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        {t('game.playTime')}
                                    </div>
                                </div>
                                <div className="rounded-lg bg-card p-4 text-center">
                                    <Calendar className="mx-auto mb-2 h-6 w-6 text-primary" />
                                    <div className="font-semibold">
                                        {gameData.pub_year || '-'}
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        {t('game.released')}
                                    </div>
                                </div>
                            </div>
                            <div className="flex w-fit items-center justify-around rounded-lg bg-card p-4 text-center gap-4">
                                <Images className="mx-4 size-6 text-primary" />
                                <div className="font-semibold text-muted-foreground">
                                    {t('game.more_pictures')}
                                </div>
                                {gameData.gameSources?.map((gs) => {
                                    const isVersion = !!gameData.parent;
                                    const referralLinks: Record<string, string> = {
                                        'bgg': isVersion
                                            ? `https://boardgamegeek.com/images/version/${gs.external_id}`
                                            : `https://boardgamegeek.com/images/boardgame/${gs.external_id}`,
                                        // Add other sources here as needed:
                                        // 'spielkiste': `https://spielkiste.de/game/${gs.external_id}`,
                                    };

                                    const link = referralLinks[gs.source_slug];
                                    if (!link) return null;

                                    return (
                                        <a
                                            key={gs.source_slug}
                                            className="mx-2 rounded-2xl border border-primary bg-secondary px-4 py-1 font-semibold text-primary-foreground hover:bg-primary"
                                            href={link}
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            {t(`game.on_${gs.source_slug}dotcom`) || `On ${gs.source_slug}`}
                                        </a>
                                    );
                                })}
                            </div>                            {/* User Rating */}
                            {/* user && (
                                        <Card className="mb-6">
                                            <CardHeader>
                                                <CardTitle className="text-lg">
                                                    {t('games.yourRating')}
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div className="flex items-center gap-4">
                                                    <StarRating
                                                        rating={userRating}
                                                        onRatingChange={
                                                            handleRatingChange
                                                        }
                                                        size="lg"
                                                        interactive
                                                    />
                                                    {userRating > 0 && (
                                                        <Button
                                                            variant="outline"
                                                            onClick={() =>
                                                                setShowReviewForm(
                                                                    true,
                                                                )
                                                            }
                                                        >
                                                            <MessageSquare className="mr-2 h-4 w-4" />
                                                            {t(
                                                                'games.addReview',
                                                            )}
                                                        </Button>
                                                    )}
                                                </div>
                                            </CardContent>
                                        </Card>
                                    ) */}
                        </div>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 py-8" data-debug-tabs>
                <Tabs defaultValue="versions" className="space-y-6">
                    <TabsList className="flex w-full">
                        <TabsTrigger value="versions">
                            {gameData.parent
                                ? t('game.parent')
                                : t('game.versions')}
                        </TabsTrigger>
                        <TabsTrigger value="overview">
                            {t('game.overview')}
                        </TabsTrigger>
                        <TabsTrigger value="details">
                            {t('game.details')}
                        </TabsTrigger>
                        <TabsTrigger value="comments">
                            {t('game.comments')}
                        </TabsTrigger>
                        <TabsTrigger value="ratings">
                            {t('game.ratings')}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    className={'rounded bg-white/30 px-2 py-1'}
                                >
                                    {t('game.about_it')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="mb-4 leading-relaxed text-muted-foreground">
                                    {(locale &&
                                        gameData?.descriptions?.[locale]) ||
                                        gameData?.descriptions?.['en'] ||
                                        t('game.missing_translation')}
                                </p>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="comments" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    className={'rounded bg-white/30 px-2 py-1'}
                                >
                                    {t('game.comments')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <CommentManager gameId={id} />
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="ratings" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    className={'rounded bg-white/30 px-2 py-1'}
                                >
                                    {t('game.rating')}
                                </CardTitle>
                                <CardDescription>
                                    {t('game.rating_description')}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    <RatingList gameId={id} />
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    <TabsContent value="details" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    className={'rounded bg-white/30 px-2 py-1'}
                                >
                                    {t('game.details')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div>
                                            <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                {t('game.designer')}
                                            </h4>
                                            <p>
                                                {gameData.designers.join(', ')}
                                            </p>
                                        </div>
                                        <div>
                                            <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                {t('game.publisher')}
                                            </h4>
                                            <p>
                                                {gameData.publishers.join(', ')}
                                            </p>
                                        </div>
                                        <div>
                                            <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                {t('game.artist')}
                                            </h4>
                                            <p>{gameData.artists.join(', ')}</p>
                                        </div>
                                        <div>
                                            <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                {t('game.year_published')}
                                            </h4>
                                            <p>{gameData.pub_year}</p>
                                        </div>
                                        <div>
                                            <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                {t('game.players')}
                                            </h4>
                                            <p>
                                                {gameData.min_players}
                                                {gameData.min_players !==
                                                    gameData.max_players &&
                                                    ` - ${gameData.max_players}`}
                                            </p>
                                        </div>
                                        <div>
                                            <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                {t('game.playing_time')}
                                            </h4>
                                            <p>
                                                {t('game.min')}:{' '}
                                                {gameData.min_time} -{' '}
                                                {t('game.box')}:{' '}
                                                {gameData.box_time} -{' '}
                                                {t('game.max')}:{' '}
                                                {gameData.max_time}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                    <TabsContent value="versions" className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    className={'rounded bg-white/30 px-2 py-1'}
                                >
                                    {gameData.parent
                                        ? t('game.parent')
                                        : t('game.versions')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <VersionTable
                                    versionsData={
                                        gameData.parent
                                            ? [gameData.parent]
                                            : versionsData
                                    }
                                />
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    );
}

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-expect-error
Game.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>;

export default Game;
