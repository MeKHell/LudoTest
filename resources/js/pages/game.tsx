import GameController from '@/actions/App/Http/Controllers/GameController';
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
import { useLang } from '@/hooks/useLang';
import AppLayout from '@/layouts/app-layout';
import { game, home } from '@/routes';
import { type Game } from '@/types';
import { usePage } from '@inertiajs/react';
import { Calendar, Clock, LoaderCircle, Star, Users } from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';

export default function Game({ id }: { id: string }) {
    const { url } = usePage();

    const { t, locale } = useLang();
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
                versionsData.flatMap((x) => x.languages.map((y) => y.code)),
            ),
        ],
        [versionsData],
    );

    // eslint-disable-next-line react-hooks/preserve-manual-memoization
    const breadcrumbs = useMemo(() => {
        const original = [
            { title: t('game.game'), href: home.url() },
            { title: gameData?.name ?? t('game.loading'), href: url },
        ];
        if (gameData?.parent) {
            original.splice(1, 0, {
                title: gameData.parent.name,
                href: game.get(gameData.parent.bgge_id).url,
            });
        }
        return original;
    }, [gameData?.name, gameData?.parent, t, url]);

    // Fetches the api
    useEffect(() => {
        (async () => {
            const data: { game: Game; versions: Game[] } = await fetch(
                GameController.get(id).url,
            ).then((res) => res.json());
            setGameData(() => data.game);
            setVersionsData(() => data.versions);
        })();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            {gameData ? (
                <div className="min-h-screen bg-background">
                    <div className="bg-muted/30">
                        <div className="container mx-auto px-4 py-8">
                            <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
                                {/* Game Image */}
                                <div className="lg:col-span-1">
                                    <div className="aspect-square overflow-hidden rounded-lg">
                                        <img
                                            src={gameData.image_url}
                                            alt={gameData.name}
                                            className="h-full w-full object-cover"
                                        />
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
                                            {languages.join(', ')}
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
                                                            i < Math.floor(3.2)
                                                                ? 'fill-yellow-400 text-yellow-400'
                                                                : 'text-muted-foreground'
                                                        }`}
                                                    />
                                                ))}
                                            </div>
                                            <span className="text-2xl font-bold">
                                                {3.2}
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
                                                {gameData.box_time}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {t('game.playTime')}
                                            </div>
                                        </div>
                                        <div className="rounded-lg bg-card p-4 text-center">
                                            <Calendar className="mx-auto mb-2 h-6 w-6 text-primary" />
                                            <div className="font-semibold">
                                                {gameData.pub_year}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {t('game.released')}
                                            </div>
                                        </div>
                                    </div>

                                    {/* User Rating */}
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

                                    <p className="leading-relaxed text-muted-foreground">
                                        {gameData.descriptions[locale]}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        className="container mx-auto px-4 py-8"
                        data-debug-tabs
                    >
                        <Tabs defaultValue="versions" className="space-y-6">
                            <TabsList className="grid w-full grid-cols-4">
                                <TabsTrigger value="versions">
                                    {t('game.versions')}
                                </TabsTrigger>
                                <TabsTrigger value="overview">
                                    {t('game.overview')}
                                </TabsTrigger>
                                <TabsTrigger value="details">
                                    {t('game.details')}
                                </TabsTrigger>
                                <TabsTrigger value="reviews">
                                    {t('game.comments')}
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="overview" className="space-y-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
                                            {t('game.about_it')}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="mb-4 leading-relaxed text-muted-foreground">
                                            {gameData.descriptions[locale] ??
                                                gameData.descriptions['EN']}
                                        </p>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="reviews" className="space-y-6">
                                {/* showReviewForm && user && (
                                    <ReviewForm
                                        gameId={game.id}
                                        gameTitle={game.title}
                                        userRating={userRating}
                                        onClose={() => setShowReviewForm(false)}
                                        onSubmit={() =>
                                            setShowReviewForm(false)
                                        }
                                    />
                                )*/}
                            </TabsContent>

                            <TabsContent value="ratings" className="space-y-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
                                            {t('game.rating')}
                                        </CardTitle>
                                        <CardDescription>
                                            How users have rated this game
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-3">
                                            {/*ratingDistribution.map((item) => (
                                                <div
                                                    key={item.stars}
                                                    className="flex items-center gap-4"
                                                >
                                                    <div className="flex w-16 items-center gap-1">
                                                        <span className="text-sm font-medium">
                                                            {item.stars}
                                                        </span>
                                                        <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    </div>
                                                    <div className="h-2 flex-1 rounded-full bg-muted">
                                                        <div
                                                            className="h-2 rounded-full bg-primary transition-all"
                                                            style={{
                                                                width: `${item.percentage}%`,
                                                            }}
                                                        />
                                                    </div>
                                                    <div className="w-16 text-right text-sm text-muted-foreground">
                                                        {item.count} (
                                                        {item.percentage}%)
                                                    </div>
                                                </div>
                                            ))*/}
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="details" className="space-y-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>
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
                                                        {gameData.designers.join(
                                                            ', ',
                                                        )}
                                                    </p>
                                                </div>
                                                <div>
                                                    <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                        {t('game.publisher')}
                                                    </h4>
                                                    <p>
                                                        {gameData.publishers.join(
                                                            ', ',
                                                        )}
                                                    </p>
                                                </div>
                                                <div>
                                                    <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                        {t('game.artist')}
                                                    </h4>
                                                    <p>
                                                        {gameData.artists.join(
                                                            ', ',
                                                        )}
                                                    </p>
                                                </div>
                                                <div>
                                                    <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                        {t(
                                                            'game.year_published',
                                                        )}
                                                    </h4>
                                                    <p>{gameData.pub_year}</p>
                                                </div>
                                                <div>
                                                    <h4 className="mb-1 text-sm font-semibold text-muted-foreground">
                                                        {t('game.players')}
                                                    </h4>
                                                    <p>
                                                        {gameData.min_players} -{' '}
                                                        {gameData.max_players}
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
                                        <CardTitle>
                                            {t('game.versions')}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <VersionTable
                                            versionsData={versionsData}
                                        />
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </div>
                </div>
            ) : (
                <div className="flex w-full justify-around">
                    <div className="text-xl font-semibold">
                        <LoaderCircle className="mt-10 mr-3 mb-5 size-20 animate-spin" />
                        Loading
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
