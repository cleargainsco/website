import { Form } from '@inertiajs/react';
import type { ReactNode } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

export function LoginForm({
    canResetPassword,
    autoFocus = false,
    footer,
}: {
    canResetPassword: boolean;
    autoFocus?: boolean;
    footer?: ReactNode;
}) {
    return (
        <Form
            {...store.form()}
            options={{ preserveScroll: true }}
            resetOnSuccess={['password']}
            className="flex flex-col gap-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="login-email">Email address</Label>
                            <Input
                                id="login-email"
                                type="email"
                                name="email"
                                required
                                autoFocus={autoFocus}
                                autoComplete="email"
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center">
                                <Label htmlFor="login-password">Password</Label>
                                {canResetPassword && (
                                    <TextLink
                                        href={request()}
                                        className="ml-auto text-sm"
                                    >
                                        Forgot your password?
                                    </TextLink>
                                )}
                            </div>
                            <PasswordInput
                                id="login-password"
                                name="password"
                                required
                                autoComplete="current-password"
                                placeholder="Password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center space-x-3">
                            <Checkbox id="remember" name="remember" />
                            <Label htmlFor="remember">Remember me</Label>
                        </div>

                        <Button
                            type="submit"
                            className="mt-2 w-full"
                            disabled={processing}
                            data-test="login-button"
                        >
                            {processing && <Spinner />}
                            Log in
                        </Button>
                    </div>

                    {footer}
                </>
            )}
        </Form>
    );
}
