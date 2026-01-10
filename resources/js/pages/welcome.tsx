import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useLang } from '@/hooks/useLang';
import { Clock, Search, Spade, Star, TrendingUp, Users } from 'lucide-react';
import { JSX, useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

const featuredGames = [
    {
        id: 1,
        title: 'Wingspan',
        image: '/wingspan-board-game-box.png',
        rating: 4.8,
        reviews: 1247,
        players: '1-5',
        playTime: '40-70 min',
        complexity: 2.4,
        description:
            'A competitive, medium-weight, card-driven, engine-building board game.',
    },
    {
        id: 2,
        title: 'Azul',
        image: '/azul-board-game-colorful-tiles.png',
        rating: 4.6,
        reviews: 892,
        players: '2-4',
        playTime: '30-45 min',
        complexity: 1.8,
        description:
            'A tile-placement game where players compete to create beautiful patterns.',
    },
    {
        id: 3,
        title: 'Gloomhaven',
        image: '/gloomhaven-fantasy-board-game.png',
        rating: 4.9,
        reviews: 2156,
        players: '1-4',
        playTime: '60-120 min',
        complexity: 3.8,
        description:
            'A game of Euro-inspired tactical combat in a persistent world.',
    },
];

const popularGames = [
    { title: 'Ticket to Ride', rating: 4.5, trend: '+12%' },
    { title: 'Catan', rating: 4.3, trend: '+8%' },
    { title: 'Pandemic', rating: 4.7, trend: '+15%' },
    { title: '7 Wonders', rating: 4.4, trend: '+6%' },
    { title: 'Splendor', rating: 4.2, trend: '+10%' },
];

export default function Welcome() {
    const [searchQuery, setSearchQuery] = useState<string>('');
    const { t } = useLang();
    return (
        <div className="min-h-screen bg-background">
            <Head title="LudoTest" />
            {/* Hero Section */}
            <section className="px-4 py-20">
                <div className="container mx-auto text-center">
                    <h1 className="mb-6 text-4xl font-bold text-balance md:text-6xl">
                        {t('welcome.title')}
                    </h1>
                    <p className="mx-auto mb-8 max-w-2xl text-xl text-balance text-muted-foreground">
                        {t('welcome.subtitle')}
                    </p>

                    {/* Search Bar */}
                    <div className="mx-auto mb-12 max-w-md">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder={t('welcome.placeholder')}
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="h-12 pl-10 text-lg"
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Featured Games */}
            <section className="bg-muted/30 px-4 py-16">
                <div className="container mx-auto">
                    <h2 className="mb-12 text-center text-3xl font-bold">
                        {t('welcome.featured')}
                    </h2>

                    <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {featuredGames.map((game) => (
                            <Card className="overflow-hidden transition-shadow hover:shadow-lg">
                                <div className="aspect-video overflow-hidden">
                                    <img
                                        src={game.image || '/placeholder.svg'}
                                        alt={game.title}
                                        className="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                    />
                                </div>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-xl">
                                            {game.title}
                                        </CardTitle>
                                        <div className="flex items-center gap-1">
                                            <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                            <span className="font-semibold">
                                                {game.rating}
                                            </span>
                                        </div>
                                    </div>
                                    <CardDescription>
                                        {game.description}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="mb-4 flex items-center justify-between text-sm text-muted-foreground">
                                        <div className="flex items-center gap-1">
                                            <Users className="h-4 w-4" />
                                            {game.players}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            <Clock className="h-4 w-4" />
                                            {game.playTime}
                                        </div>
                                        <Badge variant="secondary">
                                            Complexity: {game.complexity}/5
                                        </Badge>
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        {game.reviews} {t('welcome.reviews')}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </section>

            {/* Popular This Week */}
            <section className="px-4 py-16">
                <div className="container mx-auto">
                    <h2 className="mb-12 text-center text-3xl font-bold">
                        {t('welcome.popular')}
                    </h2>

                    <div className="mx-auto max-w-2xl">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <TrendingUp className="h-5 w-5 text-primary" />
                                    Trending Games
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {popularGames.map((game, index) => (
                                        <div className="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-muted/50">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary font-semibold text-primary-foreground">
                                                    {index + 1}
                                                </div>
                                                <div>
                                                    <div className="font-medium">
                                                        {game.title}
                                                    </div>
                                                    <div className="flex items-center gap-1 text-sm text-muted-foreground">
                                                        <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                                        {game.rating}
                                                    </div>
                                                </div>
                                            </div>
                                            <Badge
                                                variant="outline"
                                                className="border-green-600 text-green-600"
                                            >
                                                {game.trend}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t bg-card px-4 py-12">
                <div className="container mx-auto">
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-4">
                        <div>
                            <div className="mb-4 flex items-center gap-2">
                                <Spade className="h-6 w-6 text-primary" />
                                <span className="text-lg font-bold text-primary">
                                    LudoTest
                                </span>
                            </div>
                            <p className="text-muted-foreground">
                                The ultimate platform for board game enthusiasts
                                to discover, rate, and review games.
                            </p>
                        </div>

                        <div>
                            <h3 className="mb-4 font-semibold">Platform</h3>
                            <ul className="space-y-2 text-muted-foreground">
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Browse Games
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Top Rated
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        New Releases
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Categories
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 className="mb-4 font-semibold">Community</h3>
                            <ul className="space-y-2 text-muted-foreground">
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Forums
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Reviews
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Events
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Blog
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 className="mb-4 font-semibold">Support</h3>
                            <ul className="space-y-2 text-muted-foreground">
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Help Center
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Contact Us
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Privacy Policy
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="transition-colors hover:text-foreground"
                                    >
                                        Terms of Service
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="mt-8 border-t pt-8 text-center text-muted-foreground">
                        <p>&copy; 2025 LudoTest. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}

Welcome.layout = (page: JSX.Element) => (
    <AppLayout children={page} />
);
