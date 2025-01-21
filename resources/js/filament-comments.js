import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'

document.addEventListener('alpine:init', () => {
    Alpine.data('editor', (content) => {
        let editor

        return {
            updatedAt: Date.now(),

            init() {
                const _this = this

                editor = new Editor({
                    element: this.$refs.element,
                    extensions: [
                        StarterKit,
                        Placeholder.configure({
                            placeholder: 'Type your comment…',
                        }),
                    ],
                    editorProps: {
                        attributes: {
                            class: `prose prose-sm sm:prose-base lg:prose-lg xl:prose-2xl m-5 focus:outline-none min-w-full w-full rounded-lg border border-gray-300 p-4`,
                        },
                    },
                    placeholder: 'Type something...',
                    content: content,

                    onCreate({ editor }) {
                        _this.updatedAt = Date.now()
                    },

                    onUpdate({ editor }) {
                        Livewire.dispatch('editorContentUpdated', {
                            value: editor.getHTML()
                        });

                        _this.updatedAt = Date.now()
                    },
                    onSelectionUpdate({ editor }) {
                        _this.updatedAt = Date.now()
                    },
                })
            },
            isLoaded() {
                return editor
            },
            isActive(type, opts = {}) {
                return editor.isActive(type, opts)
            },
        }
    })
})
