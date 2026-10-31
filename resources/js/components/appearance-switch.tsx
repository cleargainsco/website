import { Monitor, Moon, Sun } from 'lucide-react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

const appearances: { value: Appearance; icon: typeof Sun; label: string }[] = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
];

export function AppearanceSwitch() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <div className="flex items-center gap-0.5 rounded-md border p-0.5">
            {appearances.map(({ value, icon: Icon, label }) => (
                <button
                    key={value}
                    type="button"
                    onClick={() => updateAppearance(value)}
                    aria-label={label}
                    aria-pressed={appearance === value}
                    title={label}
                    className={cn(
                        'flex size-6 items-center justify-center rounded-sm transition-colors',
                        appearance === value
                            ? 'bg-accent text-accent-foreground'
                            : 'text-muted-foreground hover:text-foreground',
                    )}
                >
                    <Icon className="size-3.5" />
                </button>
            ))}
        </div>
    );
}
