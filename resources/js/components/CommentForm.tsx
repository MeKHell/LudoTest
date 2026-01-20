import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useLang } from '@/hooks/useLang';
import { store as commentStore } from '@/routes/api/comment';
import { Form } from '@inertiajs/react';
import { Pen } from 'lucide-react';
import { ReactNode } from 'react';

export interface CommentFormProps {
    gameId: string;
}

export function CommentForm({ gameId }: CommentFormProps): ReactNode {
    const { t } = useLang();
    return (
        <Form action={commentStore()}>
            <Textarea
                name="comment"
                placeholder={t('game.comment_placeholder')}
            />
            <input type="hidden" name="game_id" value={gameId} />
            <div className="flex justify-end">
                <Button
                    type="submit"
                    variant="positive"
                    className="mt-2 font-semibold"
                >
                    <Pen className="size-5" />
                    {t('game.write_comment')}
                </Button>
            </div>
        </Form>
    );
}
