import type { ReactNode } from 'react';

export function EmptyState({ children }: { children: ReactNode }) {
    return (
        <p className="border-l-2 border-muted-foreground/30 pl-3 text-sm text-muted-foreground">
            {children}
        </p>
    );
}
