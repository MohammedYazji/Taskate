import { useState } from "react";
import { router, usePage } from "@inertiajs/react";

export default function VerificationBanner() {
    const { auth } = usePage().props;
    const [sending, setSending] = useState(false);
    const [sent, setSent] = useState(false);

    if (auth?.user?.email_verified_at) return null;

    const resend = () => {
        setSending(true);
        router.post(route("verification.send"), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => { setSent(true); setSending(false); },
            onError: () => setSending(false),
        });
    };

    return (
        <div className="bg-amber-50 border-b border-amber-200 px-6 py-2.5 flex items-center gap-3 text-sm text-amber-800">
            <svg className="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span className="flex-1">
                {sent
                    ? "A new verification link has been sent to your email."
                    : "Please verify your email address to enable all features."
                }
            </span>
            {!sent && (
                <button
                    onClick={resend}
                    disabled={sending}
                    className="text-sm font-medium text-amber-700 hover:text-amber-900 underline underline-offset-2 disabled:opacity-50"
                >
                    {sending ? "Sending..." : "Resend verification email"}
                </button>
            )}
        </div>
    );
}
