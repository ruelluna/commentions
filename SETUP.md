# File Upload Feature Setup Guide

## 🚀 Quick Start

### 1. **Install in Laravel Application**

```bash
# In your Laravel project
composer require kirschbaum/commentions
php artisan vendor:publish --tag=commentions-migrations
php artisan vendor:publish --tag=commentions-config
php artisan migrate
```

### 1.1. **For Development (Path Repository)**

If you're developing the package locally, add this to your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../commentions"
        }
    ],
    "require": {
        "kirschbaum/commentions": "*"
    }
}
```

Then run:
```bash
composer update
php artisan vendor:publish --tag=commentions-migrations
php artisan vendor:publish --tag=commentions-config
php artisan migrate
```

### 2. **Environment Configuration**

Add to your `.env`:

```env
# File Upload Settings
COMMENTIONS_UPLOADS_ENABLED=true
COMMENTIONS_UPLOADS_MAX_SIZE=10240
COMMENTIONS_UPLOADS_MAX_FILES=5
COMMENTIONS_UPLOADS_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,txt,zip
COMMENTIONS_UPLOADS_DISK=local
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### 3. **Storage Setup**

Ensure your `config/filesystems.php` has:

```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
],
```

### 4. **Usage in Your Model**

```php
use Kirschbaum\Commentions\HasComments;

class Post extends Model
{
    use HasComments;
}
```

### 5. **Include in Blade Template**

```blade
<livewire:commentions::comments :record="$post" />
```

## 🧪 Testing

### Run Tests
```bash
php artisan test --filter="FileUpload"
```

### Manual Testing
1. Create a test route:
```php
Route::get('/test-comments', function () {
    $post = \App\Models\Post::first();
    return view('test-comments', compact('post'));
});
```

2. Create test view (`resources/views/test-comments.blade.php`):
```blade
<!DOCTYPE html>
<html>
<head>
    <title>Test Comments</title>
    @livewireStyles
</head>
<body>
    <div class="container mx-auto p-4">
        <h1>Test Comments with File Upload</h1>
        <livewire:commentions::comments :record="$post" />
    </div>
    @livewireScripts
</body>
</html>
```

## 🔧 Features

- ✅ Drag & drop file upload
- ✅ Multiple file support
- ✅ File type validation
- ✅ Size limits
- ✅ Image previews
- ✅ Secure file storage
- ✅ File cleanup on deletion

## 📁 File Structure

The package now includes:
- `CommentAttachment` model
- `HandleFileUpload` action
- `FileUploadRule` validation
- `CommentAttachmentPolicy` authorization
- Enhanced Livewire components
- JavaScript file upload handling
- Blade templates for file display

## 🚨 Important Notes

1. **Storage Permissions**: Ensure your storage directory is writable
2. **File Limits**: Configure appropriate limits for your use case
3. **Security**: The package includes MIME type validation for security
4. **Cleanup**: Files are automatically deleted when comments are deleted
