import CommentDisplay from '@/components/CommentDisplay';
import { CommentForm } from '@/components/CommentForm';
import Loading from '@/components/loading';
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
        fetch(CommentAPI.get(gameId).url)
            .then((res) => res.json())
            .then((data) => setComments(() => data))
            .then(() => setIsLoading(false));
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
