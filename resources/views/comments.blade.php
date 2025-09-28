@use('\Kirschbaum\Commentions\Config')

<div class="comm:flex comm:gap-4 comm:h-full" x-data="{ wasFocused: false }">
    {{-- Main Comments Area --}}
    <div class="comm:flex-1 comm:space-y-2">
        @if (Config::resolveAuthenticatedUser()?->can('create', Config::getCommentModel()))
            <form wire:submit.prevent="save" x-cloak>
                {{-- tiptap editor --}}
                <div class="comm:relative tip-tap-container comm:mb-2" x-on:click="wasFocused = true" wire:ignore>
                    <div x-data="editor(@js($commentBody), @js($this->mentions), 'comments')">
                        <div x-ref="element"></div>
                    </div>
                </div>

                {{-- File Upload Area --}}
                <template x-if="wasFocused">
                    <div class="comm:mt-2">
                        <div x-data="fileUpload()">
                            <div class="comm:border-2 comm:border-dashed comm:border-gray-300 comm:dark:border-gray-600 comm:rounded-lg comm:p-4 comm:mb-2"
                                x-on:click="$refs.fileInput.click()"
                                x-on:dragover.prevent="$el.classList.add('comm:border-blue-400')"
                                x-on:dragleave.prevent="$el.classList.remove('comm:border-blue-400')"
                                x-on:drop.prevent="handleFiles($event.dataTransfer.files); $el.classList.remove('comm:border-blue-400')">

                                <input type="file" x-ref="fileInput" x-on:change="handleFiles($event.target.files)"
                                    multiple class="comm:hidden"
                                    accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt,.zip">

                                <div class="comm:text-center comm:py-4">
                                    <svg class="comm:mx-auto comm:h-10 comm:w-10 comm:text-gray-400"
                                        stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p class="comm:mt-2 comm:text-sm comm:text-gray-600 comm:dark:text-gray-400">
                                        <span class="comm:font-medium comm:text-blue-600 comm:dark:text-blue-400">Click
                                            to
                                            upload</span>
                                        or drag and drop
                                    </p>
                                    <p class="comm:text-xs comm:text-gray-500 comm:dark:text-gray-500">PNG, JPG, PDF,
                                        DOC,
                                        TXT, ZIP up to 10MB</p>
                                </div>
                            </div>

                            {{-- File Preview --}}
                            <template x-if="files && files.length > 0">
                                <div class="comm:space-y-2 comm:mb-2">
                                    <template x-for="(file, index) in files" :key="index">
                                        <div
                                            class="comm:flex comm:items-center comm:justify-between comm:bg-gray-50 comm:dark:bg-gray-800 comm:rounded comm:px-3 comm:py-2">
                                            <div class="comm:flex comm:items-center comm:space-x-2">
                                                <svg class="comm:h-5 comm:w-5 comm:text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                <span class="comm:text-sm comm:text-gray-700 comm:dark:text-gray-300"
                                                    x-text="file.name"></span>
                                                <span class="comm:text-xs comm:text-gray-500"
                                                    x-text="formatFileSize(file.size)"></span>
                                            </div>
                                            <button type="button" x-on:click="removeFile(index)"
                                                class="comm:text-red-500 comm:hover:text-red-700 comm:dark:text-red-400 comm:dark:hover:text-red-300">
                                                <svg class="comm:h-4 comm:w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="wasFocused">
                    <div>
                        <x-filament::button wire:click="save"
                            size="sm">{{ __('commentions::comments.comment') }}</x-filament::button>

                        <x-filament::button x-on:click="wasFocused = false" wire:click="clear" size="sm"
                            color="gray">{{ __('commentions::comments.cancel') }}</x-filament::button>
                    </div>
                </template>
            </form>
        @endif

        <livewire:commentions::comment-list :record="$record" :mentionables="$this->mentions" :polling-interval="$pollingInterval" :paginate="$paginate ?? true"
            :per-page="$perPage ?? 5" :load-more-label="$loadMoreLabel ?? 'Show more'" :per-page-increment="$perPageIncrement ?? null" />
    </div>

    {{-- Subscription Sidebar --}}
    @if ($this->canSubscribe && $this->resolvedSidebarEnabled)
        <livewire:commentions::subscription-sidebar :record="$record" :show-subscribers="$this->resolvedShowSubscribers" />
    @endif
</div>

<script>
    // Register fileUpload component when Alpine is ready
    document.addEventListener('alpine:init', () => {
        // Only register if not already registered
        if (!Alpine.data('fileUpload')) {
            Alpine.data('fileUpload', () => ({
                files: [],
                init() {
                    this.$watch('files', (files) => {
                        Livewire.dispatch('files:updated', {
                            files: files
                        });
                    });

                    this.$wire.on('files:cleared', () => {
                        this.clearFiles();
                    });
                },
                async handleFiles(fileList) {
                    const files = Array.from(fileList);

                    // Convert files to base64 and add to files array
                    for (const file of files) {
                        const base64Content = await this.fileToBase64(file);
                        this.files.push({
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            content: base64Content
                        });
                    }
                },

                fileToBase64(file) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => {
                            // Remove the data URL prefix (data:image/jpeg;base64,)
                            const base64 = reader.result.split(',')[1];
                            resolve(base64);
                        };
                        reader.onerror = error => reject(error);
                    });
                },
                removeFile(index) {
                    this.files.splice(index, 1);
                    Livewire.dispatch('files:updated', {
                        files: this.files
                    });
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
                    Livewire.dispatch('files:updated', {
                        files: []
                    });
                }
            }));
        }
    });
</script>
