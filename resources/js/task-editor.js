import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import Placeholder from '@tiptap/extension-placeholder'

window.initTaskEditor = function (el, { content = '', placeholder = 'Start writing...', onUpdate = () => {} } = {}) {
    const editor = new Editor({
        element: el,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
            TaskList,
            TaskItem.configure({ nested: true }),
            Placeholder.configure({ placeholder }),
        ],
        content,
        editorProps: {
            attributes: { class: 'tiptap task-editor-content' },
        },
        onUpdate: ({ editor }) => {
            onUpdate(editor.getHTML())
        },
    })

    return editor
}
