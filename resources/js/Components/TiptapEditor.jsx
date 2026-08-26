import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { useEffect } from 'react';

const MenuBar = ({ editor }) => {
    if (!editor) return null;

    const btnClass = (active) =>
        `p-1.5 rounded-lg text-xs transition ${active ? 'bg-brand-100 text-brand-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100'}`;

    return (
        <div className="flex items-center gap-0.5 pb-2 border-b border-gray-100 mb-2 flex-wrap">
            <button type="button" onClick={() => editor.chain().focus().toggleBold().run()} className={btnClass(editor.isActive('bold'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3"><path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleItalic().run()} className={btnClass(editor.isActive('italic'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M19 4h-9M14 20H5M15 4L9 20"/></svg>
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleStrike().run()} className={btnClass(editor.isActive('strike'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M16 4H9a3 3 0 00-2.83 4M4 12h16M15 12a4 4 0 010 8H6"/></svg>
            </button>

            <div className="w-px h-4 bg-gray-200 mx-1" />

            <button type="button" onClick={() => editor.chain().focus().toggleHeading({ level: 1 }).run()} className={btnClass(editor.isActive('heading', { level: 1 }))}>
                H1
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} className={btnClass(editor.isActive('heading', { level: 2 }))}>
                H2
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()} className={btnClass(editor.isActive('heading', { level: 3 }))}>
                H3
            </button>

            <div className="w-px h-4 bg-gray-200 mx-1" />

            <button type="button" onClick={() => editor.chain().focus().toggleBulletList().run()} className={btnClass(editor.isActive('bulletList'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleOrderedList().run()} className={btnClass(editor.isActive('orderedList'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M10 6h11M10 12h11M10 18h11M3 5v2M3 10v1M3 15v.5M4 18H2v1h3v-1h-1z"/></svg>
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleBlockquote().run()} className={btnClass(editor.isActive('blockquote'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C9.591 11.69 11 13.166 11 15c0 1.933-1.567 3.5-3.5 3.5-1.193 0-2.31-.561-2.917-1.179zM14.583 17.321C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C19.591 11.69 21 13.166 21 15c0 1.933-1.567 3.5-3.5 3.5-1.193 0-2.31-.561-2.917-1.179z"/></svg>
            </button>
            <button type="button" onClick={() => editor.chain().focus().toggleCodeBlock().run()} className={btnClass(editor.isActive('codeBlock'))}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>
            </button>

            <div className="w-px h-4 bg-gray-200 mx-1" />

            <button type="button" onClick={() => editor.chain().focus().setHorizontalRule().run()} className={btnClass(false)}>
                <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14"/></svg>
            </button>
        </div>
    );
};

export default function TiptapEditor({ content, onUpdate, placeholder = 'Type something...' }) {
    const editor = useEditor({
        extensions: [StarterKit],
        content: content || '',
        editorProps: {
            attributes: {
                class: 'prose prose-sm max-w-none focus:outline-none min-h-[80px] text-gray-800',
                'data-placeholder': placeholder,
            },
        },
        onUpdate: ({ editor }) => {
            onUpdate(editor.getHTML());
        },
    });

    useEffect(() => {
        if (editor && content !== editor.getHTML() && !editor.isFocused) {
            editor.commands.setContent(content || '', false);
        }
    }, [content]);

    if (!editor) return null;

    return (
        <div className="tiptap-editor">
            <style>{`
                .tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
                    color: #ccc;
                    content: attr(data-placeholder);
                    float: left;
                    height: 0;
                    pointer-events: none;
                }
                .tiptap-editor .ProseMirror h1 { font-size: 1.5rem; font-weight: 700; margin: 0.5rem 0; }
                .tiptap-editor .ProseMirror h2 { font-size: 1.25rem; font-weight: 700; margin: 0.5rem 0; }
                .tiptap-editor .ProseMirror h3 { font-size: 1.1rem; font-weight: 600; margin: 0.5rem 0; }
                .tiptap-editor .ProseMirror ul { list-style-type: disc; padding-left: 1.5rem; margin: 0.25rem 0; }
                .tiptap-editor .ProseMirror ol { list-style-type: decimal; padding-left: 1.5rem; margin: 0.25rem 0; }
                .tiptap-editor .ProseMirror li { margin: 0.125rem 0; }
                .tiptap-editor .ProseMirror blockquote {
                    border-left: 3px solid #d1d5db;
                    padding-left: 0.75rem;
                    color: #6b7280;
                    font-style: italic;
                    margin: 0.5rem 0;
                }
                .tiptap-editor .ProseMirror pre {
                    background: #f3f4f6;
                    border-radius: 0.5rem;
                    padding: 0.75rem;
                    font-family: monospace;
                    font-size: 0.8rem;
                    margin: 0.5rem 0;
                }
                .tiptap-editor .ProseMirror hr {
                    border: none;
                    border-top: 1px solid #e5e7eb;
                    margin: 0.75rem 0;
                }
                .tiptap-editor .ProseMirror p { margin: 0.25rem 0; line-height: 1.6; }
            `}</style>
            <EditorContent editor={editor} />
        </div>
    );
}
