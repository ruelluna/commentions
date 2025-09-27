document.addEventListener('alpine:init', () => {
    Alpine.data('fileUpload', () => ({
        files: [],

        init() {
            // Listen for file input changes and sync with Livewire
            this.$watch('files', (files) => {
                // Update Livewire component with the files
                this.$wire.set('attachments', files);
            });

            // Listen for clear events from Livewire
            this.$wire.on('files:cleared', () => {
                this.clearFiles();
            });
        },

        handleFiles(fileList) {
            const files = Array.from(fileList);
            this.files = [...this.files, ...files];
        },

        removeFile(index) {
            this.files.splice(index, 1);
            // Update Livewire when files are removed
            this.$wire.set('attachments', this.files);
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        clearFiles() {
            this.files = [];
            this.$wire.set('attachments', []);
        }
    }));
});
