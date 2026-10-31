import type { ReactNode } from 'react';
import { Eyebrow } from '@/components/marketing/eyebrow';
import { MountainRange } from '@/components/marketing/mountain-range';

export function PageHero({
    eyebrow,
    title,
    children,
}: {
    eyebrow: string;
    title: string;
    children?: ReactNode;
}) {
    return (
        <section className="relative overflow-hidden bg-sand/50">
            <div className="mx-auto max-w-7xl px-6 pt-16 pb-28 lg:px-8 lg:pt-20 lg:pb-36">
                <Eyebrow>{eyebrow}</Eyebrow>
                <h1 className="mt-4 max-w-3xl font-serif text-4xl font-medium text-charcoal sm:text-5xl">
                    {title}
                </h1>
                {children && (
                    <div className="mt-5 max-w-2xl text-lg leading-relaxed text-charcoal/70">
                        {children}
                    </div>
                )}
            </div>
            <MountainRange className="absolute inset-x-0 bottom-0 h-16 lg:h-24" />
        </section>
    );
}
