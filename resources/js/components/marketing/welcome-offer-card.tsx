import type { ElementType, ReactNode } from 'react';
import type { WelcomeOfferCopy } from '@/types/marketing';

export function WelcomeOfferCard({
    copy,
    claimed,
    form,
    title: Title = 'h2',
    description: Description = 'p',
}: {
    copy: WelcomeOfferCopy;
    claimed: boolean;
    form: ReactNode;
    title?: ElementType;
    description?: ElementType;
}) {
    return (
        <div className="text-center text-charcoal">
            <img
                src="/images/brand/clear-gains-horizontal.svg"
                alt=""
                className="mx-auto h-9 w-auto"
            />

            {claimed ? (
                <>
                    <Title className="mt-8 font-serif text-3xl font-medium text-deep-ocean">
                        {copy.success_headline}
                    </Title>
                    <Description className="mt-3 text-sm text-charcoal/70">
                        {copy.success_body}
                    </Description>
                </>
            ) : (
                <>
                    <p className="mt-8 text-[0.7rem] tracking-[0.2em] text-ocean uppercase">
                        {copy.eyebrow}
                    </p>
                    <Title className="mt-3 font-serif text-3xl font-medium text-deep-ocean">
                        {copy.headline}
                    </Title>
                    <Description className="mt-3 text-sm text-charcoal/70">
                        {copy.body}
                    </Description>
                    <div className="mt-7">{form}</div>
                    <p className="mt-5 text-xs text-charcoal/50">
                        {copy.fine_print}
                    </p>
                </>
            )}
        </div>
    );
}
