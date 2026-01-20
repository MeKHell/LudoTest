import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useLang } from '@/hooks/useLang';
import { Game, Language } from '@/types';
import { router } from '@inertiajs/react';
import { ImageOff, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { game } from '@/routes';

export function VersionTable({ versionsData }: { versionsData: Game[] }) {
    const { t } = useLang();
    const languages = useMemo<Language[]>(
        () => [
            ...new Map(
                versionsData
                    .map((game) => game.languages)
                    .flat()
                    .map((lang) => [lang.code, lang]),
            ).values(),
        ],
        [versionsData],
    );

    const [filteredLanguage, setFilteredLanguage] = useState<string[]>([]);

    const filteredVersions = useMemo<Game[]>(
        () =>
            versionsData
                .filter(
                    (v) =>
                        filteredLanguage.length === 0 ||
                        v.languages
                            .map((l) => l.code)
                            .some((code) => filteredLanguage.includes(code)),
                )
                .sort((a, b) => (a.pub_year ?? 0) - (b.pub_year ?? 0)),
        [filteredLanguage, versionsData],
    );

    return (
        <div className="rounded border">
            <Table className="rounded border">
                <TableHeader className="rounded-tl-md border bg-background">
                    <TableRow>
                        <TableHead className="rounded-tl-md border border-black">
                            {t('game.image')}
                        </TableHead>
                        <TableHead>{t('game.name')}</TableHead>
                        <TableHead className="flex justify-between">
                            <div>{t('game.language')}</div>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="outline">
                                        <Search />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    {languages.map((lang) => (
                                        <DropdownMenuCheckboxItem
                                            key={lang.code}
                                            checked={filteredLanguage.includes(
                                                lang.code,
                                            )}
                                            onCheckedChange={(event) =>
                                                setFilteredLanguage((prev) =>
                                                    event
                                                        ? [...prev, lang.code]
                                                        : prev.filter(
                                                              (x) =>
                                                                  x !==
                                                                  lang.code,
                                                          ),
                                                )
                                            }
                                            onSelect={(e) => e.preventDefault()}
                                        >
                                            {lang.name}
                                        </DropdownMenuCheckboxItem>
                                    ))}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableHead>
                        <TableHead>{t('game.year')}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {filteredVersions.map((version) => (
                        <TableRow
                            key={version.bgge_id}
                            role="link"
                            tabIndex={0}
                            className="cursor-pointer hover:bg-muted/50 even:hover:bg-primary/50"
                            onClick={() => router.get(game.get({id: version.bgge_id}).url)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    e.preventDefault();
                                    router.get(
                                        game.get({ id: version.bgge_id }).url,
                                    );
                                }
                            }}
                        >
                            <TableCell>
                                {version.thumb_url ? (
                                    <img
                                        src={version.thumb_url}
                                        alt={version.name}
                                        className="h-full w-32 object-cover"
                                    />
                                ) : (
                                    <ImageOff />
                                )}
                            </TableCell>
                            <TableCell className="font-semibold">
                                {version.name}
                            </TableCell>
                            <TableCell>
                                {version.languages.map((x) => x.name).join(', ')}
                            </TableCell>
                            <TableCell>
                                {version.pub_year
                                    ? version.pub_year
                                    : t('game.unknown')}
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}
