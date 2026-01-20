import AlertError from '@/components/alert-error';
import AlertValid from '@/components/alert-valid';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useLang } from '@/hooks/useLang';
import { store as commentStore } from '@/routes/api/comment';
import { type FormComponentSlotProps } from '@inertiajs/core';
import { Form } from '@inertiajs/react';
import { Pen } from 'lucide-react';
import { ReactNode, useEffect } from 'react';

export interface CommentFormProps {
    gameId: string;
    onSuccess: () => void;
}

const ErrorHandler = ({
    hasErrors,
    clearErrors,
}: Pick<FormComponentSlotProps, 'hasErrors' | 'clearErrors'>) => {
    useEffect(() => {
        if (hasErrors) {
            setTimeout(clearErrors, 4000);
        }
    }, [clearErrors, hasErrors]);
    return null;
};

export function CommentForm({
    gameId,
    onSuccess,
}: CommentFormProps): ReactNode {
    const { t } = useLang();

    return (
        <Form
            action={commentStore()}
            onFinish={onSuccess}
            options={{
                preserveScroll: true,
                preserveState: true,
                preserveUrl: true,
            }}
        >
            {({ errors, hasErrors, recentlySuccessful, clearErrors }) => (
                <>
                    <ErrorHandler
                        hasErrors={hasErrors}
                        clearErrors={clearErrors}
                    />
                    <Textarea
                        name="comment"
                        placeholder={t('game.comment_placeholder')}
                        className="bg-background"
                    />
                    <input type="hidden" name="game_id" value={gameId} />
                    <div className="flex justify-end">
                        <Button
                            type="submit"
                            variant="positive"
                            className="mt-1.5 font-semibold"
                        >
                            <Pen className="size-5" />
                            {t('game.write_comment')}
                        </Button>
                    </div>
                    {hasErrors && (
                        <AlertError errors={[t(errors.message, errors)]} />
                    )}
                    {recentlySuccessful && (
                        <AlertValid title={t('game.added_correctly')} />
                    )}
                </>
            )}
        </Form>
    );
}
