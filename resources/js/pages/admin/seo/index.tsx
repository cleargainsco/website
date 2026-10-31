import { Form } from '@inertiajs/react';
import { ExternalLink, Pencil } from 'lucide-react';
import { useState } from 'react';
import SeoPageController from '@/actions/App/Http/Controllers/Admin/SeoPageController';
import { ImageField } from '@/components/admin/image-field';
import { SeoFields } from '@/components/admin/seo-fields';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import type { SeoPageSettings } from '@/types/seo';

export default function SeoIndex({ pages }: { pages: SeoPageSettings[] }) {
    const [editingKey, setEditingKey] = useState<string | null>(null);
    const editing = pages.find((page) => page.key === editingKey);

    return (
        <>
            <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
                <Heading
                    title="SEO"
                    description="How each page of the site appears in Google and in link previews. Product SEO is edited on each product."
                />

                <div className="overflow-x-auto rounded-xl border bg-card shadow-xs">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">Page</th>
                                <th className="px-4 py-3 font-medium">
                                    Search result
                                </th>
                                <th className="px-4 py-3">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {pages.map((page) => (
                                <tr key={page.key} className="align-top">
                                    <td className="px-4 py-3 whitespace-nowrap">
                                        <p className="font-medium">
                                            {page.label}
                                        </p>
                                        <a
                                            href={page.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                                        >
                                            View page
                                            <ExternalLink className="size-3" />
                                        </a>
                                    </td>
                                    <td className="max-w-xl px-4 py-3">
                                        <p className="font-medium">
                                            {page.title ?? page.default_title}
                                        </p>
                                        <p className="line-clamp-2 text-muted-foreground">
                                            {page.description ??
                                                page.default_description}
                                        </p>
                                        <div className="mt-2 flex flex-wrap gap-1.5">
                                            {page.title === null &&
                                                page.description === null && (
                                                    <Badge variant="outline">
                                                        Default
                                                    </Badge>
                                                )}
                                            {page.share_image_url && (
                                                <Badge variant="secondary">
                                                    Share image
                                                </Badge>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            aria-label={`Edit ${page.label} SEO`}
                                            title="Edit"
                                            onClick={() =>
                                                setEditingKey(page.key)
                                            }
                                        >
                                            <Pencil />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Sheet
                open={editing !== undefined}
                onOpenChange={(open) => !open && setEditingKey(null)}
            >
                <SheetContent className="flex w-full flex-col gap-0 sm:max-w-2xl">
                    {editing && (
                        <>
                            <SheetHeader className="border-b">
                                <SheetTitle>{editing.label}</SheetTitle>
                                <SheetDescription>
                                    Anything left empty uses the default.
                                </SheetDescription>
                            </SheetHeader>
                            <Form
                                key={editing.key}
                                {...SeoPageController.update.form(editing.key)}
                                options={{ preserveScroll: true }}
                                onSuccess={() => setEditingKey(null)}
                                noValidate
                                className="flex min-h-0 flex-1 flex-col"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="flex-1 space-y-8 overflow-y-auto p-6">
                                            <SeoFields
                                                titleName="title"
                                                descriptionName="description"
                                                title={editing.title}
                                                description={
                                                    editing.description
                                                }
                                                fallbackTitle={
                                                    editing.default_title
                                                }
                                                fallbackDescription={
                                                    editing.default_description
                                                }
                                                url={editing.url}
                                                errors={errors}
                                            />
                                            <div className="space-y-2">
                                                <h3 className="text-sm font-medium">
                                                    Share image
                                                </h3>
                                                <p className="text-xs text-muted-foreground">
                                                    Shown when the page is
                                                    shared on social media or in
                                                    messages. Without one, the
                                                    site's logo card is used.
                                                </p>
                                                <ImageField
                                                    name="share_image"
                                                    removeName="remove_share_image"
                                                    currentUrl={
                                                        editing.share_image_url
                                                    }
                                                    help="JPG, PNG or WebP, up to 10 MB. Cropped to 1200 × 630 when you save."
                                                    error={errors.share_image}
                                                />
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3 border-t px-6 py-4">
                                            <Button disabled={processing}>
                                                Save
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                onClick={() =>
                                                    setEditingKey(null)
                                                }
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </>
                    )}
                </SheetContent>
            </Sheet>
        </>
    );
}
