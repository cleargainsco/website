import { useState } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export const SEO_TITLE_LIMIT = 70;
export const SEO_DESCRIPTION_LIMIT = 160;

const textareaClass =
    'flex min-h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm';

export function SeoFields({
    titleName,
    descriptionName,
    title,
    description,
    fallbackTitle,
    fallbackDescription,
    url,
    errors,
}: {
    titleName: string;
    descriptionName: string;
    title: string | null;
    description: string | null;
    fallbackTitle: string;
    fallbackDescription: string;
    url: string;
    errors: Record<string, string | undefined>;
}) {
    const [titleValue, setTitleValue] = useState(title ?? '');
    const [descriptionValue, setDescriptionValue] = useState(description ?? '');

    return (
        <div className="space-y-6">
            <div className="rounded-lg border bg-white p-4 text-left dark:bg-white">
                <p className="truncate text-xs text-[#4d5156]">{url}</p>
                <p className="mt-1 truncate text-lg leading-snug text-[#1a0dab]">
                    {titleValue || fallbackTitle}
                </p>
                <p className="mt-1 line-clamp-2 text-sm text-[#4d5156]">
                    {descriptionValue || fallbackDescription}
                </p>
            </div>

            <div className="grid gap-2">
                <div className="flex items-baseline justify-between">
                    <Label htmlFor={titleName}>Search title</Label>
                    <CharacterCount
                        count={titleValue.length}
                        limit={SEO_TITLE_LIMIT}
                    />
                </div>
                <Input
                    id={titleName}
                    name={titleName}
                    value={titleValue}
                    placeholder={fallbackTitle}
                    maxLength={SEO_TITLE_LIMIT}
                    onChange={(event) => setTitleValue(event.target.value)}
                />
                <p className="text-xs text-muted-foreground">
                    Leave empty to use the default shown in grey. The site name
                    is added to the end.
                </p>
                <InputError message={errors[titleName]} />
            </div>

            <div className="grid gap-2">
                <div className="flex items-baseline justify-between">
                    <Label htmlFor={descriptionName}>Search description</Label>
                    <CharacterCount
                        count={descriptionValue.length}
                        limit={SEO_DESCRIPTION_LIMIT}
                    />
                </div>
                <textarea
                    id={descriptionName}
                    name={descriptionName}
                    rows={3}
                    value={descriptionValue}
                    placeholder={fallbackDescription}
                    maxLength={SEO_DESCRIPTION_LIMIT}
                    onChange={(event) =>
                        setDescriptionValue(event.target.value)
                    }
                    className={textareaClass}
                />
                <InputError message={errors[descriptionName]} />
            </div>
        </div>
    );
}

function CharacterCount({ count, limit }: { count: number; limit: number }) {
    return (
        <span
            className={cn(
                'text-xs tabular-nums',
                count > limit * 0.9
                    ? 'text-amber-600'
                    : 'text-muted-foreground',
            )}
        >
            {count}/{limit}
        </span>
    );
}
