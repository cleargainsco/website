<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminOrderResource;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Facades\Head;
use Shop\Enums\OrderStatus;

final class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize(Permission::CustomersManage->value);

        $search = $request->string('search')->trim()->toString();

        $customers = $this->customers()
            ->when($search !== '', fn (Builder $query) => $query->whereAny(['name', 'email'], 'like', "%{$search}%"))
            ->latest()
            ->latest('id')
            ->paginate(50)
            ->withQueryString()
            ->through(fn (User $customer): array => (new CustomerResource($customer))->resolve($request));

        return Inertia::render('admin/customers/index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    public function show(User $customer): Response
    {
        $this->authorize(Permission::CustomersManage->value);

        $customer = $this->customers()->with('subscriber.discountCode')->whereKey($customer->id)->firstOrFail();

        Head::title($customer->name);

        return Inertia::render('admin/customers/show', [
            'customer' => $customer->toResource(CustomerResource::class),
            'welcome_code' => $customer->subscriber?->discountCode === null ? null : [
                'code' => $customer->subscriber->discountCode->code,
                'used' => $customer->subscriber->discountCode->isUsedUp(),
                'expired' => $customer->subscriber->discountCode->isExpired(),
            ],
            'orders' => AdminOrderResource::collection(
                $customer->orders()->with(['lines', 'customer'])->latest('placed_at')->latest('id')->get(),
            ),
        ]);
    }

    /**
     * Customers only, so an admin's own account never shows up here, with the figures
     * CustomerResource reads.
     *
     * @return Builder<User>
     */
    private function customers(): Builder
    {
        return User::query()
            ->role(RoleName::Customer->value)
            ->with('subscriber')
            ->withCount('orders')
            ->withMax('orders', 'placed_at')
            ->withSum(['orders as spent_minor' => fn (Builder $query) => $query->whereIn('status', [OrderStatus::Paid, OrderStatus::Shipped])], 'total_minor');
    }
}
