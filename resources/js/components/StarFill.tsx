import { Star } from 'lucide-react';

export default function StarFill({ fill }: { fill: number }) {
    const fillID = `star-gradient-fill-${fill}`;
    const strokeID = `star-gradient-stroke-${fill}`;

    return (
        <Star
            className="h-6 w-6"
            fill={`url(#${fillID})`}
            stroke={`url(#${strokeID})`}
        >
            <defs>
                <linearGradient id={fillID} x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop
                        offset={`${fill}%`}
                        stopColor="var(--color-yellow-400)"
                    />
                    <stop offset={`${fill}%`} stopColor="var(--color-card)" />
                </linearGradient>
                <linearGradient id={strokeID} x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop
                        offset={`${fill}%`}
                        stopColor={
                            fill < 1
                                ? 'var(--color-primary)'
                                : 'var(--color-yellow-400)'
                        }
                    />
                    <stop
                        offset={`${fill}%`}
                        stopColor={
                            fill > 99
                                ? 'var(--color-yellow-400)'
                                : 'var(--color-primary)'
                        }
                    />
                </linearGradient>
            </defs>
        </Star>
    );
}
