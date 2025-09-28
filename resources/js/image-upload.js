import { Node, mergeAttributes } from '@tiptap/core'
import { Plugin, PluginKey } from '@tiptap/pm/state'
import { Decoration, DecorationSet } from '@tiptap/pm/view'

export const ImageUpload = Node.create({
    name: 'imageUpload',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            alt: {
                default: null,
            },
            title: {
                default: null,
            },
            width: {
                default: null,
            },
            height: {
                default: null,
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'img[src]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['img', mergeAttributes(HTMLAttributes)]
    },

    addCommands() {
        return {
            setImageUpload: (options) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: options,
                })
            },
        }
    },

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('imageUpload'),
                props: {
                    handleDrop: (view, event, slice, moved) => {
                        if (!moved && event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) {
                            const files = Array.from(event.dataTransfer.files)
                            const images = files.filter(file => file.type.startsWith('image/'))

                            if (images.length > 0) {
                                event.preventDefault()

                                images.forEach(file => {
                                    this.uploadImage(file, view)
                                })

                                return true
                            }
                        }
                        return false
                    },
                    handlePaste: (view, event, slice) => {
                        const items = Array.from(event.clipboardData?.items || [])
                        const images = items.filter(item => item.type.startsWith('image/'))

                        if (images.length > 0) {
                            event.preventDefault()

                            images.forEach(item => {
                                const file = item.getAsFile()
                                if (file) {
                                    this.uploadImage(file, view)
                                }
                            })

                            return true
                        }
                        return false
                    },
                },
            }),
        ]
    },

    uploadImage(file, view) {
        const { state, dispatch } = view
        const { tr } = state

        // Insert a placeholder image
        const placeholder = this.editor.schema.nodes.imageUpload.create({
            src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAiIHZpZXdCb3g9IjAgMCAyMDAgMTAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxsaW5lYXJHcmFkaWVudCBpZD0iZyIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+PHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2Y4ZmFmYyIvPjxzdG9wIG9mZnNldD0iMTAwJSIgc3RvcC1jb2xvcj0iI2YxZjVmOSIvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZykiIHN0cm9rZT0iI2U1ZTdlYiIgc3Ryb2tlLXdpZHRoPSIyIiByeD0iOCIvPjxjaXJjbGUgY3g9IjUwJSIgY3k9IjQwJSIgcj0iMTUiIGZpbGw9IiMzNzQxNTEiIG9wYWNpdHk9IjAuMSIvPjxwYXRoIGQ9Im00MCA0MCBsMTAgMTAgbTAtMTAgbC0xMCAxMCIgc3Ryb2tlPSIjMzc0MTUxIiBzdHJva2Utd2lkdGg9IjMiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjxjaXJjbGUgY3g9IjUwJSIgY3k9IjQwJSIgcj0iNSIgZmlsbD0iIzM3NDE1MSIgb3BhY2l0eT0iMC4zIi8+PHRleHQgeD0iNTAlIiB5PSI3MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzY2NjY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC13ZWlnaHQ9IjUwMCI+VXBsb2FkaW5nLi4uPC90ZXh0Pjwvc3ZnPg==',
            alt: 'Uploading...',
            style: 'display: block; margin: 0 auto; width: 100%; max-width: 100%; height: auto; object-fit: contain; border-radius: 8px;',
        })

        const pos = tr.selection.from
        tr.insert(pos, placeholder)
        dispatch(tr)

        // Upload the file
        this.uploadFile(file).then(url => {
            const { state, dispatch } = view
            const { tr } = state

            // Find and replace the placeholder
            state.doc.descendants((node, pos) => {
                if (node.type.name === 'imageUpload' && node.attrs.src.includes('data:image/svg+xml')) {
                    tr.setNodeMarkup(pos, null, {
                        ...node.attrs,
                        src: url,
                        alt: file.name,
                    })
                }
            })

            dispatch(tr)
        }).catch(error => {
            console.error('Upload failed:', error)
            // Remove the placeholder on error
            const { state, dispatch } = view
            const { tr } = state

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'imageUpload' && node.attrs.src.includes('data:image/svg+xml')) {
                    tr.delete(pos, pos + node.nodeSize)
                }
            })

            dispatch(tr)
        })
    },

    async uploadFile(file) {
        const formData = new FormData()
        formData.append('image', file)
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'))

        const response = await fetch('/commentions/upload-image', {
            method: 'POST',
            body: formData,
        })

        if (!response.ok) {
            throw new Error('Upload failed')
        }

        const data = await response.json()
        return data.url
    },
})
