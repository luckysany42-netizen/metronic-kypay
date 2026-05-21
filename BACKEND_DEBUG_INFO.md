# Backend Debug Information - 10 May 2026

## ✅ SERVER STATUS

**Server sekarang RUNNING di: http://192.168.1.74:8000**

### Penting ⚠️
- **IP yang benar: 192.168.1.74** (bukan 192.168.1.75)
- **Port: 8000**
- Server sudah tested dan respond ke requests

---

## CORS Configuration ✅

File: `config/cors.php`
```php
'paths'                    => ['api/*'],
'allowed_methods'          => ['*'],
'allowed_origins'          => ['*'],  // ✅ Allow semua origins
'allowed_origins_patterns' => [],
'allowed_headers'          => ['*'],  // ✅ Allow semua headers
'supports_credentials'     => false,
```

**Status: BAIK** - CORS sudah configured untuk accept requests dari mobile/emulator

---

## API ENDPOINT: /api/register

### Method: POST ✅

### Request Body Format:
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+628123456789",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Field yang Required:**
- `first_name` (string, max 255) ✅
- `last_name` (string, max 255) ✅
- `phone` (string, max 20, unique) ✅
- `email` (email format, unique) ✅
- `password` (min 8 chars, must be confirmed) ✅

### Phone Format Support:
Backend auto-normalize berbagai format phone:
- `+62xxx` ✅
- `08xxx` → konvert ke `+62xxx` ✅
- `62xxx` → konvert ke `+62xxx` ✅

### Success Response: HTTP 201
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+628123456789",
  "api_token": "xxxxx...",
  "role": "user",
  "created_at": "2026-05-10T...",
  "updated_at": "2026-05-10T..."
}
```

**Automatic actions saat register:**
- User account dibuat
- Wallet otomatis dibuat untuk user
- API token generated dan returned

---

## Server Logs Test

Test dilakukan 2026-05-10 14:19:09:
```
/api/register .................................... ~ 8s
```

✅ Request successfully processed dalam ~8 detik

---

## TROUBLESHOOTING Flutter App

### 1️⃣ Update Flutter App Configuration

Pastikan Flutter app menggunakan IP yang BENAR:
```dart
// ❌ JANGAN PAKAI
const String baseUrl = 'http://192.168.1.75:8000/api';

// ✅ PAKAI INI
const String baseUrl = 'http://192.168.1.74:8000/api';
```

### 2️⃣ Test dari Android Device/Emulator

Dari terminal device:
```bash
# Cek connectivity ke backend
curl -X POST http://192.168.1.74:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Test",
    "last_name": "User",
    "email": "test123@example.com",
    "phone": "+628123456789",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 3️⃣ Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| CONNECT_TIMEOUT | Device tidak bisa reach IP:port | Pastikan IP 192.168.1.74 benar, device sama network |
| Connection Refused | Port 8000 tidak open | Pastikan Laravel serve command running |
| CORS Error | Browser blocking requests | Server CORS sudah configured ✅ |
| 422 Validation Error | Field validation failed | Check request body format |
| 201 Created ✅ | Success | User & wallet created |

---

## Starting Server Manually

```bash
cd c:\xampp\htdocs\backend

# Start server di IP yang benar
php artisan serve --host 192.168.1.74 --port 8000
```

Output yang benar:
```
INFO  Server running on [http://192.168.1.74:8000].
Press Ctrl+C to stop the server
```

---

## Database Connection

File: `.env`
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crud
DB_USERNAME=root
DB_PASSWORD=
```

**Status:** ✅ Connected (register test successful)

---

## Next Steps

1. ✅ Update Flutter app base URL ke `http://192.168.1.74:8000/api`
2. ✅ Run flutter app dan test register endpoint
3. ✅ Check flutter error logs (expect 201 Created status)
4. ✅ If still error, check Android device network connectivity

---

**Last Updated:** 2026-05-10 14:19:09
