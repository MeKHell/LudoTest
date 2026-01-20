import { Alert, AlertTitle } from '@/components/ui/alert';
import { CircleCheckIcon } from 'lucide-react';

export default function AlertValid({
    title,
}: {
    title: string;
}) {
    return (
        <Alert variant="constructive" className="text-constructive-foreground">
            <CircleCheckIcon />
            <AlertTitle>{title}</AlertTitle>
        </Alert>
    );
}
