import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useLang } from '@/hooks/useLang';
import { type Comment } from '@/types';
import { ReactNode, useMemo } from 'react';

export default function CommentDisplay({
    comment,
    className,
}: {
    comment: Comment;
    className: string;
}): ReactNode {
    const { t, locale } = useLang();
    const timeFormatter = useMemo(
        () =>
            new Intl.DateTimeFormat(locale, {
                dateStyle: 'full',
                timeStyle: 'medium',
            }),
        [locale],
    );

    return (
        <Card className={`bg-background pt-1 pl-1 gap-3 ${className}`}>
            <CardHeader className="pl-2 gap-0">
                <CardTitle className="text-xs font-light">
                    {t('comment.written_by_date', {
                        name: comment.writer,
                        date: timeFormatter.format(
                            new Date(comment.created_at),
                        ),
                    })}
                </CardTitle>
                {comment.editor && (
                    <CardDescription className="text-xs font-light">
                        {t('comment.edited_by_date', {
                            name: comment.editor,
                            date: timeFormatter.format(
                                new Date(comment.updated_at),
                            ),
                        })}
                    </CardDescription>
                )}
            </CardHeader>
            <CardContent className="pl-3">
                {comment.comment[comment.original_lang.code]}
            </CardContent>
        </Card>
    );
}
