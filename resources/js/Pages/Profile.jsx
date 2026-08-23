import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';

export default function Profile({ user, mustVerifyEmail, sessions }) {
    return (
        <div className="max-w-2xl mx-auto px-6 py-8 space-y-6">
                <h1 className="text-2xl font-bold text-gray-900">Profile</h1>

                <UpdateProfileForm user={user} mustVerifyEmail={mustVerifyEmail} />
                <UpdatePasswordForm />
                <DeleteUserForm />
        </div>
    );
}

function UpdateProfileForm({ user, mustVerifyEmail }) {
    const { data, setData, patch, processing, recentlySuccessful } = useForm({
        name: user.name,
        email: user.email,
    });

    const submit = (e) => {
        e.preventDefault();
        patch('/profile');
    };

    return (
        <div className="bg-white rounded-xl border border-gray-200 p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>
            <p className="text-sm text-gray-500 mb-4">Update your account's profile information and email address.</p>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="name" value="Name" />
                    <TextInput id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} className="mt-1 block w-full" required />
                </div>
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="mt-1 block w-full" required />
                </div>
                {mustVerifyEmail && (
                    <p className="text-sm text-gray-500">Your email address is unverified. Please verify your email.</p>
                )}
                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Save</PrimaryButton>
                    {recentlySuccessful && <p className="text-sm text-gray-600">Saved.</p>}
                </div>
            </form>
        </div>
    );
}

function UpdatePasswordForm() {
    const { data, setData, put, processing, reset, errors, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        put('/password', { onSuccess: () => reset() });
    };

    return (
        <div className="bg-white rounded-xl border border-gray-200 p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Update Password</h2>
            <p className="text-sm text-gray-500 mb-4">Ensure your account is using a long, random password to stay secure.</p>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="current_password" value="Current Password" />
                    <TextInput id="current_password" type="password" value={data.current_password} onChange={(e) => setData('current_password', e.target.value)} className="mt-1 block w-full" required />
                    <InputError message={errors.current_password} className="mt-2" />
                </div>
                <div>
                    <InputLabel htmlFor="password" value="New Password" />
                    <TextInput id="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} className="mt-1 block w-full" required />
                    <InputError message={errors.password} className="mt-2" />
                </div>
                <div>
                    <InputLabel htmlFor="password_confirmation" value="Confirm Password" />
                    <TextInput id="password_confirmation" type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} className="mt-1 block w-full" required />
                </div>
                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Update</PrimaryButton>
                    {recentlySuccessful && <p className="text-sm text-gray-600">Updated.</p>}
                </div>
            </form>
        </div>
    );
}

function DeleteUserForm() {
    const { data, setData, delete: destroy, processing, reset, errors } = useForm({ password: '' });

    const submit = (e) => {
        e.preventDefault();
        destroy('/profile', { onSuccess: () => reset() });
    };

    return (
        <div className="bg-white rounded-xl border border-gray-200 p-6">
            <h2 className="text-lg font-semibold text-gray-900 mb-4">Delete Account</h2>
            <p className="text-sm text-gray-500 mb-4">Permanently delete your account. Once deleted, all resources will be permanently deleted.</p>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="password" value="Password" />
                    <TextInput id="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} className="mt-1 block w-full" required />
                    <InputError message={errors.password} className="mt-2" />
                </div>
                <DangerButton disabled={processing}>Delete Account</DangerButton>
            </form>
        </div>
    );
}
