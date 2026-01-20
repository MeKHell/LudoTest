import { ReactNode } from 'react';
import StarFill from '@/components/StarFill';


export default function StarRating({
    rank = 3.6,
}: {
    rank?: number;
}): ReactNode {
    return (
        <div className='flex'>
            {[...Array(5)].map((_, i) => (
                <StarFill key={i} fill={Math.max(0, Math.min(rank - i, 1))*110}/>
            ))}
        </div>
    );
}
