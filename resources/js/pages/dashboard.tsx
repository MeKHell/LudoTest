import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Loading from '@/components/loading';
import AppLayout from '@/layouts/app-layout';
import { game_internal, home } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useBreadcrumbContext } from '@/hooks/useBreadcrumbs';
import { useLang } from '@/hooks/useLang';
import { fetchJson } from '@/lib/fetch-json';
import { MessageSquare, Library, ThumbsUp, ImageOff } from 'lucide-react';
import { ReactNode, useEffect, useMemo, useState } from 'react';

interface DashboardStats {
    comments_count: number;
    votes_count: number;
    library_count: number;
}

interface RecentLibraryGame {
    id: number;
    name: string;
    thumb_url: string | null;
    list_type: string;
}

interface DashboardData {
    stats: DashboardStats;
    recentLibraryGames: RecentLibraryGame[];
}

const emptyDashboard: DashboardData = {
    stats: {
        comments_count: 0,
        votes_count: 0,
        library_count: 0,
    },
    recentLibraryGames: [],
};

function Dashboard() {
    const { setBreadcrumbs } = useBreadcrumbContext();
    const { t } = useLang();
    const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [loadFailed, setLoadFailed] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = useMemo(
        () => [
            {
                title: t('menu.home'),
                href: home().url,
            },
        ],
        [t],
    );

    useEffect(() => {
        setBreadcrumbs(breadcrumbs);
    }, [setBreadcrumbs, breadcrumbs]);

    useEffect(() => {
        fetchJson<DashboardData>('/api/dashboard')
            .then((data) => {
                setDashboardData({
                    stats: {
                        comments_count: data?.stats?.comments_count ?? 0,
                        votes_count: data?.stats?.votes_count ?? 0,
                        library_count: data?.stats?.library_count ?? 0,
                    },
                    recentLibraryGames: Array.isArray(data?.recentLibraryGames)
                        ? data.recentLibraryGames
                        : [],
                });
                setLoadFailed(false);
            })
            .catch((error) => {
                console.error('Failed to load dashboard', error);
                setDashboardData(emptyDashboard);
                setLoadFailed(true);
            })
            .finally(() => setIsLoading(false));
    }, []);

    const listTypeLabel = (listType: string): string => {
        if (listType === 'owned') {
            return t('dashboard.list_owned');
        }
        if (listType === 'wishlist') {
            return t('dashboard.list_wishlist');
        }
        return listType;
    };

    if (isLoading || !dashboardData) {
        return (
            <>
                <Head title={t('dashboard.title')} />
                <Loading />
            </>
        );
    }

    const { stats, recentLibraryGames } = dashboardData;

    return (
        <>
            <Head title={t('dashboard.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {loadFailed && (
                    <p className="text-sm text-muted-foreground">{t('menu.load_failed')}</p>
                )}
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('dashboard.comments')}
                            </CardTitle>
                            <MessageSquare className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.comments_count}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('dashboard.votes')}
                            </CardTitle>
                            <ThumbsUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.votes_count}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium">
                                {t('dashboard.library')}
                            </CardTitle>
                            <Library className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.library_count}</div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('dashboard.recent_library')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentLibraryGames.length === 0 ? (
                            <p className="text-muted-foreground">{t('dashboard.empty_library')}</p>
                        ) : (
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {recentLibraryGames.map((game) => (
                                    <Link
                                        key={game.id}
                                        href={game_internal.url(game.id)}
                                        className="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                    >
                                    <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded bg-muted">
                                        {game.thumb_url ? (
                                            <img
                                                src={game.thumb_url}
                                                alt={game.name}
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <ImageOff className="h-5 w-5 text-muted-foreground" />
                                        )}
                                    </div>
                                    <div>
                                        <div className="font-medium">{game.name}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {listTypeLabel(game.list_type)}
                                        </div>
                                    </div>
                                    </Link>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Dashboard.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;

export default Dashboard;
