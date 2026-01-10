import { Spade } from 'lucide-react';

export default function AppLogoIcon({className} : {className: string| null}) {
    return (
            <Spade className={className ?? "h-8 w-8 text-primary"} />
        );
}
