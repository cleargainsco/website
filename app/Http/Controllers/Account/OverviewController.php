<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Shop\OrderResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\Response;

final class OverviewController extends Controller
{
    public function index(#[CurrentUser] User $user): Response
    {
        return Inertia::render('account/index', [
            'recentOrders' => OrderResource::collection($user->orders()->with('lines')->latest('placed_at')->limit(3)->get()),
        ]);
    }
}
