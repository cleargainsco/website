import { Form, Link } from '@inertiajs/react';
import { Search, Users } from 'lucide-react';
import CustomerController from '@/actions/App/Http/Controllers/Admin/CustomerController';
import { Pagination } from '@/components/admin/pagination';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import { index, show } from '@/routes/admin/customers';
import type { Customer } from '@/types/customers';
import type { Paginated } from '@/types/pagination';

export default function CustomersIndex({
    customers,
    search,
}: {
    customers: Paginated<Customer>;
    search: string;
}) {
    return (
        <div className="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <Heading
                    title="Customers"
                    description={`${customers.total} ${customers.total === 1 ? 'customer' : 'customers'}${search ? ` matching “${search}”` : ''}. Spent counts paid and shipped orders.`}
                />
                <Form
                    {...CustomerController.index.form()}
                    options={{ preserveState: true }}
                    className="relative w-full sm:w-72"
                >
                    <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        type="search"
                        name="search"
                        defaultValue={search}
                        placeholder="Search name or email"
                        aria-label="Search customers"
                        className="pl-9"
                    />
                    <button type="submit" className="sr-only">
                        Search
                    </button>
                </Form>
            </div>

            {customers.data.length === 0 ? (
                <div className="flex flex-col items-center rounded-xl border border-dashed px-6 py-16 text-center">
                    <Users className="size-8 text-muted-foreground" />
                    <p className="mt-4 font-medium">
                        {search ? 'No customers match' : 'No customers yet'}
                    </p>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {search
                            ? 'Try a different name or email.'
                            : 'People who create an account show up here.'}
                    </p>
                </div>
            ) : (
                <div className="overflow-x-auto rounded-xl border bg-card shadow-xs">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">
                                    Customer
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Joined
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Orders
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Spent
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Last order
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Marketing
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {customers.data.map((customer) => (
                                <tr
                                    key={customer.id}
                                    className="transition-colors hover:bg-accent/50"
                                >
                                    <td className="px-4 py-3">
                                        <Link
                                            href={show(customer.id)}
                                            className="font-medium hover:underline"
                                        >
                                            {customer.name}
                                        </Link>
                                        <p className="text-xs text-muted-foreground">
                                            {customer.email}
                                            {!customer.email_verified &&
                                                ' · not verified'}
                                        </p>
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                        {customer.joined_at &&
                                            formatDate(customer.joined_at)}
                                    </td>
                                    <td className="px-4 py-3 text-right tabular-nums">
                                        {customer.orders_count}
                                    </td>
                                    <td className="px-4 py-3 text-right tabular-nums">
                                        {formatMoney(customer.spent)}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                        {customer.last_order_at
                                            ? formatDate(customer.last_order_at)
                                            : '—'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {customer.marketing_consent ? (
                                            <Badge variant="secondary">
                                                Subscribed
                                            </Badge>
                                        ) : (
                                            <span className="text-muted-foreground">
                                                —
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            <Pagination page={customers} />
        </div>
    );
}

CustomersIndex.layout = {
    breadcrumbs: [{ title: 'Customers', href: index() }],
};
