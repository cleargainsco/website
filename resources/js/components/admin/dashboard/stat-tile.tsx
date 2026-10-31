import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import type { RouteDefinition } from '@/wayfinder';

export function StatTile({
    label,
    value,
    detail,
    icon: Icon,
    href,
}: {
    label: string;
    value: ReactNode;
    detail: ReactNode;
    icon: LucideIcon;
    href?: RouteDefinition<'get'>;
}) {
    const body = (
        <>
            <div className="flex items-center justify-between gap-3">
                <p className="text-sm font-medium text-muted-foreground">
                    {label}
                </p>
                <Icon className="size-4 text-muted-foreground" />
            </div>
            <p className="mt-3 text-3xl font-semibold tracking-tight tabular-nums">
                {value}
            </p>
            <p className="mt-1 text-sm text-muted-foreground">{detail}</p>
        </>
    );

    const className = 'rounded-xl border bg-card p-5 shadow-xs';

    return href ? (
        <Link
            href={href}
            className={`${className} block transition-colors hover:border-primary/40`}
        >
            {body}
        </Link>
    ) : (
        <div className={className}>{body}</div>
    );
}
