# Commentions - File Upload Configuration

## Storage Driver Configuration

The package supports any Laravel storage driver through configuration. Here are examples for different scenarios:

### 1. Local Storage (Default)
```env
COMMENTIONS_UPLOADS_DISK=local
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### 2. Public Storage
```env
COMMENTIONS_UPLOADS_DISK=public
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### 3. Amazon S3
```env
COMMENTIONS_UPLOADS_DISK=s3
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

Make sure your `config/filesystems.php` has the S3 configuration:
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
],
```

### 4. Google Cloud Storage
```env
COMMENTIONS_UPLOADS_DISK=gcs
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### 5. DigitalOcean Spaces
```env
COMMENTIONS_UPLOADS_DISK=spaces
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### 6. Custom Storage Driver
```env
COMMENTIONS_UPLOADS_DISK=my_custom_disk
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

## File Upload Configuration

### Environment Variables
```env
# Enable/disable file uploads
COMMENTIONS_UPLOADS_ENABLED=true

# Maximum file size in KB (default: 10240 = 10MB)
COMMENTIONS_UPLOADS_MAX_SIZE=10240

# Maximum number of files per comment (default: 5)
COMMENTIONS_UPLOADS_MAX_FILES=5

# Allowed file types (comma-separated)
COMMENTIONS_UPLOADS_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx,txt,zip

# Storage disk to use
COMMENTIONS_UPLOADS_DISK=public

# Storage path within the disk
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### Configuration File
The package uses `config/commentions.php` for all settings:

```php
'uploads' => [
    'enabled' => env('COMMENTIONS_UPLOADS_ENABLED', true),
    'max_file_size' => env('COMMENTIONS_UPLOADS_MAX_SIZE', 10240), // KB
    'max_files' => env('COMMENTIONS_UPLOADS_MAX_FILES', 5),
    'allowed_types' => explode(',', env('COMMENTIONS_UPLOADS_ALLOWED_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt,zip')),
    'disk' => env('COMMENTIONS_UPLOADS_DISK', 'local'),
    'path' => env('COMMENTIONS_UPLOADS_PATH', 'commentions/attachments'),
],
```

## Dynamic Storage Features

✅ **Automatic Disk Detection**: The package automatically uses the configured disk for all operations
✅ **URL Generation**: File URLs are generated using the correct disk's URL method
✅ **Path Resolution**: File paths are resolved using the correct disk's path method
✅ **File Deletion**: Files are deleted from the correct disk when comments are removed
✅ **Validation**: File validation uses the configured limits and allowed types

## Examples

### Using S3 for Production
```env
COMMENTIONS_UPLOADS_DISK=s3
COMMENTIONS_UPLOADS_PATH=production/commentions/attachments
```

### Using Local Storage for Development
```env
COMMENTIONS_UPLOADS_DISK=local
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

### Using Public Storage for Simple Deployments
```env
COMMENTIONS_UPLOADS_DISK=public
COMMENTIONS_UPLOADS_PATH=commentions/attachments
```

The package will automatically handle all storage operations using the configured disk and path.
