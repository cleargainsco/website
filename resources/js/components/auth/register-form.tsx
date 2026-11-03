import { Form } from '@inertiajs/react';
import type { ReactNode } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/register';

export function RegisterForm({
    passwordRules,
    autoFocus = false,
    footer,
}: {
    passwordRules: string;
    autoFocus?: boolean;
    footer?: ReactNode;
}) {
    return (
        <Form
            {...store.form()}
            options={{ preserveScroll: true }}
            resetOnSuccess={['password', 'password_confirmation']}
            disableWhileProcessing
            className="flex flex-col gap-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="register-name">Name</Label>
                            <Input
                                id="register-name"
                                type="text"
                                required
                                autoFocus={autoFocus}
                                autoComplete="name"
                                name="name"
                                placeholder="Full name"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="register-email">
                                Email address
                            </Label>
                            <Input
                                id="register-email"
                                type="email"
                                required
                                autoComplete="email"
                                name="email"
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="register-password">Password</Label>
                            <PasswordInput
                                id="register-password"
                                required
                                autoComplete="new-password"
                                name="password"
                                placeholder="Password"
                                passwordrules={passwordRules}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="register-password-confirmation">
                                Confirm password
                            </Label>
                            <PasswordInput
                                id="register-password-confirmation"
                                required
                                autoComplete="new-password"
                                name="password_confirmation"
                                placeholder="Confirm password"
                                passwordrules={passwordRules}
                            />
                            <InputError
                                message={errors.password_confirmation}
                            />
                        </div>

                        <label className="flex items-start gap-3 text-sm">
                            <Checkbox
                                name="marketing_opt_in"
                                value="1"
                                className="mt-0.5"
                            />
                            <span className="text-muted-foreground">
                                Email me offers, restocks and new arrivals.
                                Unsubscribe anytime.
                            </span>
                        </label>

                        <Button
                            type="submit"
                            className="mt-2 w-full"
                            data-test="register-user-button"
                        >
                            {processing && <Spinner />}
                            Create account
                        </Button>
                    </div>

                    {footer}
                </>
            )}
        </Form>
    );
}
