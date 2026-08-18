import { useState, useRef, useCallback, useEffect } from 'react';

const BLOCK_TYPES = {
    h1: 'text-2xl font-bold text-gray-900',
    h2: 'text-xl font-bold text-gray-900',
    h3: 'text-lg font-semibold text-gray-900',
    bullet: 'text-sm text-gray-800 pl-4 list-disc',
    numbered: 'text-sm text-gray-800 pl-4 list-decimal',
    quote: 'text-sm text-gray-600 italic border-l-2 border-gray-300 pl-3',
    code: 'text-sm font-mono text-gray-800 bg-gray-100 rounded px-3 py-2',
    paragraph: 'text-sm text-gray-800',
};

function parseLine(text) {
    if (text.startsWith('### ')) return { type: 'h3', content: text.slice(4) };
    if (text.startsWith('## ')) return { type: 'h2', content: text.slice(3) };
    if (text.startsWith('# ')) return { type: 'h1', content: text.slice(2) };
    if (text.startsWith('> ')) return { type: 'quote', content: text.slice(2) };
    if (text.startsWith('- ') || text.startsWith('* ')) return { type: 'bullet', content: text.slice(2) };
    if (/^\d+\.\s/.test(text)) return { type: 'numbered', content: text.replace(/^\d+\.\s/, '') };
    if (text.startsWith('````')) return { type: 'code', content: text.slice(4) };
    return { type: 'paragraph', content: text };
}

function BlockRenderer({ block, onChange, onKeyDown, onFocus }) {
    const ref = useRef(null);
    const { type, content } = parseLine(block.text);

    useEffect(() => {
        if (ref.current && ref.current.textContent !== content) {
            ref.current.textContent = content;
        }
    }, [content]);

    const handleInput = (e) => {
        const raw = e.target.textContent;
        let prefix = '';
        if (type === 'h1') prefix = '# ';
        else if (type === 'h2') prefix = '## ';
        else if (type === 'h3') prefix = '### ';
        else if (type === 'quote') prefix = '> ';
        else if (type === 'bullet') prefix = '- ';
        else if (type === 'numbered') prefix = '1. ';
        else if (type === 'code') prefix = '````';

        const newText = prefix + raw;
        onChange(block.id, newText);
    };

    return (
        <div
            ref={ref}
            contentEditable
            suppressContentEditableWarning
            className={`outline-none empty:before:content-[attr(data-placeholder)] empty:text-gray-300 ${BLOCK_TYPES[type]} leading-relaxed`}
            data-placeholder={block.placeholder || 'Type something...'}
            onInput={handleInput}
            onKeyDown={(e) => onKeyDown(e, block.id, type)}
            onFocus={onFocus}
            dangerouslySetInnerHTML={{ __html: content }}
        />
    );
}

export default function MarkdownEditor({ value, onChange, placeholder = 'Write a description...' }) {
    const [blocks, setBlocks] = useState(() => {
        if (!value) return [{ id: 1, text: '', placeholder }];
        const lines = value.split('\n');
        return lines.map((line, i) => ({
            id: i + 1,
            text: line,
            placeholder: i === 0 ? placeholder : '',
        }));
    });

    const containerRef = useRef(null);
    const idCounter = useRef(blocks.length);

    const handleChange = useCallback((id, text) => {
        setBlocks(prev => prev.map(b => b.id === id ? { ...b, text } : b));
    }, []);

    useEffect(() => {
        const text = blocks.map(b => b.text).join('\n');
        if (text !== (value || '')) {
            onChange(text);
        }
    }, [blocks]);

    const handleKeyDown = useCallback((e, blockId, currentType) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            setBlocks(prev => {
                const idx = prev.findIndex(b => b.id === blockId);
                const newId = ++idCounter.current;
                const newBlock = { id: newId, text: '', placeholder: '' };
                const next = [...prev];
                next.splice(idx + 1, 0, newBlock);

                setTimeout(() => {
                    const el = containerRef.current?.querySelector(`[data-block-id="${newId}"]`);
                    if (el) el.focus();
                }, 0);

                return next;
            });
        }

        if (e.key === 'Backspace') {
            const block = blocks.find(b => b.id === blockId);
            if (block && block.text === '' && blocks.length > 1) {
                e.preventDefault();
                setBlocks(prev => {
                    const idx = prev.findIndex(b => b.id === blockId);
                    const filtered = prev.filter(b => b.id !== blockId);
                    const focusIdx = Math.max(0, idx - 1);
                    const focusId = filtered[focusIdx]?.id;

                    setTimeout(() => {
                        if (focusId) {
                            const el = containerRef.current?.querySelector(`[data-block-id="${focusId}"]`);
                            if (el) {
                                el.focus();
                                const range = document.createRange();
                                const sel = window.getSelection();
                                range.selectNodeContents(el);
                                range.collapse(false);
                                sel.removeAllRanges();
                                sel.addRange(range);
                            }
                        }
                    }, 0);

                    return filtered;
                });
            }
        }
    }, [blocks]);

    return (
        <div ref={containerRef} className="space-y-1">
            {blocks.map((block, i) => (
                <div key={block.id} data-block-id={block.id}>
                    <BlockRenderer
                        block={{ ...block, placeholder: i === 0 ? placeholder : '' }}
                        onChange={handleChange}
                        onKeyDown={handleKeyDown}
                        onFocus={() => {}}
                    />
                </div>
            ))}
        </div>
    );
}
