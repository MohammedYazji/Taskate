import { useState, useRef, useEffect } from "react";
import { usePage, router } from "@inertiajs/react";
import Sortable from "sortablejs";
import ApplicationLogo from "@/Components/ApplicationLogo";
import Toast from "@/Components/Toast";
import VerificationBanner from "@/Components/VerificationBanner";
import NotificationBell from "@/Components/NotificationBell";
import usePresence from "@/hooks/usePresence";

const SIDEBAR_NAV = [
    {
        name: "Dashboard",
        route: "dashboard",
        path: "/dashboard",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                />
            </svg>
        ),
    },
    {
        name: "Projects",
        route: "projects.index",
        path: "/projects",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                />
            </svg>
        ),
    },
    {
        name: "Matrix",
        route: "eisenhower.index",
        path: "/eisenhower",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                />
            </svg>
        ),
    },
    {
        name: "Pomodoro",
        route: "pomodoro.index",
        path: "/pomodoro",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        ),
    },
    {
        name: "Habits",
        route: "habits.index",
        path: "/habits",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
        ),
    },
    {
        name: "AI",
        route: "ai.form",
        path: "/ai",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"
                />
            </svg>
        ),
    },
];

const SIDEBAR_BOTTOM_NAV = [
    {
        name: "Tags",
        route: "tags.index",
        path: "/tags",
        icon: (
            <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="1.5"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
                />
            </svg>
        ),
    },
];

const COLORS = [
    "#14B8A6",
    "#3B82F6",
    "#8B5CF6",
    "#EF4444",
    "#F59E0B",
    "#EC4899",
    "#6366F1",
    "#10B981",
    "#F97316",
    "#06B6D4",
];

function Sidebar({ sidebar }) {
    const { url, props } = usePage();
    const user = props.auth.user;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const onlineUsers = usePresence('online');

    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const [foldersOpen, setFoldersOpen] = useState({});
    const [listMenuOpen, setListMenuOpen] = useState(null);
    const [folderMenuOpen, setFolderMenuOpen] = useState(null);
    const [tagMenuOpen, setTagMenuOpen] = useState(null);

    const [listModalOpen, setListModalOpen] = useState(false);
    const [listModalMode, setListModalMode] = useState("add");
    const [listForm, setListForm] = useState({
        id: null,
        name: "",
        color: "#14B8A6",
        icon: "👋",
        view_type: "list",
        folder_id: null,
    });

    const [tagModalOpen, setTagModalOpen] = useState(false);
    const [tagModalMode, setTagModalMode] = useState("add");
    const [tagForm, setTagForm] = useState({
        id: null,
        name: "",
        color: "#14B8A6",
        icon: "",
        parent_id: null,
    });

    const [renameModalOpen, setRenameModalOpen] = useState(false);
    const [renameFolderId, setRenameFolderId] = useState(null);
    const [renameFolderName, setRenameFolderName] = useState("");

    const openAddList = (folderId) => {
        setListModalMode("add");
        setListForm({
            id: null,
            name: "",
            color: "#14B8A6",
            icon: "👋",
            view_type: "list",
            folder_id: folderId || null,
        });
        setListModalOpen(true);
    };

    const openEditList = (project) => {
        setListModalMode("edit");
        setListForm({
            id: project.id,
            name: project.name,
            color: project.color || "#14B8A6",
            icon: project.icon || "👋",
            view_type: project.view_type || "list",
            folder_id: project.folder_id,
        });
        setListModalOpen(true);
    };

    const saveList = () => {
        if (!listForm.name.trim()) return;
        const payload = {
            name: listForm.name,
            color: listForm.color,
            icon: listForm.icon,
            view_type: listForm.view_type,
            folder_id: listForm.folder_id,
        };
        if (listModalMode === "add") {
            router.post("/projects", payload, {
                preserveScroll: true,
                onSuccess: () => setListModalOpen(false),
            });
        } else {
            router.patch(`/projects/${listForm.id}`, payload, {
                preserveScroll: true,
                onSuccess: () => setListModalOpen(false),
            });
        }
    };

    const deleteList = (project) => {
        if (!confirm("Delete this list?")) return;
        router.delete(`/projects/${project.id}`, { preserveScroll: true });
    };

    const pinList = (project) => {
        router.patch(
            `/projects/${project.id}/pin`,
            {},
            { preserveScroll: true },
        );
    };

    const duplicateList = (project) => {
        router.post(
            `/projects/${project.id}/duplicate`,
            {},
            { preserveScroll: true },
        );
    };

    const openAddTag = () => {
        setTagModalMode("add");
        setTagForm({
            id: null,
            name: "",
            color: "#14B8A6",
            icon: "",
            parent_id: null,
        });
        setTagModalOpen(true);
    };

    const openEditTag = (tag) => {
        setTagModalMode("edit");
        setTagForm({
            id: tag.id,
            name: tag.name,
            color: tag.color,
            icon: tag.icon || "",
            parent_id: tag.parent_id,
        });
        setTagModalOpen(true);
    };

    const saveTag = () => {
        if (!tagForm.name.trim()) return;
        const payload = {
            name: tagForm.name,
            color: tagForm.color,
            icon: tagForm.icon || null,
            parent_id: tagForm.parent_id || null,
        };
        if (tagModalMode === "add") {
            router.post("/tags", payload, {
                preserveScroll: true,
                onSuccess: () => setTagModalOpen(false),
            });
        } else {
            router.patch(`/tags/${tagForm.id}`, payload, {
                preserveScroll: true,
                onSuccess: () => setTagModalOpen(false),
            });
        }
    };

    const deleteTag = (tag) => {
        if (!confirm("Delete this tag?")) return;
        router.delete(`/tags/${tag.id}`, { preserveScroll: true });
    };

    const openRenameFolder = (folder) => {
        setRenameFolderId(folder.id);
        setRenameFolderName(folder.name);
        setRenameModalOpen(true);
    };

    const renameFolder = () => {
        if (!renameFolderName.trim()) return;
        router.patch(
            `/folders/${renameFolderId}`,
            { name: renameFolderName },
            {
                preserveScroll: true,
                onSuccess: () => setRenameModalOpen(false),
            },
        );
    };

    const deleteFolder = (folder) => {
        if (!confirm("Delete this folder? Lists will be moved to ungrouped."))
            return;
        router.delete(`/folders/${folder.id}`, { preserveScroll: true });
    };

    const toggleFolder = (id) => {
        setFoldersOpen((prev) => ({
            ...prev,
            [id]: prev[id] === false ? true : false,
        }));
    };

    const foldersRef = useRef(null);
    const folderProjectsRef = useRef({});
    const ungroupedRef = useRef(null);

    useEffect(() => {
        const instances = [];

        if (foldersRef.current) {
            const f = new Sortable(foldersRef.current, {
                animation: 150,
                handle: ".folder-drag-handle",
                onEnd: (evt) => {
                    const order = f.toArray().map(Number);
                    const payload = order.map((id, idx) => ({ id, position: idx }));
                    fetch("/folders/reorder", {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({ folders: payload }),
                    });
                },
            });
            instances.push(f);
        }

        Object.entries(folderProjectsRef.current).forEach(([folderId, el]) => {
            if (!el) return;
            const s = new Sortable(el, {
                group: "sidebar-projects",
                animation: 150,
                handle: ".project-drag-handle",
                onEnd: (evt) => {
                    const fromItems = Sortable.get(evt.from).toArray().map(Number);
                    const toItems = Sortable.get(evt.to).toArray().map(Number);
                    const fromFolderId = evt.from.dataset.folderId ? Number(evt.from.dataset.folderId) : null;
                    const toFolderId = evt.to.dataset.folderId ? Number(evt.to.dataset.folderId) : null;
                    const seen = new Set();
                    const payload = [
                        ...fromItems.map((id, idx) => ({ id, position: idx, folder_id: fromFolderId })),
                        ...toItems.map((id, idx) => ({ id, position: idx, folder_id: toFolderId })),
                    ].filter((item) => {
                        if (seen.has(item.id)) return false;
                        seen.add(item.id);
                        return true;
                    });
                    fetch("/projects/reorder", {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({ projects: payload }),
                    });
                },
            });
            instances.push(s);
        });

        if (ungroupedRef.current) {
            const u = new Sortable(ungroupedRef.current, {
                group: "sidebar-projects",
                animation: 150,
                handle: ".project-drag-handle",
                onEnd: (evt) => {
                    const fromItems = Sortable.get(evt.from).toArray().map(Number);
                    const toItems = Sortable.get(evt.to).toArray().map(Number);
                    const fromFolderId = evt.from.dataset.folderId ? Number(evt.from.dataset.folderId) : null;
                    const toFolderId = evt.to.dataset.folderId ? Number(evt.to.dataset.folderId) : null;
                    const seen = new Set();
                    const payload = [
                        ...fromItems.map((id, idx) => ({ id, position: idx, folder_id: fromFolderId })),
                        ...toItems.map((id, idx) => ({ id, position: idx, folder_id: toFolderId })),
                    ].filter((item) => {
                        if (seen.has(item.id)) return false;
                        seen.add(item.id);
                        return true;
                    });
                    fetch("/projects/reorder", {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({ projects: payload }),
                    });
                },
            });
            instances.push(u);
        }

        return () => instances.forEach((s) => s.destroy());
    }, [sidebar]);

    return (
        <>
        <aside className="flex h-screen flex-shrink-0">
            {/* Icon Rail */}
            <div className="w-14 bg-gray-100 flex flex-col items-center py-4 gap-1 flex-shrink-0 border-r border-gray-200">
                {/* Mobile hamburger */}
                <button
                    onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                    className="lg:hidden w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 transition mb-2"
                    title="Toggle menu"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {mobileMenuOpen ? (
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        ) : (
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                        )}
                    </svg>
                </button>
                <a href={route("dashboard")} className="mb-4" title="Taskate">
                    <img src="/logo.svg" alt="Taskate" className="w-9 h-9" />
                </a>
                <nav className="flex-1 flex flex-col items-center gap-1 w-full px-2">
                    {SIDEBAR_NAV.map((item) => (
                        <a
                            key={item.route}
                            href={route(item.route)}
                            title={item.name}
                            className={`w-10 h-10 rounded-xl flex items-center justify-center transition ${
                                url.startsWith(item.path)
                                    ? "bg-brand-50 text-brand-600"
                                    : "text-gray-400 hover:text-gray-700 hover:bg-gray-200/60"
                            }`}
                        >
                            {item.icon}
                        </a>
                    ))}
                </nav>
                <div className="flex flex-col items-center gap-1 px-2">
                    {SIDEBAR_BOTTOM_NAV.map((item) => (
                        <a
                            key={item.route}
                            href={route(item.route)}
                            title={item.name}
                            className={`w-10 h-10 rounded-xl flex items-center justify-center transition ${
                                url.startsWith(item.path)
                                    ? "bg-brand-50 text-brand-600"
                                    : "text-gray-400 hover:text-gray-700 hover:bg-gray-200/60"
                            }`}
                        >
                            {item.icon}
                        </a>
                    ))}
                    <NotificationBell userId={user.id} />
                </div>
            </div>

            {/* Context Panel */}
            <div
                className={`bg-white flex flex-col h-screen flex-shrink-0 border-r border-gray-200 transition-all duration-200 overflow-hidden ${
                    mobileMenuOpen
                        ? "fixed inset-y-0 left-14 z-50 w-64 shadow-xl"
                        : "hidden lg:flex w-64"
                }`}
            >
                <div className="flex-1 overflow-y-auto px-2 pb-4 space-y-4 pt-4">
                    {/* Smart Views */}
                    <div>
                        {[
                            {
                                name: "Today",
                                route: "smart.today",
                                path: "/today",
                                count: null,
                            },
                            {
                                name: "Next 7 Days",
                                route: "smart.next7days",
                                path: "/next7days",
                                count: sidebar?.next7Count,
                            },
                            {
                                name: "Inbox",
                                route: "smart.inbox",
                                path: "/inbox",
                                count: sidebar?.inboxCount,
                            },
                        ].map((sv) => (
                            <a
                                key={sv.route}
                                href={route(sv.route)}
                                className={`flex items-center gap-3 px-3 py-2 rounded-lg transition ${url === sv.path ? "bg-brand-50 text-brand-600" : "text-gray-600 hover:text-gray-900 hover:bg-gray-50"}`}
                            >
                                <svg
                                    className="w-4 h-4 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="1.5"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span className="flex-1 text-sm font-medium">
                                    {sv.name}
                                </span>
                                {sv.count > 0 && (
                                    <span className="text-[10px] font-semibold bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded-full">
                                        {sv.count}
                                    </span>
                                )}
                            </a>
                        ))}
                    </div>

                    {/* Lists Header */}
                    <div>
                        <div className="flex items-center justify-between px-3 mb-1">
                            <h3 className="text-[10px] font-semibold uppercase tracking-widest text-gray-400">
                                Lists
                            </h3>
                            <div className="flex items-center gap-1">
                                <span className="text-[10px] text-gray-400">
                                    {sidebar?.projects?.length || 0}
                                </span>
                                <button
                                    onClick={() => openAddList()}
                                    className="w-4 h-4 rounded flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                                    title="Add List"
                                >
                                    <svg
                                        className="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {/* Folders */}
                        <div ref={foldersRef}>
                        {sidebar?.folders?.map((folder) => {
                            const folderProjects =
                                sidebar.projects?.filter(
                                    (p) => p.folder_id === folder.id,
                                ) || [];
                            return (
                                <div key={folder.id} data-id={folder.id} className="mb-1">
                                    <div className="flex items-center gap-1 px-2 py-1.5 text-xs font-medium text-gray-500 group/folder">
                                        <span className="folder-drag-handle cursor-grab active:cursor-grabbing p-0.5 text-gray-300 hover:text-gray-500 rounded transition opacity-0 group-hover/folder:opacity-100">
                                            <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" /></svg>
                                        </span>
                                        <button
                                            onClick={() =>
                                                toggleFolder(folder.id)
                                            }
                                            className="w-4 h-4 flex items-center justify-center rounded transition text-gray-400 hover:text-gray-600"
                                        >
                                            <svg
                                                className={`w-3 h-3 transition-transform ${foldersOpen[folder.id] === false ? "-rotate-90" : ""}`}
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M19 9l-7 7-7-7"
                                                />
                                            </svg>
                                        </button>
                                        <span className="flex-1 truncate">
                                            {folder.name}
                                        </span>
                                        <div
                                            className="relative"
                                            onClick={(e) => e.stopPropagation()}
                                        >
                                            <button
                                                onClick={() =>
                                                    setFolderMenuOpen(
                                                        folderMenuOpen ===
                                                            folder.id
                                                            ? null
                                                            : folder.id,
                                                    )
                                                }
                                                className="opacity-0 group-hover/folder:opacity-100 w-5 h-5 flex items-center justify-center rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                                            >
                                                <svg
                                                    className="w-3.5 h-3.5"
                                                    fill="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        cx="12"
                                                        cy="5"
                                                        r="1.5"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="1.5"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="19"
                                                        r="1.5"
                                                    />
                                                </svg>
                                            </button>
                                            {folderMenuOpen === folder.id && (
                                                <>
                                                    <div
                                                        className="fixed inset-0 z-40"
                                                        onClick={() =>
                                                            setFolderMenuOpen(
                                                                null,
                                                            )
                                                        }
                                                    ></div>
                                                    <div className="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                                        <button
                                                            onClick={() => {
                                                                openAddList(
                                                                    folder.id,
                                                                );
                                                                setFolderMenuOpen(
                                                                    null,
                                                                );
                                                            }}
                                                            className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                        >
                                                            <svg
                                                                className="w-3.5 h-3.5"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth="2"
                                                                    d="M12 4v16m8-8H4"
                                                                />
                                                            </svg>
                                                            Add List
                                                        </button>
                                                        <button
                                                            onClick={() => {
                                                                openRenameFolder(
                                                                    folder,
                                                                );
                                                                setFolderMenuOpen(
                                                                    null,
                                                                );
                                                            }}
                                                            className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                        >
                                                            <svg
                                                                className="w-3.5 h-3.5"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                                />
                                                            </svg>
                                                            Edit
                                                        </button>
                                                        <div className="border-t border-gray-100 my-0.5"></div>
                                                        <button
                                                            onClick={() => {
                                                                deleteFolder(
                                                                    folder,
                                                                );
                                                                setFolderMenuOpen(
                                                                    null,
                                                                );
                                                            }}
                                                            className="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition"
                                                        >
                                                            <svg
                                                                className="w-3.5 h-3.5"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                />
                                                            </svg>
                                                            Ungroup
                                                        </button>
                                                    </div>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                    {foldersOpen[folder.id] !== false && (
                                        <div
                                            ref={(el) => { if (el) folderProjectsRef.current[folder.id] = el; }}
                                            data-folder-id={folder.id}
                                        >
                                        {folderProjects.map((project) => (
                                            <div
                                                key={project.id}
                                                data-id={project.id}
                                                className="group relative flex items-center gap-1 pl-6 pr-3 py-1.5 rounded-lg transition text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                                            >
                                                <span className="project-drag-handle cursor-grab active:cursor-grabbing p-0.5 text-gray-300 hover:text-gray-500 rounded transition opacity-0 group-hover:opacity-100 flex-shrink-0">
                                                    <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" /></svg>
                                                </span>
                                                <a
                                                    href={route(
                                                        "projects.show",
                                                        project.id,
                                                    )}
                                                    className="flex items-center gap-2 flex-1 min-w-0"
                                                >
                                                    <span className="text-sm flex-shrink-0">
                                                        {project.icon || "👋"}
                                                    </span>
                                                    <span className="flex-1 text-sm truncate">
                                                        {project.name}
                                                    </span>
                                                    {project.tasks_count >
                                                        0 && (
                                                        <span className="text-[10px] text-gray-400">
                                                            {
                                                                project.tasks_count
                                                            }
                                                        </span>
                                                    )}
                                                </a>
                                                {project.pinned && (
                                                    <svg
                                                        className="w-3 h-3 text-gray-400 flex-shrink-0"
                                                        fill="currentColor"
                                                        viewBox="0 0 24 24"
                                                        title="Pinned"
                                                    >
                                                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                                    </svg>
                                                )}
                                                <span
                                                    className="w-2 h-2 rounded-full flex-shrink-0"
                                                    style={{
                                                        backgroundColor:
                                                            project.color ||
                                                            "#14B8A6",
                                                    }}
                                                ></span>
                                                <div
                                                    className="relative"
                                                    onClick={(e) =>
                                                        e.stopPropagation()
                                                    }
                                                >
                                                    <button
                                                        onClick={() =>
                                                            setListMenuOpen(
                                                                listMenuOpen ===
                                                                    project.id
                                                                    ? null
                                                                    : project.id,
                                                            )
                                                        }
                                                        className="opacity-0 group-hover:opacity-100 p-0.5 rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                                                    >
                                                        <svg
                                                            className="w-3.5 h-3.5"
                                                            fill="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <circle
                                                                cx="12"
                                                                cy="5"
                                                                r="1.5"
                                                            />
                                                            <circle
                                                                cx="12"
                                                                cy="12"
                                                                r="1.5"
                                                            />
                                                            <circle
                                                                cx="12"
                                                                cy="19"
                                                                r="1.5"
                                                            />
                                                        </svg>
                                                    </button>
                                                    {listMenuOpen ===
                                                        project.id && (
                                                        <>
                                                            <div
                                                                className="fixed inset-0 z-40"
                                                                onClick={() =>
                                                                    setListMenuOpen(
                                                                        null,
                                                                    )
                                                                }
                                                            ></div>
                                                            <div className="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                                                <button
                                                                    onClick={() => {
                                                                        openEditList(
                                                                            project,
                                                                        );
                                                                        setListMenuOpen(
                                                                            null,
                                                                        );
                                                                    }}
                                                                    className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                                >
                                                                    <svg
                                                                        className="w-3.5 h-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            strokeLinecap="round"
                                                                            strokeLinejoin="round"
                                                                            strokeWidth="2"
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                                        />
                                                                    </svg>
                                                                    Edit
                                                                </button>
                                                                <button
                                                                    onClick={() => {
                                                                        pinList(
                                                                            project,
                                                                        );
                                                                        setListMenuOpen(
                                                                            null,
                                                                        );
                                                                    }}
                                                                    className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                                >
                                                                    <svg
                                                                        className="w-3.5 h-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            strokeLinecap="round"
                                                                            strokeLinejoin="round"
                                                                            strokeWidth="2"
                                                                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                                                        />
                                                                    </svg>
                                                                    {project.pinned
                                                                        ? "Unpin"
                                                                        : "Pin"}
                                                                </button>
                                                                <button
                                                                    onClick={() => {
                                                                        duplicateList(
                                                                            project,
                                                                        );
                                                                        setListMenuOpen(
                                                                            null,
                                                                        );
                                                                    }}
                                                                    className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                                >
                                                                    <svg
                                                                        className="w-3.5 h-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            strokeLinecap="round"
                                                                            strokeLinejoin="round"
                                                                            strokeWidth="2"
                                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                                        />
                                                                    </svg>
                                                                    Duplicate
                                                                </button>
                                                                <div className="border-t border-gray-100 my-0.5"></div>
                                                                <button
                                                                    onClick={() => {
                                                                        deleteList(
                                                                            project,
                                                                        );
                                                                        setListMenuOpen(
                                                                            null,
                                                                        );
                                                                    }}
                                                                    className="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition"
                                                                >
                                                                    <svg
                                                                        className="w-3.5 h-3.5"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        viewBox="0 0 24 24"
                                                                    >
                                                                        <path
                                                                            strokeLinecap="round"
                                                                            strokeLinejoin="round"
                                                                            strokeWidth="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                        />
                                                                    </svg>
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                 </div>
                             )}
                         </div>
                             );
                        })}
                        </div>

                        {/* Ungrouped */}
                        <div ref={ungroupedRef}>
                        {sidebar?.projects
                            ?.filter((p) => !p.folder_id)
                            ?.map((project) => (
                                <div
                                    key={project.id}
                                    data-id={project.id}
                                    className="group relative flex items-center gap-1 px-3 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                                >
                                    <span className="project-drag-handle cursor-grab active:cursor-grabbing p-0.5 text-gray-300 hover:text-gray-500 rounded transition opacity-0 group-hover:opacity-100 flex-shrink-0">
                                        <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" /></svg>
                                    </span>
                                    <a
                                        href={route(
                                            "projects.show",
                                            project.id,
                                        )}
                                        className="flex items-center gap-2 flex-1 min-w-0"
                                    >
                                        <span className="text-sm flex-shrink-0">
                                            {project.icon || "👋"}
                                        </span>
                                        <span className="flex-1 text-sm truncate">
                                            {project.name}
                                        </span>
                                        {project.tasks_count > 0 && (
                                            <span className="text-[10px] text-gray-400">
                                                {project.tasks_count}
                                            </span>
                                        )}
                                    </a>
                                    {project.pinned && (
                                        <svg
                                            className="w-3 h-3 text-gray-400 flex-shrink-0"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                            title="Pinned"
                                        >
                                            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                        </svg>
                                    )}
                                    <span
                                        className="w-2 h-2 rounded-full flex-shrink-0"
                                        style={{
                                            backgroundColor:
                                                project.color || "#14B8A6",
                                        }}
                                    ></span>
                                    <div
                                        className="relative"
                                        onClick={(e) => e.stopPropagation()}
                                    >
                                        <button
                                            onClick={() =>
                                                setListMenuOpen(
                                                    listMenuOpen === project.id
                                                        ? null
                                                        : project.id,
                                                )
                                            }
                                            className="opacity-0 group-hover:opacity-100 p-0.5 rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                                        >
                                            <svg
                                                className="w-3.5 h-3.5"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="5"
                                                    r="1.5"
                                                />
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="1.5"
                                                />
                                                <circle
                                                    cx="12"
                                                    cy="19"
                                                    r="1.5"
                                                />
                                            </svg>
                                        </button>
                                        {listMenuOpen === project.id && (
                                            <>
                                                <div
                                                    className="fixed inset-0 z-40"
                                                    onClick={() =>
                                                        setListMenuOpen(null)
                                                    }
                                                ></div>
                                                <div className="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                                    <button
                                                        onClick={() => {
                                                            openEditList(
                                                                project,
                                                            );
                                                            setListMenuOpen(
                                                                null,
                                                            );
                                                        }}
                                                        className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                    >
                                                        <svg
                                                            className="w-3.5 h-3.5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                            />
                                                        </svg>
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() => {
                                                            pinList(project);
                                                            setListMenuOpen(
                                                                null,
                                                            );
                                                        }}
                                                        className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                    >
                                                        <svg
                                                            className="w-3.5 h-3.5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth="2"
                                                                d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                                                            />
                                                        </svg>
                                                        {project.pinned
                                                            ? "Unpin"
                                                            : "Pin"}
                                                    </button>
                                                    <button
                                                        onClick={() => {
                                                            duplicateList(
                                                                project,
                                                            );
                                                            setListMenuOpen(
                                                                null,
                                                            );
                                                        }}
                                                        className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                    >
                                                        <svg
                                                            className="w-3.5 h-3.5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth="2"
                                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                            />
                                                        </svg>
                                                        Duplicate
                                                    </button>
                                                    <div className="border-t border-gray-100 my-0.5"></div>
                                                    <button
                                                        onClick={() => {
                                                            deleteList(project);
                                                            setListMenuOpen(
                                                                null,
                                                            );
                                                        }}
                                                        className="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition"
                                                    >
                                                        <svg
                                                            className="w-3.5 h-3.5"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                            />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                </div>
                            ))}

                        </div>

                        {(!sidebar?.projects ||
                            sidebar.projects.length === 0) && (
                            <p className="px-3 py-2 text-xs text-gray-400">
                                No lists yet
                            </p>
                        )}
                    </div>

                    {/* Tags */}
                    <div>
                        <div className="flex items-center justify-between px-3 mb-1">
                            <h3 className="text-[10px] font-semibold uppercase tracking-widest text-gray-400">
                                Tags
                            </h3>
                            <div className="flex items-center gap-1">
                                <span className="text-[10px] text-gray-400">
                                    {sidebar?.tags?.length || 0}
                                </span>
                                <button
                                    onClick={openAddTag}
                                    className="w-4 h-4 rounded flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition"
                                    title="Add Tag"
                                >
                                    <svg
                                        className="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        {sidebar?.tags?.map((tag) => (
                            <div
                                key={tag.id}
                                className="group relative flex items-center gap-3 px-3 py-2 rounded-lg transition text-gray-600 hover:text-gray-900 hover:bg-gray-50"
                            >
                                <span className="flex-1 text-sm truncate">
                                    {tag.icon || "🏷️"} {tag.name}
                                </span>
                                <span
                                    className="w-2 h-2 rounded-full flex-shrink-0"
                                    style={{ backgroundColor: tag.color }}
                                ></span>
                                <div
                                    className="relative opacity-0 group-hover:opacity-100 transition"
                                    onClick={(e) => e.stopPropagation()}
                                >
                                    <button
                                        onClick={() =>
                                            setTagMenuOpen(
                                                tagMenuOpen === tag.id
                                                    ? null
                                                    : tag.id,
                                            )
                                        }
                                        className="p-0.5 rounded transition text-gray-400 hover:text-gray-600 hover:bg-gray-100"
                                    >
                                        <svg
                                            className="w-3.5 h-3.5"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle cx="12" cy="5" r="1.5" />
                                            <circle cx="12" cy="12" r="1.5" />
                                            <circle cx="12" cy="19" r="1.5" />
                                        </svg>
                                    </button>
                                    {tagMenuOpen === tag.id && (
                                        <>
                                            <div
                                                className="fixed inset-0 z-40"
                                                onClick={() =>
                                                    setTagMenuOpen(null)
                                                }
                                            ></div>
                                            <div className="absolute top-full right-0 mt-1 w-40 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                                                <button
                                                    onClick={() => {
                                                        openEditTag(tag);
                                                        setTagMenuOpen(null);
                                                    }}
                                                    className="w-full flex items-center gap-2 px-3 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"
                                                >
                                                    <svg
                                                        className="w-3.5 h-3.5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                        />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <div className="border-t border-gray-100 my-0.5"></div>
                                                <button
                                                    onClick={() => {
                                                        deleteTag(tag);
                                                        setTagMenuOpen(null);
                                                    }}
                                                    className="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-500 hover:bg-red-50 transition"
                                                >
                                                    <svg
                                                        className="w-3.5 h-3.5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                        />
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Bottom */}
                <div className="px-2 pb-2 space-y-0.5 flex-shrink-0 border-t border-gray-100 pt-2">
                    <a
                        href={route("completed.index")}
                        className="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-400 text-sm hover:bg-gray-50 hover:text-gray-600 transition"
                    >
                        <svg
                            className="w-4 h-4 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span className="flex-1">Completed</span>
                    </a>
                    <div className="relative">
                        <button
                            onClick={() => setUserMenuOpen(!userMenuOpen)}
                            className="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition cursor-pointer text-left"
                        >
                            <div className="relative w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                {user?.name?.charAt(0).toUpperCase()}
                                {onlineUsers.length > 0 && <span className="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 ring-2 ring-white" />}
                            </div>
                            <span className="flex-1 text-sm font-medium text-gray-700 truncate">
                                {user?.name}
                            </span>
                            <svg
                                className={`w-4 h-4 text-gray-400 transition-transform ${userMenuOpen ? "rotate-180" : ""}`}
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>
                        {userMenuOpen && (
                            <>
                                <div
                                    className="fixed inset-0 z-40"
                                    onClick={() => setUserMenuOpen(false)}
                                ></div>
                                <div className="absolute bottom-full left-0 right-0 mb-1 mx-1 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-50">
                                    <div className="px-3 py-2 border-b border-gray-100">
                                        <div className="text-xs font-medium text-gray-900 truncate">
                                            {user?.name}
                                        </div>
                                        <div className="text-[11px] text-gray-500 truncate">
                                            {user?.email}
                                        </div>
                                    </div>
                                    <a
                                        href={route("dashboard")}
                                        className="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        <svg
                                            className="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="1.5"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4"
                                            />
                                        </svg>
                                        Dashboard
                                    </a>
                                    <form
                                        method="POST"
                                        action={route("logout")}
                                    >
                                        <button
                                            type="submit"
                                            className="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                                        >
                                            <svg
                                                className="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="1.5"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                />
                                            </svg>
                                            Log out
                                        </button>
                                    </form>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* === LIST MODAL === */}
            {listModalOpen && (
                <>
                    <div
                        className="fixed inset-0 bg-black/40 z-[100]"
                        onClick={() => setListModalOpen(false)}
                    ></div>
                    <div className="fixed inset-0 z-[100] flex items-center justify-center">
                        <div
                            className="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <div className="px-6 pt-6 pb-4">
                                <h3 className="text-lg font-semibold text-gray-900 text-center">
                                    {listModalMode === "add"
                                        ? "Add List"
                                        : "Edit List"}
                                </h3>
                            </div>
                            <div className="px-6 pb-6 space-y-5">
                                <div>
                                    <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Name
                                    </label>
                                    <input
                                        type="text"
                                        value={listForm.name}
                                        onChange={(e) =>
                                            setListForm({
                                                ...listForm,
                                                name: e.target.value,
                                            })
                                        }
                                        placeholder="List name..."
                                        autoFocus
                                        className="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Icon
                                    </label>
                                    <div className="mt-1.5 flex items-center gap-3">
                                        <button
                                            type="button"
                                            className="w-14 h-14 rounded-xl border-2 border-gray-200 flex items-center justify-center text-3xl hover:border-brand-500 transition flex-shrink-0"
                                            style={
                                                listForm.icon
                                                    ? {
                                                          borderColor:
                                                              "#14B8A6",
                                                          backgroundColor:
                                                              "#F0FDFA",
                                                      }
                                                    : {}
                                            }
                                        >
                                            <span>{listForm.icon || "👋"}</span>
                                        </button>
                                        <input
                                            type="text"
                                            value={listForm.icon}
                                            onChange={(e) =>
                                                setListForm({
                                                    ...listForm,
                                                    icon: e.target.value,
                                                })
                                            }
                                            className="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300"
                                            placeholder="Type or paste an emoji..."
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Color
                                    </label>
                                    <div className="flex gap-2 mt-1.5">
                                        {COLORS.map((c) => (
                                            <button
                                                key={c}
                                                type="button"
                                                onClick={() =>
                                                    setListForm({
                                                        ...listForm,
                                                        color: c,
                                                    })
                                                }
                                                className="w-7 h-7 rounded-full border-2 transition"
                                                style={{
                                                    backgroundColor: c,
                                                    borderColor:
                                                        listForm.color === c
                                                            ? "#111827"
                                                            : "transparent",
                                                    transform:
                                                        listForm.color === c
                                                            ? "scale(1.1)"
                                                            : "scale(1)",
                                                }}
                                            />
                                        ))}
                                    </div>
                                </div>
                                {listModalMode === "add" &&
                                    sidebar?.folders?.length > 0 && (
                                        <div>
                                            <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                Folder
                                            </label>
                                            <select
                                                value={listForm.folder_id || ""}
                                                onChange={(e) =>
                                                    setListForm({
                                                        ...listForm,
                                                        folder_id:
                                                            e.target.value ||
                                                            null,
                                                    })
                                                }
                                                className="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 text-gray-700"
                                            >
                                                <option value="">None</option>
                                                {sidebar.folders.map((f) => (
                                                    <option
                                                        key={f.id}
                                                        value={f.id}
                                                    >
                                                        {f.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    )}
                                <div className="flex gap-3 pt-2">
                                    <button
                                        onClick={() => setListModalOpen(false)}
                                        className="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={saveList}
                                        className="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition"
                                    >
                                        {listModalMode === "add"
                                            ? "Create List"
                                            : "Save Changes"}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}

            {/* === TAG MODAL === */}
            {tagModalOpen && (
                <>
                    <div
                        className="fixed inset-0 bg-black/40 z-[100]"
                        onClick={() => setTagModalOpen(false)}
                    ></div>
                    <div className="fixed inset-0 z-[100] flex items-center justify-center">
                        <div
                            className="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <div className="px-6 pt-6 pb-4">
                                <h3 className="text-lg font-semibold text-gray-900 text-center">
                                    {tagModalMode === "add"
                                        ? "Add Tag"
                                        : "Edit Tag"}
                                </h3>
                            </div>
                            <div className="px-6 pb-6 space-y-5">
                                <div>
                                    <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Name
                                    </label>
                                    <input
                                        type="text"
                                        value={tagForm.name}
                                        onChange={(e) =>
                                            setTagForm({
                                                ...tagForm,
                                                name: e.target.value,
                                            })
                                        }
                                        placeholder="Tag name..."
                                        autoFocus
                                        className="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Icon
                                    </label>
                                    <input
                                        type="text"
                                        value={tagForm.icon}
                                        onChange={(e) =>
                                            setTagForm({
                                                ...tagForm,
                                                icon: e.target.value,
                                            })
                                        }
                                        placeholder="Emoji icon..."
                                        className="w-full mt-1.5 text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-300"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs font-medium text-gray-500 uppercase tracking-wide">
                                        Color
                                    </label>
                                    <div className="flex gap-2 mt-1.5">
                                        {COLORS.map((c) => (
                                            <button
                                                key={c}
                                                type="button"
                                                onClick={() =>
                                                    setTagForm({
                                                        ...tagForm,
                                                        color: c,
                                                    })
                                                }
                                                className="w-7 h-7 rounded-full border-2 transition"
                                                style={{
                                                    backgroundColor: c,
                                                    borderColor:
                                                        tagForm.color === c
                                                            ? "#111827"
                                                            : "transparent",
                                                    transform:
                                                        tagForm.color === c
                                                            ? "scale(1.1)"
                                                            : "scale(1)",
                                                }}
                                            />
                                        ))}
                                    </div>
                                </div>
                                <div className="flex gap-3 pt-2">
                                    <button
                                        onClick={() => setTagModalOpen(false)}
                                        className="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={saveTag}
                                        className="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition"
                                    >
                                        {tagModalMode === "add"
                                            ? "Create Tag"
                                            : "Save Changes"}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}

            {/* === RENAME FOLDER MODAL === */}
            {renameModalOpen && (
                <>
                    <div
                        className="fixed inset-0 bg-black/40 z-[100]"
                        onClick={() => setRenameModalOpen(false)}
                    ></div>
                    <div className="fixed inset-0 z-[100] flex items-center justify-center">
                        <div
                            className="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <div className="px-6 pt-6 pb-4">
                                <h3 className="text-lg font-semibold text-gray-900 text-center">
                                    Rename Folder
                                </h3>
                            </div>
                            <div className="px-6 pb-6 space-y-5">
                                <input
                                    type="text"
                                    value={renameFolderName}
                                    onChange={(e) =>
                                        setRenameFolderName(e.target.value)
                                    }
                                    onKeyDown={(e) =>
                                        e.key === "Enter" && renameFolder()
                                    }
                                    autoFocus
                                    className="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                />
                                <div className="flex gap-3">
                                    <button
                                        onClick={() =>
                                            setRenameModalOpen(false)
                                        }
                                        className="flex-1 text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        onClick={renameFolder}
                                        className="flex-1 text-sm font-medium py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition"
                                    >
                                        Rename
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </aside>

        {mobileMenuOpen && (
            <div
                className="fixed inset-0 bg-black/40 z-40 lg:hidden"
                onClick={() => setMobileMenuOpen(false)}
            />
        )}
        </>
    );
}

export default function AuthenticatedLayout({ header, children }) {
    const { props } = usePage();
    const sidebar = props.layoutSidebar || {};

    return (
        <div className="bg-gray-50 h-screen flex flex-col overflow-hidden">
            <Toast />
            <VerificationBanner />
            <div className="flex flex-1 overflow-hidden">
                <Sidebar sidebar={sidebar} />
                <div className="flex flex-col flex-1 overflow-hidden min-w-0">
                    <main className="flex-1 overflow-y-auto p-6">{children}</main>
                </div>
            </div>
        </div>
    );
}
