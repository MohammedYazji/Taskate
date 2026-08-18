import { Head, router, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import PrimaryButton from '@/Components/PrimaryButton';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    const logout = (e) => {
        e.preventDefault();
        router.post(route('logout'));
    };

    return (
        <GuestLayout>
            <Head title="Verify Email" />

            <div className="mb-4 text-sm text-gray-600">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 font-medium text-sm text-brand-600">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            )}

            <div className="mt-4 flex items-center justify-between">
                <form onSubmit={submit}>
                    <PrimaryButton disabled={processing}>
                        Resend Verification Email
                    </PrimaryButton>
                </form>

                <form onSubmit={logout}>
                    <button
                        type="submit"
                        className="underline text-sm text-brand-600 hover:text-brand-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
                    >
                        Log Out
                    </button>
                </form>
            </div>
        </GuestLayout>
    );
}
