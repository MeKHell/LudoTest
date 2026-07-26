import Loading from '@/components/loading';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { useLang } from '@/hooks/useLang';
import AppLayout from '@/layouts/app-layout';
import { game_internal } from '@/routes';
import { Question } from '@/types';
import { router, Head } from '@inertiajs/react';
import { ArrowRight, Clock, HelpCircle, ImageOff, Search, Spade, Star } from 'lucide-react';
import React, { useEffect, useState } from 'react';

interface WelcomeGame {
    id: number;
    name: string;
    thumb_url: string | null;
}

interface TopGame extends WelcomeGame {
    average_score: number;
    votes_count: number;
}

interface WelcomeQuestion extends Question {
    top_games: TopGame[];
}

function GameCard({
    game,
    onClick,
    rank,
    score,
}: {
    game: WelcomeGame;
    onClick: () => void;
    rank?: number;
    score?: number;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="group w-40 shrink-0 overflow-hidden rounded-lg border bg-card text-left transition-shadow hover:shadow-md sm:w-auto"
        >
            <div className="relative aspect-square overflow-hidden bg-muted">
                {game.thumb_url ? (
                    <img
                        src={game.thumb_url}
                        alt={game.name}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center">
                        <ImageOff className="h-10 w-10 text-muted-foreground" />
                    </div>
                )}
                {rank !== undefined && (
                    <div className="absolute top-2 left-2 flex h-7 w-7 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                        {rank}
                    </div>
                )}
                {score !== undefined && (
                    <div className="absolute right-2 bottom-2 flex items-center gap-1 rounded-full bg-black/75 px-2 py-1 text-xs font-semibold text-white">
                        <Star className="h-3 w-3 fill-current" />
                        {score.toFixed(1)}
                    </div>
                )}
            </div>
            <div className="p-3">
                <p className="line-clamp-2 text-sm font-medium">{game.name}</p>
            </div>
        </button>
    );
}

function Welcome() {
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [recentGames, setRecentGames] = useState<WelcomeGame[]>([]);
    const [questions, setQuestions] = useState<WelcomeQuestion[]>([]);
    const [isLoadingHistory, setIsLoadingHistory] = useState(true);
    const [isLoadingQuestions, setIsLoadingQuestions] = useState(true);
    const { t, locale } = useLang();

    useEffect(() => {
        fetch('/api/latest', { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((data: WelcomeGame[]) => setRecentGames(data))
            .catch((error) => console.error('Failed to load recent games', error))
            .finally(() => setIsLoadingHistory(false));

        fetch('/api/questions', { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((data) => setQuestions(data.questions ?? []))
            .catch((error) => console.error('Failed to load questions', error))
            .finally(() => setIsLoadingQuestions(false));
    }, []);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            router.get('/search', { q: searchQuery });
        }
    };

    const visitGame = (gameId: number) => {
        router.visit(game_internal.url(gameId));
    };

    return (
        <div className="min-h-screen bg-background">
            <Head title="LudoTest" />
            <section className="px-4 py-20">
                <div className="container mx-auto text-center">
                    <h1 className="mb-6 text-4xl font-bold text-balance md:text-6xl">
                        {t('welcome.title')}
                    </h1>
                    <p className="mx-auto mb-8 max-w-2xl text-xl text-balance text-muted-foreground">
                        {t('welcome.subtitle')}
                    </p>

                    <form onSubmit={handleSearch} className="mx-auto mb-12 max-w-md">
                        <div className="relative flex gap-2">
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                <Input
                                    type="text"
                                    placeholder={t('welcome.placeholder')}
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="h-12 pl-10 text-lg"
                                />
                            </div>
                            <Button type="submit" size="lg" className="h-12">
                                <ArrowRight className="h-5 w-5" />
                            </Button>
                        </div>
                    </form>
                </div>
            </section>

            <section className="px-4 py-16">
                <div className="container mx-auto max-w-6xl">
                    <div className="mb-8 text-center">
                        <h2 className="mb-4 text-3xl font-bold">{t('welcome.history_title')}</h2>
                        <p className="mx-auto max-w-2xl text-muted-foreground">
                            {t('welcome.history_subtitle')}
                        </p>
                    </div>

                    {isLoadingHistory ? (
                        <Loading />
                    ) : recentGames.length === 0 ? (
                        <p className="text-center text-muted-foreground">
                            {t('welcome.no_recent_games')}
                        </p>
                    ) : (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Clock className="h-5 w-5 text-primary" />
                                    {t('welcome.history_title')}
                                </CardTitle>
                                <CardDescription>{t('welcome.history_subtitle')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="flex gap-4 overflow-x-auto pb-2 sm:grid sm:grid-cols-3 sm:overflow-visible md:grid-cols-5 lg:grid-cols-5">
                                    {recentGames.map((game) => (
                                        <GameCard
                                            key={game.id}
                                            game={game}
                                            onClick={() => visitGame(game.id)}
                                        />
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </section>

            <section className="bg-muted/30 px-4 py-16">
                <div className="container mx-auto max-w-6xl">
                    <div className="mb-12 text-center">
                        <h2 className="mb-4 text-3xl font-bold">
                            {t('welcome.questions_title')}
                        </h2>
                        <p className="mx-auto max-w-2xl text-muted-foreground">
                            {t('welcome.questions_subtitle')}
                        </p>
                    </div>

                    {isLoadingQuestions ? (
                        <Loading />
                    ) : questions.length === 0 ? (
                        <p className="text-center text-muted-foreground">
                            {t('welcome.no_questions_yet')}
                        </p>
                    ) : (
                        <div className="space-y-10">
                            {questions.map((question) => (
                                <Card key={question.id}>
                                    <CardHeader>
                                        <CardTitle className="flex items-start gap-2 text-lg">
                                            <HelpCircle className="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                                            <span>{question.translations[locale]}</span>
                                        </CardTitle>
                                        <CardDescription>
                                            {t('welcome.top_games_for_question')}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        {question.top_games.length === 0 ? (
                                            <p className="text-muted-foreground">
                                                {t('welcome.no_answers_yet')}
                                            </p>
                                        ) : (
                                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5">
                                                {question.top_games.map((game, index) => (
                                                    <GameCard
                                                        key={game.id}
                                                        game={game}
                                                        rank={index + 1}
                                                        score={game.average_score}
                                                        onClick={() => visitGame(game.id)}
                                                    />
                                                ))}
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            <footer className="border-t bg-card px-4 py-12">
                <div className="container mx-auto">
                    <div className="flex items-center gap-2">
                        <Spade className="h-6 w-6 text-primary" />
                        <span className="text-lg font-bold text-primary">LudoTest</span>
                    </div>
                    <div className="mt-8 border-t pt-8 text-center text-muted-foreground">
                        <p>{t('welcome.copyright', { year: new Date().getFullYear() })}</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}

Welcome.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>;

export default Welcome;
