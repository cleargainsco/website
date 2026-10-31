<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\OrderResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Facades\Head;

final class OrderController extends Controller
{
    public function index(#[CurrentUser] User $user): Response
    {
        return Inertia::render('account/orders/index', [
            'orders' => OrderResource::collection($user->orders()->with('lines')->latest('placed_at')->get()),
        ]);
    }

    /**
     * Read through the customer's own orders, so somebody else's answers 404 rather
     * than confirming it exists.
     */
    public function show(Request $request, #[CurrentUser] User $user, string $order): Response
    {
        $customerOrder = $user->orders()->with('lines')->where('public_id', $order)->firstOrFail();

        Head::title("Order {$customerOrder->number}");

        return Inertia::render('account/orders/show', [
            'order' => $customerOrder->toResource(OrderResource::class),
            'justPlaced' => $request->boolean('placed'),
        ]);
    }
}
