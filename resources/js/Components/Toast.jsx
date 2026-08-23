import { usePage } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";

let nextId = 1;

function ToastItem({ type, message, onDismiss }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const raf = requestAnimationFrame(() => setVisible(true));
        return () => cancelAnimationFrame(raf);
    }, []);

    const isSuccess = type === "success";

    return (
        <div
            className={`flex items-start gap-3 rounded-lg px-4 py-3 text-sm text-white shadow-lg transition-all duration-200 ${
                isSuccess ? "bg-emerald-600" : "bg-red-600"
            } ${visible ? "opacity-100 translate-y-0" : "opacity-0 -translate-y-2"}`}
        >
            {isSuccess ? (
                <svg className="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
            ) : (
                <svg className="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
            )}
            <p className="flex-1 leading-snug">{message}</p>
            <button
                onClick={onDismiss}
                className="opacity-70 hover:opacity-100 flex-shrink-0"
                aria-label="Dismiss"
            >
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    );
}

export default function Toast() {
    const { props } = usePage();
    const flash = props.flash || {};
    const [toasts, setToasts] = useState([]);
    const timers = useRef({});

    const dismiss = (id) => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
        clearTimeout(timers.current[id]);
        delete timers.current[id];
    };

    const push = (type, message) => {
        const id = nextId++;
        setToasts((prev) => [...prev, { id, type, message }]);
        timers.current[id] = setTimeout(() => dismiss(id), 4000);
    };

    useEffect(() => {
        if (flash.success) push("success", flash.success);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [flash.success]);

    useEffect(() => {
        if (flash.error) push("error", flash.error);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [flash.error]);

    useEffect(() => {
        return () => {
            Object.values(timers.current).forEach(clearTimeout);
        };
    }, []);

    if (toasts.length === 0) return null;

    return (
        <div className="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)] pointer-events-none">
            {toasts.map((toast) => (
                <div key={toast.id} className="pointer-events-auto">
                    <ToastItem type={toast.type} message={toast.message} onDismiss={() => dismiss(toast.id)} />
                </div>
            ))}
        </div>
    );
}
