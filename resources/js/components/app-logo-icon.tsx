import type { ImgHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export default function AppLogoIcon({
    className,
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/images/brand/clear-gains-icon.svg"
            alt=""
            className={cn('object-contain', className)}
            {...props}
        />
    );
}
