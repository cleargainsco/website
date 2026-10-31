import { FlaskConical } from 'lucide-react';
import { cn } from '@/lib/utils';

export function ResearchUseNotice({ className }: { className?: string }) {
    return (
        <div
            className={cn(
                'flex gap-4 rounded-2xl border border-champagne/50 bg-sand/40 p-5 text-sm text-charcoal/80',
                className,
            )}
        >
            <FlaskConical className="mt-0.5 size-5 shrink-0 text-deep-ocean" />
            <p>
                <span className="font-semibold text-charcoal">
                    For research use only.
                </span>{' '}
                Not for human or veterinary consumption. Our products are not
                drugs, foods or supplements, and are not intended to diagnose,
                treat, cure or prevent any disease.
            </p>
        </div>
    );
}
