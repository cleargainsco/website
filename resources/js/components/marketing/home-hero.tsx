import { Eyebrow } from '@/components/marketing/eyebrow';
import { cn } from '@/lib/utils';
import type { HomeHero } from '@/types/storefront';

export function HomeHeroCopy({ hero }: { hero: HomeHero }) {
    return (
        <>
            <Eyebrow>{hero.eyebrow}</Eyebrow>
            <h1 className="mt-5 font-serif text-5xl leading-[1.05] font-medium text-charcoal sm:text-6xl">
                {hero.title}
                {hero.title_highlight && (
                    <>
                        {' '}
                        <span className="text-ocean">
                            {hero.title_highlight}
                        </span>
                    </>
                )}
            </h1>
            <p className="mt-6 max-w-lg text-lg leading-relaxed text-charcoal/70">
                {hero.description}
            </p>
        </>
    );
}

export function HomeHeroImage({
    hero,
    className,
}: {
    hero: HomeHero;
    className?: string;
}) {
    const imageClass = cn(
        'aspect-[3/2] w-full rounded-[2rem] object-cover shadow-2xl shadow-deep-ocean/20',
        className,
    );
    if (hero.image_url) {
        return (
            <img
                src={hero.image_url}
                alt={hero.image_alt}
                className={imageClass}
            />
        );
    }

    return (
        <img
            src="/images/vial-and-card.jpg"
            srcSet="/images/vial-and-card-960.jpg 960w, /images/vial-and-card.jpg 1536w"
            sizes="(min-width: 1024px) 50vw, 100vw"
            alt={hero.image_alt}
            className={imageClass}
        />
    );
}
