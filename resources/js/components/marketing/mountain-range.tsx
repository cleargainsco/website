import { cn } from '@/lib/utils';

export function MountainRange({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 400 120"
            preserveAspectRatio="none"
            aria-hidden="true"
            className={cn('block w-full', className)}
        >
            <path
                d="M0 70 C40 50 80 40 120 55 C160 70 200 35 250 40 C300 45 340 65 400 50 V120 H0Z"
                className="fill-mist"
                opacity="0.8"
            />
            <path
                d="M0 85 C50 70 90 75 140 80 C190 85 230 60 280 65 C330 70 360 85 400 75 V120 H0Z"
                className="fill-sand"
            />
            <path
                d="M0 95 C60 80 110 100 170 92 C230 84 260 70 320 85 C360 95 380 90 400 88 V120 H0Z"
                className="fill-ocean"
                opacity="0.7"
            />
            <path
                d="M0 100 C80 88 150 108 220 98 C290 88 340 100 400 94"
                fill="none"
                className="stroke-champagne"
                strokeWidth="1.5"
                vectorEffect="non-scaling-stroke"
            />
            <path
                d="M0 105 C70 95 130 112 200 106 C270 100 330 110 400 102 V120 H0Z"
                className="fill-deep-ocean"
            />
            <path
                d="M0 114 C80 108 160 118 240 113 C320 108 360 116 400 112 V120 H0Z"
                className="fill-charcoal"
            />
        </svg>
    );
}
