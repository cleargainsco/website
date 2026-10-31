import { ImageUp, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const acceptedTypes = ['image/jpeg', 'image/png', 'image/webp'];

export function ProductImageField({
    currentUrl,
    error,
}: {
    currentUrl: string | null;
    error?: string;
}) {
    const input = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState(currentUrl);
    const [removed, setRemoved] = useState(false);
    const [dragging, setDragging] = useState(false);
    const [rejected, setRejected] = useState(false);

    const choose = (files: FileList | null) => {
        const file = files?.[0];

        if (!file) {
            return;
        }

        if (!acceptedTypes.includes(file.type)) {
            setRejected(true);

            return;
        }

        if (input.current && input.current.files !== files) {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.current.files = transfer.files;
        }

        setPreview(URL.createObjectURL(file));
        setRemoved(false);
        setRejected(false);
    };

    const remove = () => {
        if (input.current) {
            input.current.value = '';
        }

        setPreview(null);
        setRemoved(currentUrl !== null);
    };

    return (
        <div className="space-y-3">
            <input
                ref={input}
                id="image"
                type="file"
                name="image"
                accept={acceptedTypes.join(',')}
                className="sr-only"
                tabIndex={-1}
                onChange={(event) => choose(event.target.files)}
            />

            <button
                type="button"
                onClick={() => input.current?.click()}
                onDragOver={(event) => {
                    event.preventDefault();
                    setDragging(true);
                }}
                onDragLeave={() => setDragging(false)}
                onDrop={(event) => {
                    event.preventDefault();
                    setDragging(false);
                    choose(event.dataTransfer.files);
                }}
                className={cn(
                    'relative flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none',
                    preview ? 'h-72 bg-muted' : 'h-44',
                    dragging
                        ? 'border-primary bg-accent'
                        : 'border-input hover:border-primary/50 hover:bg-accent/50',
                )}
            >
                {preview ? (
                    <>
                        <img
                            src={preview}
                            alt=""
                            className="size-full object-contain"
                        />
                        <span
                            className={cn(
                                'absolute inset-0 flex items-center justify-center bg-background/80 text-sm font-medium opacity-0 transition-opacity',
                                dragging ? 'opacity-100' : 'hover:opacity-100',
                            )}
                        >
                            Drop or click to replace
                        </span>
                    </>
                ) : (
                    <span className="flex flex-col items-center gap-2 px-6 text-center">
                        <ImageUp className="size-8 text-muted-foreground" />
                        <span className="text-sm font-medium">
                            Drop an image here, or click to browse
                        </span>
                        <span className="text-xs text-muted-foreground">
                            JPG, PNG or WebP, up to 10 MB. Large photos are
                            resized and converted to WebP when you save.
                        </span>
                    </span>
                )}
            </button>

            {preview && (
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={remove}
                >
                    <Trash2 />
                    Remove image
                </Button>
            )}

            {removed && <input type="hidden" name="remove_image" value="1" />}
            <InputError
                message={rejected ? 'Choose a JPG, PNG or WebP image.' : error}
            />
        </div>
    );
}
