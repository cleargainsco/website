import { cva } from 'class-variance-authority';

export const brandButton = cva(
    'inline-flex items-center justify-center gap-2 rounded-full font-medium tracking-wide transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ocean disabled:cursor-not-allowed disabled:opacity-50',
    {
        variants: {
            variant: {
                primary: 'bg-deep-ocean text-linen hover:bg-ocean',
                secondary:
                    'border border-deep-ocean/30 text-deep-ocean hover:border-deep-ocean hover:bg-deep-ocean/5',
                light: 'bg-linen text-deep-ocean hover:bg-sand',
                ghostLight:
                    'border border-linen/40 text-linen hover:bg-linen/10',
            },
            size: {
                default: 'px-6 py-3 text-sm',
                sm: 'px-4 py-2 text-xs',
            },
        },
        defaultVariants: {
            variant: 'primary',
            size: 'default',
        },
    },
);
