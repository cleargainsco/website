import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function Eyebrow({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <p
            className={cn(
                'text-xs font-medium tracking-[0.3em] text-ocean uppercase',
                className,
            )}
        >
            {children}
        </p>
    );
}
