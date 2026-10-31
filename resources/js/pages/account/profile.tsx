import { Form, Link, usePage } from '@inertiajs/react';
import { useRef } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import { AccountPanel } from '@/components/account/account-panel';
import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { send } from '@/routes/verification';

export default function AccountProfile({
    status,
    passwordRules,
}: {
    status?: string;
    passwordRules: string;
}) {
    const { auth } = usePage().props;
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <>
            <div className="space-y-6">
                <AccountPanel
                    title="Your details"
                    description="The name and email we use for your orders."
                >
                    <Form
                        {...ProfileController.update.form()}
                        options={{ preserveScroll: true }}
                        noValidate
                        className="max-w-md space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={auth.user.name}
                                        required
                                        autoComplete="name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        defaultValue={auth.user.email}
                                        required
                                        autoComplete="username"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                {auth.user.email_verified_at === null && (
                                    <div className="-mt-2 text-sm text-charcoal/70">
                                        Your email address isn't verified yet.{' '}
                                        <Link
                                            href={send()}
                                            as="button"
                                            className="cursor-pointer font-medium text-ocean underline underline-offset-4 hover:text-deep-ocean"
                                        >
                                            Resend the verification email
                                        </Link>
                                        {status ===
                                            'verification-link-sent' && (
                                            <p className="mt-2 font-medium text-green-700">
                                                We've sent a new verification
                                                link to your email address.
                                            </p>
                                        )}
                                    </div>
                                )}

                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Save details
                                </Button>
                            </>
                        )}
                    </Form>
                </AccountPanel>

                <AccountPanel
                    title="Password"
                    description="Use a long, unique password you don't use anywhere else."
                >
                    <Form
                        {...SecurityController.update.form()}
                        options={{ preserveScroll: true }}
                        noValidate
                        resetOnError={[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]}
                        resetOnSuccess
                        onError={(errors) => {
                            if (errors.password) {
                                passwordInput.current?.focus();
                            }

                            if (errors.current_password) {
                                currentPasswordInput.current?.focus();
                            }
                        }}
                        className="max-w-md space-y-6"
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">
                                        Current password
                                    </Label>
                                    <PasswordInput
                                        id="current_password"
                                        ref={currentPasswordInput}
                                        name="current_password"
                                        autoComplete="current-password"
                                    />
                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        New password
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        ref={passwordInput}
                                        name="password"
                                        autoComplete="new-password"
                                        passwordrules={passwordRules}
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirm new password
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                        passwordrules={passwordRules}
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>

                                <Button
                                    disabled={processing}
                                    data-test="update-password-button"
                                >
                                    Update password
                                </Button>
                            </>
                        )}
                    </Form>
                </AccountPanel>

                <section className="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-charcoal/5 sm:p-8">
                    <DeleteUser />
                </section>
            </div>
        </>
    );
}
