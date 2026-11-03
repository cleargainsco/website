import { RegisterForm } from '@/components/auth/register-form';
import TextLink from '@/components/text-link';
import { login } from '@/routes';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    return (
        <RegisterForm
            passwordRules={passwordRules}
            autoFocus
            footer={
                <div className="text-center text-sm text-muted-foreground">
                    Already have an account?{' '}
                    <TextLink href={login()}>Log in</TextLink>
                </div>
            }
        />
    );
}

Register.layout = {
    title: 'Create an account',
    description: 'Enter your details below to create your account',
};
