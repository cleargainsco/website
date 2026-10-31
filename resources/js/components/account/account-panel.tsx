import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function AccountPanel({
    title,
    description,
    action,
    children,
    className,
}: {
    title: string;
    description?: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section
            className={cn(
                'rounded-3xl bg-white p-6 shadow-sm ring-1 ring-charcoal/5 sm:p-8',
                className,
            )}
        >
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 className="font-serif text-2xl font-medium text-deep-ocean">
                        {title}
                    </h2>
                    {description && (
                        <p className="mt-1 text-sm text-charcoal/60">
                            {description}
                        </p>
                    )}
                </div>
                {action}
            </div>
            <div className="mt-6">{children}</div>
        </section>
    );
}
