// Simple image upload functionality for TipTap editor
export function setupImageUpload(editor) {
    // Add image upload button to toolbar
    const toolbar = document.querySelector('.ProseMirror-toolbar') || document.querySelector('[data-toolbar]')
    
    if (toolbar) {
        const imageButton = document.createElement('button')
        imageButton.type = 'button'
        imageButton.innerHTML = '📷'
        imageButton.title = 'Upload Image'
        imageButton.style.cssText = `
            background: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px 12px;
            margin: 0 2px;
            cursor: pointer;
            font-size: 16px;
        `
        
        imageButton.addEventListener('click', () => {
            const input = document.createElement('input')
            input.type = 'file'
            input.accept = 'image/*'
            input.style.display = 'none'
            
            input.addEventListener('change', async (event) => {
                const file = event.target.files[0]
                if (!file) return
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB')
                    return
                }
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file')
                    return
                }
                
                try {
                    // Upload file
                    const formData = new FormData()
                    formData.append('file', file)
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))
                    
                    const response = await fetch('/livewire/upload-file', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        },
                    })
                    
                    if (!response.ok) {
                        throw new Error('Upload failed')
                    }
                    
                    const result = await response.json()
                    
                    // Insert image into editor
                    editor.chain().focus().setImage({ src: result.url, alt: file.name }).run()
                    
                } catch (error) {
                    console.error('Upload failed:', error)
                    alert('Upload failed: ' + error.message)
                }
            })
            
            document.body.appendChild(input)
            input.click()
            document.body.removeChild(input)
        })
        
        toolbar.appendChild(imageButton)
    }
    
    // Add drag and drop support
    const editorElement = editor.view.dom
    
    editorElement.addEventListener('dragover', (event) => {
        event.preventDefault()
        event.dataTransfer.dropEffect = 'copy'
    })
    
    editorElement.addEventListener('drop', async (event) => {
        event.preventDefault()
        const files = event.dataTransfer.files
        
        if (files.length > 0) {
            const file = files[0]
            
            // Validate file
            if (!file.type.startsWith('image/')) {
                alert('Please drop an image file')
                return
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB')
                return
            }
            
            try {
                // Upload file
                const formData = new FormData()
                formData.append('file', file)
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'))
                
                const response = await fetch('/livewire/upload-file', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    },
                })
                
                if (!response.ok) {
                    throw new Error('Upload failed')
                }
                
                const result = await response.json()
                
                // Insert image into editor
                editor.chain().focus().setImage({ src: result.url, alt: file.name }).run()
                
            } catch (error) {
                console.error('Upload failed:', error)
                alert('Upload failed: ' + error.message)
            }
        }
    })
}
