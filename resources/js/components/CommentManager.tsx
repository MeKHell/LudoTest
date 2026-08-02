import CommentDisplay from '@/components/CommentDisplay';
import { CommentForm } from '@/components/CommentForm';
import Loading from '@/components/loading';
import { fetchJson } from '@/lib/fetch-json';
import { default as CommentAPI } from '@/routes/api/comment';
import { type Comment } from '@/types';
import { ReactNode, useEffect, useState } from 'react';

export default function CommentManager({
    gameId,
}: {
    gameId: string;
}): ReactNode {
    const [comments, setComments] = useState<Comment[]>([]);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const loadComments = () => {
        setIsLoading(true);
        fetchJson<Comment[]>(CommentAPI.get(gameId).url)
            .then((data) => setComments(Array.isArray(data) ? data : []))
            .catch((error) => {
                console.error('Failed to load comments', error);
                setComments([]);
            })
            .finally(() => setIsLoading(false));
    };

    // eslint-disable-next-line react-hooks/set-state-in-effect
    useEffect(loadComments, [gameId]);

    return (
        <div>
            <CommentForm gameId={gameId} onSuccess={loadComments} />
            {isLoading ? (
                <Loading />
            ) : (
                comments.map((comment) => (
                    <CommentDisplay
                        key={comment.id}
                        comment={comment}
                        className="mt-3"
                    />
                ))
            )}
        </div>
    );
}
