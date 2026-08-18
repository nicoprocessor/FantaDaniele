import type { LucideIcon } from 'lucide-react';
import { Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();

    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'Chiaro' },
        { value: 'dark', icon: Moon, label: 'Scuro' },
        { value: 'system', icon: Monitor, label: 'Sistema' },
    ];

    return (
        <div
            className={cn(
                'inline-grid grid-cols-3 overflow-hidden rounded-sm border bg-background',
                className,
            )}
            role="group"
            aria-label="Tema dell’interfaccia"
            {...props}
        >
            {tabs.map(({ value, icon: Icon, label }) => (
                <button
                    key={value}
                    type="button"
                    onClick={() => updateAppearance(value)}
                    aria-pressed={appearance === value}
                    className={cn(
                        'flex h-11 min-w-0 items-center justify-center gap-1.5 border-r px-2 text-xs font-medium transition-colors last:border-r-0 focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                        appearance === value
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-secondary hover:text-secondary-foreground',
                    )}
                >
                    <Icon className="size-3.5 shrink-0" />
                    <span>{label}</span>
                </button>
            ))}
        </div>
    );
}
