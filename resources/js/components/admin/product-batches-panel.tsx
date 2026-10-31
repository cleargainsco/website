import { Form } from '@inertiajs/react';
import { FileText, Plus, Trash2 } from 'lucide-react';
import ProductBatchController from '@/actions/App/Http/Controllers/Admin/ProductBatchController';
import { ConfirmActionDialog } from '@/components/confirm-action-dialog';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/dates';
import type { AdminProductBatch } from '@/types/admin-products';

export function ProductBatchesPanel({
    productId,
    batches,
}: {
    productId: string;
    batches: AdminProductBatch[];
}) {
    return (
        <div className="flex-1 space-y-8 overflow-y-auto px-6 py-6">
            <section className="space-y-3">
                <header>
                    <h2 className="text-base font-semibold">
                        Published batches
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Shown on the product page and the Testing & COAs page,
                        newest test first.
                    </p>
                </header>

                {batches.length === 0 ? (
                    <p className="rounded-lg border border-dashed px-4 py-6 text-center text-sm text-muted-foreground">
                        No batches yet. Until one is added, the shop says the
                        report for the current batch is on its way.
                    </p>
                ) : (
                    <ul className="divide-y rounded-lg border">
                        {batches.map((batch) => (
                            <li
                                key={batch.id}
                                className="flex items-center gap-4 px-4 py-3 text-sm"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="font-medium">
                                        Batch {batch.number}
                                    </p>
                                    <p className="text-muted-foreground">
                                        Tested {formatDate(batch.tested_at)}
                                        {batch.purity && ` · ${batch.purity}`}
                                        {batch.lab && ` · ${batch.lab}`}
                                    </p>
                                </div>
                                {batch.certificate_url && (
                                    <Button variant="ghost" size="icon" asChild>
                                        <a
                                            href={batch.certificate_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            aria-label={`Open the COA for batch ${batch.number}`}
                                            title="Open COA"
                                        >
                                            <FileText />
                                        </a>
                                    </Button>
                                )}
                                <ConfirmActionDialog
                                    trigger={
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="text-destructive hover:text-destructive"
                                            aria-label={`Remove batch ${batch.number}`}
                                            title="Remove"
                                        >
                                            <Trash2 />
                                        </Button>
                                    }
                                    title={`Remove batch ${batch.number}?`}
                                    description="Its COA is deleted and it's no longer shown in the shop."
                                    confirmLabel="Remove"
                                    action={ProductBatchController.destroy.form(
                                        {
                                            product: productId,
                                            batch: batch.id,
                                        },
                                    )}
                                    destructive
                                />
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <section className="space-y-4">
                <header>
                    <h2 className="text-base font-semibold">Add a batch</h2>
                </header>
                <Form
                    {...ProductBatchController.store.form(productId)}
                    options={{ preserveScroll: true, preserveState: true }}
                    resetOnSuccess
                    noValidate
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="batch-number">
                                        Batch number
                                    </Label>
                                    <Input
                                        id="batch-number"
                                        name="number"
                                        placeholder="As printed on the vial"
                                        required
                                    />
                                    <InputError message={errors.number} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="batch-tested-at">
                                        Test date
                                    </Label>
                                    <Input
                                        id="batch-tested-at"
                                        name="tested_at"
                                        type="date"
                                        required
                                    />
                                    <InputError message={errors.tested_at} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="batch-purity">
                                        Purity{' '}
                                        <span className="font-normal text-muted-foreground">
                                            (optional)
                                        </span>
                                    </Label>
                                    <Input
                                        id="batch-purity"
                                        name="purity"
                                        placeholder="e.g. 99.2%"
                                    />
                                    <InputError message={errors.purity} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="batch-lab">
                                        Testing lab{' '}
                                        <span className="font-normal text-muted-foreground">
                                            (optional)
                                        </span>
                                    </Label>
                                    <Input id="batch-lab" name="lab" />
                                    <InputError message={errors.lab} />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="batch-certificate">
                                    COA (PDF)
                                </Label>
                                <Input
                                    id="batch-certificate"
                                    name="certificate"
                                    type="file"
                                    accept="application/pdf"
                                    required
                                />
                                <InputError message={errors.certificate} />
                            </div>
                            <Button variant="outline" disabled={processing}>
                                <Plus />
                                Add batch
                            </Button>
                        </>
                    )}
                </Form>
            </section>
        </div>
    );
}
