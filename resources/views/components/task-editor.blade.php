@props(['content' => '', 'placeholder' => 'Start writing...', 'taskId' => null])

<div x-data="{
    html: {{ json_encode($content) }},
    editor: null,
    saveTimer: null,
    save() {
        clearTimeout(this.saveTimer)
        this.saveTimer = setTimeout(() => {
            fetch('{{ route('tasks.description', $taskId) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ description: this.html }),
            })
        }, 500)
    }
}" x-init="$nextTick(() => {
    editor = initTaskEditor($refs.editor, {
        content: html,
        placeholder: {{ json_encode($placeholder) }},
        onUpdate: (val) => { html = val; this.save() },
    });
})" class="task-editor-wrapper">
    <div x-ref="editor" class="task-editor-area min-h-[200px] text-sm leading-relaxed text-gray-800 outline-none"></div>
</div>
