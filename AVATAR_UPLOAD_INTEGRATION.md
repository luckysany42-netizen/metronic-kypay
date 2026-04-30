# Image Upload Integration Guide

**For Mobile Apps & Web Frontends**

---

## Quick Start

### Basic Request
```bash
POST /api/profile/avatar
Authorization: Token {api_token}
Content-Type: multipart/form-data

Form Field: avatar (File)
```

### cURL Example
```bash
curl -X POST http://localhost:8000/api/profile/avatar \
  -H "Authorization: Token YOUR_TOKEN_HERE" \
  -F "avatar=@/path/to/image.jpg"
```

---

## Flutter Implementation Reference

**Client-Side (Already Implemented)**
```dart
// Image compression before upload
final compressedFile = await FlutterImageCompress.compressAndGetFile(
  pickedFile.path,
  "${Directory.systemTemp.path}/avatar_${DateTime.now().millisecondsSinceEpoch}.jpg",
  quality: 75,
  format: CompressFormat.jpeg,
);

// Check file size (max 5MB)
if (compressedFile.lengthSync() > 5 * 1024 * 1024) {
  showError("Ukuran foto maksimal 5MB");
  return;
}

// Upload
final formData = FormData.fromMap({
  'avatar': await MultipartFile.fromFile(
    compressedFile.path,
    filename: 'avatar.jpg',
  ),
});

final response = await _apiClient.post('/profile/avatar', data: formData);
```

**Server-Side Response**
```json
{
  "success": true,
  "message": "Foto profil berhasil diperbarui",
  "avatar": "2026-04-30-044115-hash.png",
  "avatar_url": "http://localhost/uploads/avatars/2026-04-30-044115-hash.png",
  "file_info": {
    "name": "avatar.jpg",
    "size": 185920,
    "mime_type": "image/jpeg",
    "stored_as": "2026-04-30-044115-hash.png"
  },
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "avatar": "2026-04-30-044115-hash.png",
    ...
  }
}
```

---

## Web Frontend Integration

**Vue 3 + TypeScript (Already Implemented)**
```typescript
// In resources/js/stores/auth.ts
function uploadAvatar(file: File) {
  const formData = new FormData();
  formData.append("avatar", file);
  return ApiService.post("profile/avatar", formData)
    .then(({ data }) => {
      user.value = data.user;
      return data;
    })
    .catch(({ response }) => {
      setError(response.data.errors);
      throw response;
    });
}
```

**Component Usage**
```vue
<template>
  <input 
    type="file" 
    accept="image/jpg,image/jpeg,image/png"
    @change="onAvatarChange"
  />
  <img 
    :src="`${apiUrl}/uploads/${user.avatar}`"
    alt="Avatar"
  />
</template>

<script setup>
const avatarUrl = computed(() => {
  if (!user.avatar) return null;
  return `${import.meta.env.VITE_APP_API_URL?.replace('/api', '')}/uploads/avatars/${user.avatar}`;
});
</script>
```

---

## Error Handling

### 401 - No/Invalid Token
```json
{
  "success": false,
  "message": "Unauthorized. Token tidak ditemukan."
}
```

### 422 - Validation Error
```json
{
  "errors": {
    "avatar": [
      "File harus berupa gambar yang valid",
      "Format file hanya support JPEG (.jpg, .jpeg) dan PNG (.png)"
    ]
  }
}
```

**Common Errors:**
- `"File wajib diupload"` - No file sent
- `"Format file hanya support..."` - Wrong file type (PDF, WebP, etc.)
- `"Ukuran file maksimal 5MB"` - File exceeds 5MB limit
- `"File harus berupa gambar yang valid"` - File corrupted or fake image

---

## File Storage Details

**Location:** `/public/uploads/avatars/`

**Filename Pattern:** `{TIMESTAMP}-{HASH}.{EXT}`
- `TIMESTAMP`: `Y-m-d-His` format (e.g., `2026-04-30-044115`)
- `HASH`: MD5 hash of file content (prevents duplicates)
- `EXT`: Original extension (jpg, jpeg, or png)

**Example:**
```
/public/uploads/avatars/
├── 2026-04-30-044115-1954a4413b31311d80ce6fe37c86649d.png
├── 2026-04-30-055230-7a3c8f2b1e9d6c4a0b5f8e1d2c3a4b5c.jpg
└── 2026-05-01-120530-9f5e8d7c6b4a3f2e1d0c9b8a7f6e5d4c.jpeg
```

**Cleanup:**
- Old avatar automatically deleted when new one uploaded
- No orphaned files left on disk

---

## Environment Configuration

### Local Development
```bash
APP_URL=http://localhost:8000
FILESYSTEM_DISK=local
```

### Production (Railway)
```bash
APP_URL=https://your-domain.com
FILESYSTEM_DISK=local
# Files persist in /public/uploads/ (not ephemeral)
```

---

## Important Notes

⚠️ **Authentication Format**
- Use: `Authorization: Token {api_token}`
- NOT: `Authorization: Bearer {api_token}` (Bearer not supported)

✅ **Supported Formats**
- JPEG: `.jpg`, `.jpeg` (recommended - smaller size)
- PNG: `.png`

❌ **Not Supported**
- WebP (Flutter compresses to JPEG instead)
- HEIC (use JPEG conversion)
- GIF, BMP, TIFF, etc.

📱 **Mobile App Optimization**
- Pre-compress to JPEG with quality 75
- Validates size before upload (max 5MB)
- Proper content-type: `image/jpeg` or `image/png`

🌐 **CORS Support**
- Enabled for all origins
- Supports multipart/form-data requests

---

## Troubleshooting

**Problem:** `401 Unauthorized. Token tidak ditemukan.`
- Solution: Ensure header format is `Authorization: Token {token}` (not Bearer)

**Problem:** `File harus berupa gambar yang valid`
- Solution: Ensure file is actual image (not fake/corrupted), not just renamed

**Problem:** `Format file hanya support JPEG dan PNG`
- Solution: Only upload .jpg, .jpeg, or .png files

**Problem:** `Ukuran file maksimal 5MB`
- Solution: Compress image before upload (Flutter app does this automatically)

**Problem:** Avatar not updating on web after upload
- Solution: Clear browser cache, check avatar_url in response, verify /uploads folder access

**Problem:** Old avatar still visible after upload
- Solution: Verify browser cache, check that avatar_url changed in response, check database

---

## API Response Structure

```typescript
interface UploadAvatarResponse {
  success: boolean;
  message: string;
  avatar: string;                    // Filename only
  avatar_url: string;                // Full URL for display
  file_info: {
    name: string;                    // Original filename
    size: number;                    // File size in bytes
    mime_type: string;               // e.g., "image/jpeg"
    stored_as: string;               // Actual stored filename
  };
  user: {
    id: number;
    name: string;
    email: string;
    avatar: string;                  // Updated avatar filename
    role: string;
    // ... other user fields
  };
}
```

---

## Testing with cURL

```bash
# Create test token
TOKEN="your_actual_token_here"

# Upload JPEG
curl -X POST http://localhost:8000/api/profile/avatar \
  -H "Authorization: Token $TOKEN" \
  -F "avatar=@test.jpg"

# Upload PNG
curl -X POST http://localhost:8000/api/profile/avatar \
  -H "Authorization: Token $TOKEN" \
  -F "avatar=@test.png"

# Test unauthorized
curl -X POST http://localhost:8000/api/profile/avatar \
  -F "avatar=@test.jpg"
# Should return: 401 Token tidak ditemukan
```

---

**Last Updated:** April 30, 2026  
**Status:** ✅ Production Ready  
**Tested:** Yes (JPEG, PNG, validation, auth, cleanup)

