<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\UpdateHomePageAction;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateHomePageRequest;
use App\Models\HomePage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class HomePageController extends Controller
{
    public function edit(): Response
    {
        $this->authorize(Permission::ContentManage->value);

        return Inertia::render('admin/pages/home', [
            'hero' => HomePage::current()->hero(),
        ]);
    }

    public function update(UpdateHomePageRequest $request, UpdateHomePageAction $action): RedirectResponse
    {
        $action->handle($request->toHomePageData());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Saved the home page.')]);

        return back();
    }
}
