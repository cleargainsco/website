import { Head } from '@inertiajs/react';
import { AccountPanel } from '@/components/account/account-panel';
import { NoOrders } from '@/components/account/no-orders';

export default function AccountOrders() {
    return (
        <>
            <Head title="Orders" />

            <AccountPanel
                title="Orders"
                description="Your order history, status and tracking."
            >
                <NoOrders />
            </AccountPanel>
        </>
    );
}
