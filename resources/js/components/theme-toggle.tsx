import { Appearance, useAppearance } from '@/hooks/use-appearance';
import { Cog, Moon, Sun } from 'lucide-react';
import { Button } from './ui/button';

export function ThemeToggle() {
    const { appearance, updateAppearance } = useAppearance();
    const iconMap = {
        system: <Cog className="h-5 min-h-5 w-5 min-w-5" />,
        light: <Sun className="h-5 min-h-5 w-5 min-w-5" />,
        dark: <Moon className="h-5 min-h-5 w-5 min-w-5" />,
    };
    return (
        <div className="flex justify-around">
            {(Object.keys(iconMap) as Appearance[]).map((theme) => (
                <Button
                    variant={(theme === appearance) ? 'secondary': "outline"}
                    size="icon"
                    onClick={() => updateAppearance(theme)}
                >
                    {iconMap[theme]}
                </Button>
            ))}
        </div>
    );
}
