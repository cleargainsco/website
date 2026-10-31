import { Head, Link, usePage } from '@inertiajs/react';
import { Mail } from 'lucide-react';
import { AccountPanel } from '@/components/account/account-panel';
import { NoOrders } from '@/components/account/no-orders';
import { OrderList } from '@/components/account/order-list';
import { brandButton } from '@/lib/brand-button';
import { index as ordersIndex } from '@/routes/account/orders';
import { edit as editProfile } from '@/routes/account/profile';
import type { Order } from '@/types/shop';

export default function AccountOverview({
    recentOrders,
}: {
    recentOrders: Order[];
}) {
    const { auth, supportEmail } = usePage().props;

    return (
        <>
            <Head title="My account" />

            <div className="grid gap-6 xl:grid-cols-[1fr_20rem]">
                <AccountPanel
                    title="Recent orders"
                    action={
                        <Link
                            href={ordersIndex()}
                            className="text-sm font-medium text-ocean hover:text-deep-ocean"
                        >
                            View all
                        </Link>
                    }
                >
                    {recentOrders.length === 0 ? (
                        <NoOrders />
                    ) : (
                        <OrderList orders={recentOrders} />
                    )}
                </AccountPanel>

                <div className="space-y-6">
                    <AccountPanel title="Your details">
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="text-charcoal/50">Name</dt>
                                <dd className="font-medium">
                                    {auth.user.name}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-charcoal/50">Email</dt>
                                <dd className="font-medium break-all">
                                    {auth.user.email}
                                </dd>
                            </div>
                        </dl>
                        <Link
                            href={editProfile()}
                            className={brandButton({
                                variant: 'secondary',
                                size: 'sm',
                                className: 'mt-6',
                            })}
                        >
                            Edit profile
                        </Link>
                    </AccountPanel>

                    <AccountPanel title="Need help?">
                        <p className="text-sm text-charcoal/70">
                            Questions about an order or a product? Email us.
                        </p>
                        <a
                            href={`mailto:${supportEmail}`}
                            className="mt-4 inline-flex items-center gap-2 text-sm font-medium break-all text-ocean hover:text-deep-ocean"
                        >
                            <Mail className="size-4 shrink-0" />
                            {supportEmail}
                        </a>
                    </AccountPanel>
                </div>
            </div>
        </>
    );
}
