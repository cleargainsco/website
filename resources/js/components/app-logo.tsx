import { usePage } from '@inertiajs/react';

import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <AppLogoIcon className="h-6 w-9 shrink-0" />
            <div className="ml-1 grid flex-1 text-left">
                <span className="truncate font-serif text-base leading-tight font-semibold">
                    {name}
                </span>
            </div>
        </>
    );
}
