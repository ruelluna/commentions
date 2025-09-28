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

        // Insert a simple text placeholder
        const placeholder = this.editor.schema.nodes.paragraph.create({}, 
            this.editor.schema.text('Uploading image...')
        )

        const pos = tr.selection.from
        tr.insert(pos, placeholder)
        dispatch(tr)

        // Upload the file
        this.uploadFile(file).then(url => {
            const { state, dispatch } = view
            const { tr } = state

            // Find and replace the placeholder
            state.doc.descendants((node, pos) => {
                if (node.type.name === 'paragraph' && node.textContent.includes('Uploading image...')) {
                    const imageNode = this.editor.schema.nodes.image.create({
                        src: url,
                        alt: file.name,
                    })
                    tr.replaceWith(pos, pos + node.nodeSize, imageNode)
                }
            })

            dispatch(tr)
        }).catch(error => {
            console.error('Upload failed:', error)
            // Remove the placeholder on error
            const { state, dispatch } = view
            const { tr } = state

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'paragraph' && node.textContent.includes('Uploading image...')) {
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
