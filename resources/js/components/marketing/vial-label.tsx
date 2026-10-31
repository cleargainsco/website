import { BrushRing } from '@/components/marketing/brush-ring';
import { MountainRange } from '@/components/marketing/mountain-range';
import { cn } from '@/lib/utils';

export function VialLabel({
    name,
    amount,
    className,
}: {
    name: string;
    amount: string;
    className?: string;
}) {
    return (
        <div
            className={cn(
                '@container relative isolate flex aspect-4/5 flex-col items-center overflow-hidden rounded-2xl bg-linen text-center ring-1 ring-charcoal/5',
                className,
            )}
        >
            <BrushRing className="absolute top-[-6%] left-1/2 -z-10 w-[92%] -translate-x-1/2" />
            <img
                src="/images/brand/clear-gains-icon.svg"
                alt=""
                className="mt-[13cqw] w-[20cqw]"
            />
            <img
                src="/images/brand/clear-gains-wordmark.svg"
                alt="Clear Gains"
                className="mt-[3cqw] w-[62cqw]"
            />
            <p className="mt-[2cqw] text-[3.2cqw] tracking-[0.35em] text-charcoal/80 uppercase">
                Research peptides
            </p>
            <span className="my-[4cqw] h-px w-[10cqw] bg-champagne" />
            <p className="px-[6cqw] font-serif text-[8cqw] leading-tight text-charcoal">
                {name}
            </p>
            <p className="mt-[1cqw] font-serif text-[7cqw] text-deep-ocean">
                {amount}
            </p>
            <MountainRange className="absolute inset-x-0 bottom-[9cqw] -z-10 h-[34%]" />
            <p className="absolute inset-x-0 bottom-0 bg-linen py-[3cqw] text-[2.8cqw] tracking-[0.25em] text-charcoal/80 uppercase">
                Research use only
            </p>
        </div>
    );
}
