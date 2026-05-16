# 05. API OVERVIEW

> **Dokumentasi lengkap REST API untuk Mobile App (Flutter)**

---

## 📋 TABLE OF CONTENTS

1. [Base URL & Versioning](#base-url--versioning)
2. [Authentication](#authentication)
3. [Request/Response Format](#requestresponse-format)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [API Endpoints Summary](#api-endpoints-summary)
7. [Common Headers](#common-headers)
8. [Status Codes](#status-codes)

---

## 1. BASE URL & VERSIONING

### Production
```
https://api.yourdomain.com/api
```

### Staging
```
https://staging-api.yourdomain.com/api
```

### Development
```
http://localhost:8000/api
```

### Versioning Strategy
- **Current Version**: v1 (implicit, no version in URL)
- **Future**: `/api/v2/...` when breaking changes introduced
- **Deprecation Policy**: 6 months notice before removing old version

---

## 2. AUTHENTICATION

### Authentication Method
**Laravel Sanctum** - Token-based authentication

### Login Flow
```
1. POST /api/login
   ├─ Request: { driver_id, password }
   └─ Response: { token, driver_data, sim_alert }

2. Store token in secure storage (Flutter Secure Storage)

3. Include token in all subsequent requests:
   Header: Authorization: Bearer {token}

4. Token expires: Never (until logout or revoked)

5. POST /api/logout
   └─ Revokes current token
```

### Single Device Policy
- Only one active token per driver
- New login automatically revokes previous token
- Ensures driver can only be logged in on one device

---

## 3. REQUEST/RESPONSE FORMAT

### Request Format

**Content-Type**: `application/json` (for JSON payloads)  
**Content-Type**: `multipart/form-data` (for file uploads)

**Example JSON Request:**
```json
POST /api/submit-attendance
Content-Type: application/json
Authorization: Bearer {token}

{
  "plate_number": "B1234XYZ",
  "gps_location": "-6.2088, 106.8456",
  "timestamp": "2026-05-14 08:30:00",
  "speedometer_manual": 45000
}
```

**Example Multipart Request:**
```
POST /api/submit-attendance
Content-Type: multipart/form-data
Authorization: Bearer {token}

plate_number: B1234XYZ
gps_location: -6.2088, 106.8456
timestamp: 2026-05-14 08:30:00
speedometer_manual: 45000
selfie_photo: [binary]
speedometer_photo: [binary]
car_condition_photo_1: [binary]
car_condition_photo_2: [binary]
```

### Response Format

**Success Response:**
```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## 4. ERROR HANDLING

### Error Response Structure

```json
{
  "status": "error",
  "message": "Human-readable error message",
  "errors": {
    "field1": ["Error message 1", "Error message 2"],
    "field2": ["Error message"]
  },
  "code": "ERROR_CODE",
  "timestamp": "2026-05-14T08:30:00Z"
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Input validation failed |
| `UNAUTHORIZED` | 401 | Invalid or missing token |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `CONFLICT` | 409 | Resource conflict (e.g., already checked in) |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests |
| `SERVER_ERROR` | 500 | Internal server error |
| `SERVICE_UNAVAILABLE` | 503 | Service temporarily unavailable |

### Error Handling Best Practices

**Flutter Implementation:**
```dart
try {
  final response = await apiService.submitAttendance(data);
  // Handle success
} on ValidationException catch (e) {
  // Show validation errors to user
  showErrorDialog(e.errors);
} on UnauthorizedException catch (e) {
  // Redirect to login
  navigateToLogin();
} on NetworkException catch (e) {
  // Show network error
  showNetworkError();
} catch (e) {
  // Generic error handling
  showGenericError();
}
```

---

## 5. RATE LIMITING

### Rate Limits

| Endpoint | Limit | Window |
|----------|-------|--------|
| `/api/login` | 10 requests | 1 minute |
| All authenticated endpoints | 60 requests | 1 minute |
| `/api/submit-attendance` | 5 requests | 1 minute |
| `/api/submit-end-of-duty` | 5 requests | 1 minute |

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1620123456
```

### Rate Limit Exceeded Response

```json
{
  "status": "error",
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

---

## 6. API ENDPOINTS SUMMARY

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | Driver login | ❌ |
| POST | `/api/logout` | Driver logout | ✅ |
| POST | `/api/change-password` | Change password | ✅ |

### Driver Information

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/driver-details` | Get driver info | ✅ |
| GET | `/api/driver/status` | Check on-duty status | ✅ |
| GET | `/api/driver/history` | Get attendance history | ✅ |

### Attendance Operations

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/submit-attendance` | Check-in | ✅ |
| POST | `/api/submit-end-of-duty` | Check-out | ✅ |
| POST | `/api/submit-emergency-report` | Emergency report | ✅ |

### Utilities

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/health` | Health check | ❌ |
| POST | `/api/clear-cache` | Clear driver cache | ✅ |

---

## 7. COMMON HEADERS

### Request Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
User-Agent: FlutterApp/1.0.0 (Android 12)
X-Device-ID: {unique_device_id}
X-App-Version: 1.0.0
```

### Response Headers

```
Content-Type: application/json
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1620123456
X-Request-ID: abc123def456
```

---

## 8. STATUS CODES

### Success Codes

| Code | Description | Usage |
|------|-------------|-------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST (resource created) |
| 204 | No Content | Successful DELETE (no response body) |

### Client Error Codes

| Code | Description | Usage |
|------|-------------|-------|
| 400 | Bad Request | Malformed request |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Resource conflict |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |

### Server Error Codes

| Code | Description | Usage |
|------|-------------|-------|
| 500 | Internal Server Error | Unexpected server error |
| 503 | Service Unavailable | Server temporarily unavailable |

---

## 9. PAGINATION

### Pagination Format

**Request:**
```
GET /api/driver/history?page=2&per_page=20
```

**Response:**
```json
{
  "status": "success",
  "data": [...],
  "meta": {
    "current_page": 2,
    "from": 21,
    "last_page": 5,
    "per_page": 20,
    "to": 40,
    "total": 100
  },
  "links": {
    "first": "/api/driver/history?page=1",
    "last": "/api/driver/history?page=5",
    "prev": "/api/driver/history?page=1",
    "next": "/api/driver/history?page=3"
  }
}
```

---

## 10. FILTERING & SORTING

### Filtering

```
GET /api/driver/history?start_date=2026-01-01&end_date=2026-05-14
```

### Sorting

```
GET /api/driver/history?sort_by=time_in&sort_order=desc
```

### Multiple Filters

```
GET /api/driver/history?start_date=2026-01-01&vehicle_id=5&sort_by=time_in
```

---

## 11. FILE UPLOADS

### Image Upload Requirements

**Accepted Formats**: JPEG, JPG, PNG  
**Max Size**: 5 MB (5120 KB)  
**Recommended Size**: 1200px width (auto-compressed by server)  
**Compression**: JPEG quality 70%

### Upload Example

```dart
// Flutter Example
final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/submit-attendance'));
request.headers['Authorization'] = 'Bearer $token';
request.fields['plate_number'] = 'B1234XYZ';
request.files.add(await http.MultipartFile.fromPath(
  'selfie_photo',
  selfieFile.path,
  contentType: MediaType('image', 'jpeg'),
));
```

---

## 12. CACHING STRATEGY

### Client-Side Caching

**Recommended Cache Duration:**
- Driver details: 1 hour
- Driver status: 1 minute
- Attendance history: 5 minutes

**Cache Invalidation:**
- After check-in: Clear status cache
- After check-out: Clear status & history cache
- After logout: Clear all cache

### Server-Side Caching

- Driver status: 60 seconds
- Attendance history: 300 seconds (5 minutes)
- Cache automatically cleared on data changes

---

## 13. TESTING

### Postman Collection

Download: [API_Collection.postman_collection.json](./postman/API_Collection.postman_collection.json)

### Test Credentials

**Staging Environment:**
```
Driver ID: TEST001
Password: test123
```

### Health Check

```bash
curl https://api.yourdomain.com/api/health
```

**Expected Response:**
```json
{
  "status": "OK",
  "timestamp": "2026-05-14 08:30:00",
  "ip": "192.168.1.1"
}
```

---

## 14. CHANGELOG

### Version 1.0.0 (2026-05-14)
- Initial API release
- Authentication endpoints
- Attendance operations
- Emergency reporting

### Upcoming (v1.1.0)
- Vehicle health status endpoint
- Maintenance schedule endpoint
- Push notification registration

---

**Document Version**: 1.0  
**Last Updated**: 2026-05-14  
**API Version**: v1  
**Owner**: Backend Team

---

**Related Documents:**
- [06. Authentication API](./06_API_AUTHENTICATION.md)
- [07. Attendance API](./07_API_ATTENDANCE.md)
- [08. Emergency Report API](./08_API_EMERGENCY.md)
- [09. API Error Handling](./09_API_ERROR_HANDLING.md)
