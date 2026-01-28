import StarFill from '@/components/StarFill';
import { ReactNode } from 'react';

export default function StarRating({
    rank,
    vote = () => {},
}: {
    rank: number;
    vote?: (x: number) => void;
}): ReactNode {
    return (
        <div className="flex">
            {[...Array(5)].map((_, i) => (
                <div key={i} onClick={() => vote(i + 1)}>
                    <StarFill fill={Math.max(0, Math.min(rank - i, 1)) * 110} />
                </div>
            ))}
        </div>
    );
}
