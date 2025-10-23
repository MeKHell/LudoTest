import { Appearance, useAppearance } from '@/hooks/use-appearance';
import { Cog, Moon, Sun } from 'lucide-react';
import { Button } from './ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from './ui/dropdown-menu';

export function ThemeToggle(props: { className?: string }) {
    const { appearance, updateAppearance } = useAppearance();
    const iconMap = {
        light: <Sun className="h-5 min-h-5 w-5 min-w-5" />,
        dark: <Moon className="h-5 min-h-5 w-5 min-w-5" />,
        system: <Cog className="h-5 min-h-5 w-5 min-w-5" />,
    };
    return (
        <div className={props.className}>
            <DropdownMenu modal={false}>
                <DropdownMenuTrigger>
                    <Button variant="outline" size="icon" {...props}>
                        {iconMap[appearance]}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="min-w-fit">
                    {(Object.keys(iconMap) as Appearance[])
                        .filter((x) => x !== appearance)
                        .map((theme) => (
                            <DropdownMenuItem
                                key={theme}
                                onClick={() => updateAppearance(theme)}
                                className="h-full w-full"
                            >
                                {iconMap[theme]}
                            </DropdownMenuItem>
                        ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
