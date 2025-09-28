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
            src: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImciIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0b3AtY29sb3I9IiNmOGZhZmMiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNmMWY1ZjkiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2cpIiBzdHJva2U9IiNlNWU3ZWIiIHN0cm9rZS13aWR0aD0iMiIgcng9IjEyIi8+PGNpcmNsZSBjeD0iNTAlIiBjeT0iNDAlIiByPSIyNCIgZmlsbD0iIzM3NDE1MSIgb3BhY2l0eT0iMC4xIi8+PHBhdGggZD0ibTQwIDQwIGwxMiAxMiBtMC0xMiBsLTEyIDEyIiBzdHJva2U9IiMzNzQxNTEiIHN0cm9rZS13aWR0aD0iNCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+PGNpcmNsZSBjeD0iNTAlIiBjeT0iNDAlIiByPSI4IiBmaWxsPSIjMzc0MTUxIiBvcGFjaXR5PSIwLjMiLz48dGV4dCB4PSI1MCUiIHk9Ijc1JSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE2IiBmaWxsPSIjNjY2NjY2IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LXdlaWdodD0iNTAwIj5VcGxvYWRpbmcuLi48L3RleHQ+PC9zdmc+',
            alt: 'Uploading...',
            style: 'display: block; margin: 20px auto; max-width: 400px; height: 200px; object-fit: contain; border-radius: 12px;',
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
