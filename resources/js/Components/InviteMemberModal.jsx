import { useEffect, useRef, useState } from "react";
import { router } from "@inertiajs/react";

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}

export default function InviteMemberModal({ project, onClose }) {
    const [query, setQuery] = useState("");
    const [results, setResults] = useState([]);
    const [selected, setSelected] = useState(null);
    const [role, setRole] = useState("editor");
    const [error, setError] = useState("");
    const debounceRef = useRef(null);

    useEffect(() => {
        clearTimeout(debounceRef.current);
        if (query.trim().length < 2) {
            setResults([]);
            return;
        }
        debounceRef.current = setTimeout(() => {
            fetch(`/projects/${project.id}/members/search?q=${encodeURIComponent(query)}`, {
                headers: { "X-CSRF-TOKEN": csrfToken(), "X-Requested-With": "XMLHttpRequest" },
            })
                .then((res) => res.json())
                .then(setResults)
                .catch(() => {});
        }, 300);
        return () => clearTimeout(debounceRef.current);
    }, [query, project.id]);

    const submit = (e) => {
        e.preventDefault();
        if (!selected) return;
        router.post(
            `/projects/${project.id}/invitations`,
            { email: selected.email, role },
            {
                preserveScroll: true,
                onSuccess: () => onClose(),
                onError: (errors) => setError(errors.email || "Something went wrong"),
            },
        );
    };

    return (
        <>
            <div className="fixed inset-0 bg-black/40 z-[100]" onClick={onClose} />
            <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div className="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5">
                    <h3 className="text-sm font-semibold text-gray-900 mb-3">Invite to project</h3>
                    <form onSubmit={submit}>
                        <div className="relative">
                            <input
                                type="text"
                                autoFocus
                                value={selected ? selected.name : query}
                                onChange={(e) => {
                                    setSelected(null);
                                    setQuery(e.target.value);
                                }}
                                placeholder="Search by name or email..."
                                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            />
                            {results.length > 0 && !selected && (
                                <div className="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-40 overflow-y-auto">
                                    {results.map((user) => (
                                        <button
                                            key={user.id}
                                            type="button"
                                            onClick={() => {
                                                setSelected(user);
                                                setResults([]);
                                            }}
                                            className="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 transition"
                                        >
                                            <div className="font-medium text-gray-800">{user.name}</div>
                                            <div className="text-gray-400">{user.email}</div>
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="flex items-center gap-2 mt-3">
                            <label className="text-xs text-gray-500">Role</label>
                            <select
                                value={role}
                                onChange={(e) => setRole(e.target.value)}
                                className="border border-gray-200 rounded-lg px-2 py-1 text-xs outline-none focus:ring-2 focus:ring-brand-500"
                            >
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                            </select>
                        </div>

                        {error && <p className="text-xs text-red-500 mt-2">{error}</p>}

                        <div className="flex items-center justify-end gap-2 mt-5">
                            <button
                                type="button"
                                onClick={onClose}
                                className="px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50 rounded-lg transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={!selected}
                                className="px-3 py-1.5 text-xs font-medium text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                Send invite
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
