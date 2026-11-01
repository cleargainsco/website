import { Link } from '@inertiajs/react';
import { Users } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/dates';
import { index } from '@/routes/admin/subscribers';
import type { Paginated, SubscriberRow } from '@/types/marketing';

export default function SubscribersIndex({
    subscribers,
    totals,
    resend_configured,
}: {
    subscribers: Paginated<SubscriberRow>;
    totals: { all: number; consented: number; synced: number };
    resend_configured: boolean;
}) {
    return (
        <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <Heading
                title="Subscribers"
                description={`${totals.all} on the list · ${totals.consented} agreed to marketing emails · ${totals.synced} in Resend. Send campaigns from Resend.`}
            />

            {!resend_configured && (
                <p className="rounded-xl border border-amber-300/60 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                    Subscribers aren't being sent to Resend yet. Set
                    RESEND_GENERAL_SEGMENT_ID (and
                    RESEND_WELCOME_OFFER_SEGMENT_ID) to the segment IDs from
                    Resend's Audience page; everyone already on the list who
                    agreed to marketing emails is sent within the hour.
                </p>
            )}

            {subscribers.data.length === 0 ? (
                <div className="flex flex-col items-center rounded-xl border border-dashed px-6 py-16 text-center">
                    <Users className="size-8 text-muted-foreground" />
                    <p className="mt-4 font-medium">No subscribers yet</p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        People who sign up through the welcome offer, or tick
                        the box when they register, show up here.
                    </p>
                </div>
            ) : (
                <div className="overflow-x-auto rounded-xl border bg-card shadow-xs">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">Email</th>
                                <th className="px-4 py-3 font-medium">
                                    Signed up
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Welcome code
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Marketing
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {subscribers.data.map((subscriber) => (
                                <tr key={subscriber.id}>
                                    <td className="px-4 py-3">
                                        <p className="font-medium">
                                            {subscriber.email}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {subscriber.has_account
                                                ? 'Has an account'
                                                : 'No account'}
                                        </p>
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap">
                                        {subscriber.created_at &&
                                            formatDate(subscriber.created_at)}
                                        <p className="text-xs text-muted-foreground">
                                            {subscriber.source_label}
                                        </p>
                                    </td>
                                    <td className="px-4 py-3">
                                        {subscriber.discount_code ? (
                                            <>
                                                <p className="font-mono text-xs">
                                                    {subscriber.discount_code}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {subscriber.discount_code_used
                                                        ? 'Redeemed'
                                                        : 'Not used yet'}
                                                </p>
                                            </>
                                        ) : (
                                            <span className="text-muted-foreground">
                                                —
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        {subscriber.marketing_consent_at ===
                                        null ? (
                                            <Badge variant="outline">
                                                No consent
                                            </Badge>
                                        ) : subscriber.synced_to_resend_at ? (
                                            <Badge variant="secondary">
                                                In Resend
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline">
                                                Waiting to sync
                                            </Badge>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {subscribers.last_page > 1 && (
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        Page {subscribers.current_page} of{' '}
                        {subscribers.last_page}
                    </span>
                    <div className="flex gap-2">
                        {subscribers.prev_page_url && (
                            <Button variant="outline" size="sm" asChild>
                                <Link href={subscribers.prev_page_url}>
                                    Previous
                                </Link>
                            </Button>
                        )}
                        {subscribers.next_page_url && (
                            <Button variant="outline" size="sm" asChild>
                                <Link href={subscribers.next_page_url}>
                                    Next
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

SubscribersIndex.layout = {
    breadcrumbs: [{ title: 'Subscribers', href: index() }],
};
