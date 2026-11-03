import { LoginForm } from '@/components/auth/login-form';
import PasskeyVerify from '@/components/passkey-verify';
import TextLink from '@/components/text-link';
import { register } from '@/routes';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    return (
        <>
            <PasskeyVerify />

            <LoginForm
                canResetPassword={canResetPassword}
                autoFocus
                footer={
                    <div className="text-center text-sm text-muted-foreground">
                        Don't have an account?{' '}
                        <TextLink href={register()}>Sign up</TextLink>
                    </div>
                }
            />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </>
    );
}

Login.layout = {
    title: 'Log in to your account',
    description: 'Enter your email and password below to log in',
};
