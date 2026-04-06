<?php
/**
 * Upload Class
 * Handles file uploads with validation, resizing, and security
 */

class Upload {
    private $errors = [];
    private $uploadPath;
    private $allowedTypes;
    private $maxSize;
    private $uploadedFiles = [];

    public function __construct() {
        $this->uploadPath = UPLOAD_PATH;
        $this->allowedTypes = UPLOAD_ALLOWED_TYPES;
        $this->maxSize = UPLOAD_MAX_SIZE;

        // Create upload directory if not exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        // Create subdirectories
        $subdirs = ['properties', 'agents', 'thumbnails', 'temp'];
        foreach ($subdirs as $subdir) {
            $path = $this->uploadPath . $subdir . '/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Check GD extension for image processing
        if (!extension_loaded('gd')) {
            $this->errors[] = 'GD extension is not enabled. Please enable PHP GD for image processing.';
        }
    }

    /**
     * Upload single file
     */
    public function upload($file, $directory = 'temp', $options = []) {
        $this->errors = [];

        // Check if file exists
        if (!isset($file) || empty($file['name'])) {
            $this->errors[] = "No file selected";
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadError($file['error']);
            return false;
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = "File size exceeds maximum limit of " . $this->formatSize($this->maxSize);
            return false;
        }

        // Validate file type
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $this->allowedTypes)) {
            $this->errors[] = "Invalid file type. Allowed types: " . implode(', ', $this->getAllowedExtensions());
            return false;
        }

        // Generate unique filename
        $filename = $this->generateFilename($file['name']);
        $filepath = $this->uploadPath . $directory . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->errors[] = "Failed to upload file";
            return false;
        }

        // Set proper permissions
        chmod($filepath, 0644);

        // Resize image if needed
        if (isset($options['resize']) && $options['resize']) {
            if (!extension_loaded('gd')) {
                $this->errors[] = 'Cannot resize image because GD extension is not enabled.';
                return false;
            }
            $maxWidth = $options['max_width'] ?? IMAGE_MAX_WIDTH;
            $maxHeight = $options['max_height'] ?? IMAGE_MAX_HEIGHT;
            if (!$this->resizeImage($filepath, $maxWidth, $maxHeight)) {
                $this->errors[] = 'Image resize failed.';
                return false;
            }
        }

        // Create thumbnail if needed
        $thumbnailPath = null;
        if (isset($options['thumbnail']) && $options['thumbnail']) {
            if (!extension_loaded('gd')) {
                $this->errors[] = 'Cannot create thumbnail because GD extension is not enabled.';
                return false;
            }
            $thumbWidth = $options['thumb_width'] ?? THUMBNAIL_WIDTH;
            $thumbHeight = $options['thumb_height'] ?? THUMBNAIL_HEIGHT;
            $thumbnailPath = $this->createThumbnail($filepath, $directory, $thumbWidth, $thumbHeight);
            if (!$thumbnailPath) {
                $this->errors[] = 'Thumbnail creation failed.';
                return false;
            }
        }

        $result = [
            'filename' => $filename,
            'path' => $filepath,
            'url' => UPLOAD_URL . $directory . '/' . $filename,
            'size' => $file['size'],
            'type' => $fileType,
            'thumbnail' => $thumbnailPath ? UPLOAD_URL . 'thumbnails/' . $thumbnailPath : null
        ];

        $this->uploadedFiles[] = $result;
        return $result;
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple($files, $directory = 'temp', $options = []) {
        $this->errors = [];
        $results = [];

        // Reorganize files array
        $reorganized = $this->reorganizeFilesArray($files);

        foreach ($reorganized as $file) {
            $result = $this->upload($file, $directory, $options);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Upload property image with thumbnail
     */
    public function uploadPropertyImage($file, $propertyId = null) {
        $options = [
            'resize' => true,
            'max_width' => IMAGE_MAX_WIDTH,
            'max_height' => IMAGE_MAX_HEIGHT,
            'thumbnail' => true,
            'thumb_width' => THUMBNAIL_WIDTH,
            'thumb_height' => THUMBNAIL_HEIGHT
        ];

        return $this->upload($file, 'properties', $options);
    }

    /**
     * Upload agent avatar
     */
    public function uploadAgentAvatar($file) {
        $options = [
            'resize' => true,
            'max_width' => 400,
            'max_height' => 400,
            'thumbnail' => false
        ];

        return $this->upload($file, 'agents', $options);
    }

    /**
     * Create thumbnail from image
     */
    public function createThumbnail($sourcePath, $directory, $width = THUMBNAIL_WIDTH, $height = THUMBNAIL_HEIGHT) {
        // Get image info
        $info = getimagesize($sourcePath);
        if (!$info) {
            return false;
        }

        $sourceWidth = $info[0];
        $sourceHeight = $info[1];
        $mimeType = $info['mime'];

        // Calculate dimensions
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $newWidth = (int)($sourceWidth * $ratio);
        $newHeight = (int)($sourceHeight * $ratio);

        // Create source image
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Create thumbnail canvas
        $thumbImage = imagecreatetruecolor($width, $height);

        // Fill with white background (for transparent images)
        $white = imagecolorallocate($thumbImage, 255, 255, 255);
        imagefill($thumbImage, 0, 0, $white);

        // Calculate center position
        $destX = (int)(($width - $newWidth) / 2);
        $destY = (int)(($height - $newHeight) / 2);

        // Resize and crop
        imagecopyresampled(
            $thumbImage, $sourceImage,
            $destX, $destY, 0, 0,
            $newWidth, $newHeight,
            $sourceWidth, $sourceHeight
        );

        // Generate thumbnail filename
        $filename = basename($sourcePath);
        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $this->uploadPath . 'thumbnails/' . $thumbFilename;

        // Save thumbnail
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbImage, $thumbPath, 85);
                break;
            case 'image/png':
                imagepng($thumbImage, $thumbPath, 8);
                break;
            case 'image/gif':
                imagegif($thumbImage, $thumbPath);
                break;
            case 'image/webp':
                imagewebp($thumbImage, $thumbPath, 85);
                break;
        }

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        chmod($thumbPath, 0644);

        return $thumbFilename;
    }

    /**
     * Resize image
     */
    public function resizeImage($filepath, $maxWidth = IMAGE_MAX_WIDTH, $maxHeight = IMAGE_MAX_HEIGHT) {
        $info = getimagesize($filepath);
        if (!$info) {
            return false;
        }

        $sourceWidth = $info[0];
        $sourceHeight = $info[1];
        $mimeType = $info['mime'];

        // Check if resize is needed
        if ($sourceWidth <= $maxWidth && $sourceHeight <= $maxHeight) {
            return true;
        }

        // Calculate new dimensions
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $newWidth = (int)($sourceWidth * $ratio);
        $newHeight = (int)($sourceHeight * $ratio);

        // Create source image
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($filepath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($filepath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($filepath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Create resized image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }

        // Resize
        imagecopyresampled(
            $resizedImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $sourceWidth, $sourceHeight
        );

        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($resizedImage, $filepath, 90);
                break;
            case 'image/png':
                imagepng($resizedImage, $filepath, 6);
                break;
            case 'image/gif':
                imagegif($resizedImage, $filepath);
                break;
            case 'image/webp':
                imagewebp($resizedImage, $filepath, 90);
                break;
        }

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return true;
    }

    /**
     * Delete file
     */
    public function deleteFile($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Delete file by URL
     */
    public function deleteByUrl($url) {
        $filepath = str_replace(UPLOAD_URL, UPLOAD_PATH, $url);
        return $this->deleteFile($filepath);
    }

    /**
     * Generate unique filename
     */
    private function generateFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9-]/', '-', $basename);
        $basename = substr($basename, 0, 50);
        
        $uniqueId = uniqid() . '_' . bin2hex(random_bytes(4));
        
        return $basename . '_' . $uniqueId . '.' . strtolower($extension);
    }

    /**
     * Reorganize files array for multiple uploads
     */
    private function reorganizeFilesArray($files) {
        $reorganized = [];
        
        foreach ($files['name'] as $key => $name) {
            $reorganized[] = [
                'name' => $name,
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
        }
        
        return $reorganized;
    }

    /**
     * Get upload error message
     */
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload'
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Get allowed extensions
     */
    private function getAllowedExtensions() {
        $extensions = [];
        foreach ($this->allowedTypes as $type) {
            $extensions[] = str_replace('image/', '', $type);
        }
        return $extensions;
    }

    /**
     * Format file size
     */
    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get uploaded files
     */
    public function getUploadedFiles() {
        return $this->uploadedFiles;
    }

    /**
     * Validate file before upload (for AJAX validation)
     */
    public function validateFile($file) {
        $this->errors = [];

        if (!isset($file) || empty($file['name'])) {
            $this->errors[] = "No file selected";
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadError($file['error']);
            return false;
        }

        if ($file['size'] > $this->maxSize) {
            $this->errors[] = "File size exceeds maximum limit";
            return false;
        }

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $this->allowedTypes)) {
            $this->errors[] = "Invalid file type";
            return false;
        }

        return true;
    }
}