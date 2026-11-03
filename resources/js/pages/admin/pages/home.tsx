import { Form } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import { useState } from 'react';
import HomePageController from '@/actions/App/Http/Controllers/Admin/HomePageController';
import { ImageField } from '@/components/admin/image-field';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { HomeHeroCopy } from '@/components/marketing/home-hero';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { home } from '@/routes';
import { edit } from '@/routes/admin/pages/home';
import type { HomeHero } from '@/types/storefront';

type CopyField = 'eyebrow' | 'title' | 'title_highlight' | 'description';

const copyFields: {
    name: CopyField;
    label: string;
    max: number;
    multiline?: boolean;
    help?: string;
}[] = [
    { name: 'eyebrow', label: 'Small heading', max: 60 },
    { name: 'title', label: 'Heading', max: 80 },
    {
        name: 'title_highlight',
        label: 'Highlighted words',
        max: 80,
        help: 'Optional. Shown after the heading in blue.',
    },
    { name: 'description', label: 'Description', max: 400, multiline: true },
];

const textareaClass =
    'w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

export default function HomePageEdit({ hero }: { hero: HomeHero }) {
    const [copy, setCopy] = useState(hero);

    return (
        <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <Heading
                    title="Home page"
                    description="The heading, description and photo at the top of the home page."
                />
                <Button variant="outline" size="sm" asChild>
                    <a href={home().url} target="_blank" rel="noreferrer">
                        View page
                        <ExternalLink />
                    </a>
                </Button>
            </div>

            <div className="grid gap-6 lg:grid-cols-[1fr_28rem]">
                <Form
                    {...HomePageController.update.form()}
                    options={{ preserveScroll: true }}
                    resetOnSuccess={['hero_image']}
                    noValidate
                    className="space-y-6 rounded-xl border bg-card p-6 shadow-xs"
                >
                    {({ processing, errors }) => (
                        <>
                            {copyFields.map((field) => (
                                <div key={field.name} className="grid gap-2">
                                    <div className="flex items-baseline justify-between gap-4">
                                        <Label htmlFor={field.name}>
                                            {field.label}
                                        </Label>
                                        <span className="text-xs text-muted-foreground tabular-nums">
                                            {(copy[field.name] ?? '').length}/
                                            {field.max}
                                        </span>
                                    </div>
                                    {field.multiline ? (
                                        <textarea
                                            id={field.name}
                                            name={field.name}
                                            rows={4}
                                            maxLength={field.max}
                                            value={copy[field.name] ?? ''}
                                            onChange={(event) =>
                                                setCopy({
                                                    ...copy,
                                                    [field.name]:
                                                        event.target.value,
                                                })
                                            }
                                            className={textareaClass}
                                        />
                                    ) : (
                                        <Input
                                            id={field.name}
                                            name={field.name}
                                            maxLength={field.max}
                                            value={copy[field.name] ?? ''}
                                            onChange={(event) =>
                                                setCopy({
                                                    ...copy,
                                                    [field.name]:
                                                        event.target.value,
                                                })
                                            }
                                        />
                                    )}
                                    {field.help && (
                                        <p className="text-xs text-muted-foreground">
                                            {field.help}
                                        </p>
                                    )}
                                    <InputError message={errors[field.name]} />
                                </div>
                            ))}

                            <div className="space-y-2">
                                <h3 className="text-sm font-medium">Photo</h3>
                                <p className="text-xs text-muted-foreground">
                                    Shown beside the heading, cropped to a 3:2
                                    landscape. Without one, the original vial
                                    photo is used.
                                </p>
                                <ImageField
                                    name="hero_image"
                                    removeName="remove_hero_image"
                                    currentUrl={hero.image_url}
                                    help="JPG, PNG or WebP, up to 10 MB. Landscape works best."
                                    error={errors.hero_image}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image_alt">
                                    Photo description
                                </Label>
                                <Input
                                    id="image_alt"
                                    name="image_alt"
                                    maxLength={160}
                                    defaultValue={hero.image_alt}
                                />
                                <p className="text-xs text-muted-foreground">
                                    Read out by screen readers and used by
                                    search engines. Describe what the photo
                                    shows.
                                </p>
                                <InputError message={errors.image_alt} />
                            </div>

                            <Button disabled={processing}>Save</Button>
                        </>
                    )}
                </Form>

                <div className="space-y-3 lg:sticky lg:top-6 lg:h-fit">
                    <h3 className="text-sm font-medium">Preview</h3>
                    <div className="rounded-3xl bg-linen p-8 shadow-sm ring-1 ring-black/5">
                        <HomeHeroCopy hero={copy} />
                    </div>
                </div>
            </div>
        </div>
    );
}

HomePageEdit.layout = {
    breadcrumbs: [{ title: 'Home page', href: edit() }],
};
