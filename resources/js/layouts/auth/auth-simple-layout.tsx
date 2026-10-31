import { Link } from '@inertiajs/react';
import { MountainRange } from '@/components/marketing/mountain-range';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative flex min-h-svh flex-col items-center justify-center bg-linen px-6 pt-10 pb-40 text-charcoal">
            <div className="w-full max-w-md">
                <Link href={home()} className="mx-auto block w-fit">
                    <img
                        src="/images/brand/clear-gains-horizontal.svg"
                        alt="Clear Gains — Research Peptides"
                        className="h-12 w-auto"
                    />
                </Link>

                <div className="mt-10 rounded-3xl bg-white p-8 shadow-xl ring-1 shadow-deep-ocean/5 ring-charcoal/5 sm:p-10">
                    <div className="mb-8 space-y-2 text-center">
                        <h1 className="font-serif text-3xl font-medium">
                            {title}
                        </h1>
                        {description && (
                            <p className="text-sm text-charcoal/60">
                                {description}
                            </p>
                        )}
                    </div>
                    {children}
                </div>

                <p className="mt-6 text-center text-xs tracking-[0.2em] text-charcoal/50 uppercase">
                    For research use only
                </p>
            </div>
            <MountainRange className="absolute inset-x-0 bottom-0 h-24" />
        </div>
    );
}
