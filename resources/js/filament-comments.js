import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Mention from '@tiptap/extension-mention'
import Placeholder from '@tiptap/extension-placeholder'
import suggestion from './suggestion'

document.addEventListener('alpine:init', () => {
    Alpine.data('editor', (content, mentions = []) => {
        let editor

        return {
            updatedAt: Date.now(),

            init() {
                const _this = this

                editor = new Editor({
                    element: this.$refs.element,
                    extensions: [
                        StarterKit,
                        Mention.configure({
                            HTMLAttributes: {
                                class: 'mention',
                            },
                            suggestion: suggestion(mentions),
                        }),
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
                });

                // Watch for changes in the content property from Livewire
                Livewire.on('editorContentCleared', (newValue) => {
                    editor.commands.setContent('');
                });
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
