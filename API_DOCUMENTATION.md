# Wallet & Transaction System API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
All protected endpoints require Bearer Token authentication. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

The token is obtained from `/api/v1/login` or `/api/v1/register` endpoints.

---

## API Endpoints

### 1. Authentication

#### 1.1 Register User
**Endpoint:** `POST /api/v1/register`

**Description:** Register a new user account (Mobile only)

**Request Body:**
```json
{
    "name": "أحمد محمد",
    "phone": "0599123456",
    "email": "ahmed@example.com",  // optional
    "password": "password123"
}
```

**Validation Rules:**
- `name`: required, string, max 255
- `phone`: required, string, unique
- `email`: optional, string, email format, unique if provided
- `password`: required, string, min 8 characters

**Success Response (201):**
```json
{
    "success": true,
    "message": "تم التسجيل بنجاح",
    "data": {
        "user": {
            "id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "0599123456",
            "type": "user",
            "status": "active",
            "created_at": "2024-01-01 12:00:00"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "phone": ["رقم الهاتف مستخدم بالفعل"],
        "password": ["كلمة المرور يجب أن تكون على الأقل 8 أحرف"]
    }
}
```

---

#### 1.2 Login
**Endpoint:** `POST /api/v1/login`

**Description:** Login for User or Store

**Request Body:**
```json
{
    "identifier": "0599123456",  // phone or email
    "password": "password123"
}
```

**Validation Rules:**
- `identifier`: required, string (phone or email)
- `password`: required, string

**Abilities by User Type:**
- **User**: `wallet:read`, `wallet:pay`, `wallet:transfer`
- **Store**: `wallet:read`, `wallet:receive`

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم تسجيل الدخول بنجاح",
    "data": {
        "user": {
            "id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "0599123456",
            "type": "user",
            "status": "active"
        },
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "بيانات الدخول غير صحيحة أو الحساب معطل"
}
```

---

### 2. Profile & Wallet

#### 2.1 Get Profile
**Endpoint:** `GET /api/v1/profile`

**Description:** Get authenticated user's profile with wallet information

**Authentication:** Required (Bearer Token)

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم جلب البيانات بنجاح",
    "data": {
        "id": 1,
        "name": "أحمد محمد",
        "email": "ahmed@example.com",
        "phone": "0599123456",
        "type": "user",
        "status": "active",
        "wallet": {
            "id": 1,
            "balance": 1000.50,
            "status": "active"
        },
        "created_at": "2024-01-01 12:00:00"
    }
}
```

---

#### 2.2 Get Wallet Balance
**Endpoint:** `GET /api/v1/wallet`

**Description:** Get current wallet balance

**Authentication:** Required (Bearer Token)

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم جلب الرصيد بنجاح",
    "data": {
        "balance": 1000.50,
        "currency": "points"
    }
}
```

**Notes:**
- If wallet doesn't exist, balance will be 0
- Currency is always "points"

---

### 3. Stores

#### 3.1 Get Active Stores
**Endpoint:** `GET /api/v1/stores`

**Description:** Get list of all active stores

**Authentication:** Required (Bearer Token)

**Usage:**
1. Call this endpoint to get list of stores
2. Display stores in a dropdown/list in mobile app
3. User selects a store
4. Use `store_id` in the `/wallet/pay` endpoint

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم جلب المتاجر بنجاح",
    "data": [
        {
            "id": 2,
            "name": "متجر الأجهزة",
            "phone": "0599987654",
            "email": "store@example.com",
            "status": "active"
        },
        {
            "id": 3,
            "name": "متجر الملابس",
            "phone": "0599887766",
            "email": null,
            "status": "active"
        }
    ]
}
```

---

### 4. Transactions

#### 4.1 Get Transaction History
**Endpoint:** `GET /api/v1/wallet/transactions`

**Description:** Get transaction history for authenticated user

**Authentication:** Required (Bearer Token)

**Query Parameters:**
- `per_page` (optional): Number of items per page (default: 15)
- `status` (optional): Filter by status (`success`, `pending`, `failed`)
- `type` (optional): Filter by type (`purchase`, `transfer`, `credit`, `debit`, `refund`)

**Example:**
```
GET /api/v1/wallet/transactions?per_page=20&status=success&type=purchase
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم جلب المعاملات بنجاح",
    "data": {
        "transactions": [
            {
                "id": 1,
                "reference_id": "550e8400-e29b-41d4-a716-446655440000",
                "amount": 100.50,
                "type": "purchase",
                "status": "success",
                "from_user": {
                    "id": 1,
                    "name": "أحمد محمد",
                    "phone": "0599123456"
                },
                "to_user": {
                    "id": 2,
                    "name": "متجر الأجهزة",
                    "phone": "0599987654"
                },
                "meta": null,
                "created_at": "2024-01-01 12:00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 72
        }
    }
}
```

---

### 5. Payments & Transfers

#### 5.1 Pay to Store
**Endpoint:** `POST /api/v1/wallet/pay`

**Description:** Pay from user wallet to store wallet

**Authentication:** Required (Bearer Token with `wallet:pay` ability)

**Request Body:**
```json
{
    "store_id": 2,
    "amount": 50.00,
    "meta": {
        "order_id": "ORD-12345",
        "description": "Payment for order"
    }
}
```

**Validation Rules:**
- `store_id`: required, exists in users table
- `amount`: required, numeric, min 0.01
- `meta`: optional, array

**How to Use:**
1. First, call `/v1/stores` to get list of active stores
2. User selects a store from the list
3. User enters payment amount
4. Call this endpoint with `store_id` and `amount`

**Success Response (201):**
```json
{
    "success": true,
    "message": "تم الدفع بنجاح",
    "data": {
        "transaction": {
            "id": 1,
            "reference_id": "550e8400-e29b-41d4-a716-446655440000",
            "amount": 50.00,
            "type": "purchase",
            "status": "success",
            "from_user": {
                "id": 1,
                "name": "أحمد محمد",
                "phone": "0599123456"
            },
            "to_user": {
                "id": 2,
                "name": "متجر الأجهزة",
                "phone": "0599987654"
            },
            "created_at": "2024-01-01 12:00:00"
        },
        "new_balance": 950.50
    }
}
```

**Error Responses:**

**400 - Insufficient Balance:**
```json
{
    "success": false,
    "message": "رصيد غير كافٍ"
}
```

**400 - Invalid Store:**
```json
{
    "success": false,
    "message": "المستخدم المحدد ليس متجراً"
}
```

**422 - Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "store_id": ["معرف المتجر مطلوب"],
        "amount": ["المبلغ يجب أن يكون على الأقل 0.01"]
    }
}
```

---

#### 5.2 Transfer to User
**Endpoint:** `POST /api/v1/wallet/transfer`

**Description:** Transfer points from one user to another using phone number

**Authentication:** Required (Bearer Token with `wallet:transfer` ability)

**Request Body:**
```json
{
    "phone": "0599887766",
    "amount": 25.00,
    "meta": {
        "note": "هدية عيد ميلاد"
    }
}
```

**Validation Rules:**
- `phone`: required, string, exists in users table
- `amount`: required, numeric, min 0.01
- `meta`: optional, array

**How to Use:**
1. User enters recipient's phone number
2. User enters transfer amount
3. Call this endpoint with `phone` and `amount`

**Success Response (201):**
```json
{
    "success": true,
    "message": "تم التحويل بنجاح",
    "data": {
        "transaction": {
            "id": 2,
            "reference_id": "660e8400-e29b-41d4-a716-446655440001",
            "amount": 25.00,
            "type": "transfer",
            "status": "success",
            "from_user": {
                "id": 1,
                "name": "أحمد محمد",
                "phone": "0599123456"
            },
            "to_user": {
                "id": 3,
                "name": "محمد علي",
                "phone": "0599887766"
            },
            "created_at": "2024-01-01 12:05:00"
        },
        "new_balance": 925.50
    }
}
```

**Error Responses:**

**400 - User Not Found:**
```json
{
    "success": false,
    "message": "المستخدم غير موجود"
}
```

**400 - Invalid Recipient:**
```json
{
    "success": false,
    "message": "يمكن التحويل فقط للمستخدمين العاديين"
}
```

**400 - Insufficient Balance:**
```json
{
    "success": false,
    "message": "رصيد غير كافٍ"
}
```

**400 - Self Transfer:**
```json
{
    "success": false,
    "message": "لا يمكن التحويل لنفسك"
}
```

**422 - Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "phone": ["رقم الهاتف مطلوب"],
        "amount": ["المبلغ يجب أن يكون على الأقل 0.01"]
    }
}
```

---

## Response Format

All API responses follow this standard format:

### Success Response
```json
{
    "success": true,
    "message": "Message in Arabic",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message in Arabic",
    "errors": { ... }  // Only for validation errors (422)
}
```

---

## HTTP Status Codes

- **200**: Success
- **201**: Created (for register, pay, transfer)
- **400**: Bad Request (business logic errors)
- **401**: Unauthorized (invalid credentials or token)
- **403**: Forbidden (insufficient permissions)
- **422**: Validation Error
- **500**: Server Error

---

## Transaction Types

- `purchase`: Payment from user to store
- `transfer`: Transfer from user to user
- `credit`: Credit transaction (system operations)
- `debit`: Debit transaction (system operations)
- `refund`: Refund transaction

---

## Transaction Status

- `pending`: Transaction is pending
- `success`: Transaction completed successfully
- `failed`: Transaction failed

---

## User Types

- **user**: Regular user (can pay stores and transfer to users)
- **store**: Store account (can receive payments)
- **admin**: Admin account (dashboard only, no API access)

---

## Security Features

1. **Wallet Locking**: Prevents race conditions during transactions
2. **Atomic Operations**: All financial operations use database transactions
3. **Balance Verification**: Checks balance before any operation
4. **Token Abilities**: Different abilities for different user types
5. **Full Audit Trail**: All transactions are logged with reference IDs

---

## Example Workflows

### User Registration Flow
1. User calls `POST /api/v1/register`
2. Receives token and user data
3. Token is saved in mobile app secure storage
4. Token is used for all subsequent requests

### Payment Flow
1. User calls `GET /api/v1/stores` to get store list
2. User selects a store
3. User calls `POST /api/v1/wallet/pay` with `store_id` and `amount`
4. Receives transaction details and new balance

### Transfer Flow
1. User enters recipient phone number
2. User enters transfer amount
3. User calls `POST /api/v1/wallet/transfer` with `phone` and `amount`
4. Receives transaction details and new balance

---

## Postman Collection

Import the `Wallet_Transaction_API.postman_collection.json` file into Postman for easy testing.

**Collection Variables:**
- `base_url`: API base URL (default: `http://localhost:8000/api`)
- `auth_token`: Automatically set after login/register
- `user_id`: Automatically set after login/register

---

---

## 6. Notifications

### 6.1 Update FCM Token
**Endpoint:** `POST /api/v1/notifications/fcm-token`

**Description:** Update Firebase Cloud Messaging (FCM) token for authenticated user

**Authentication:** Required (Bearer Token)

**Request Body:**
```json
{
    "fcm_token": "user-fcm-token-from-firebase-sdk"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم تحديث رمز FCM بنجاح",
    "data": {
        "fcm_token": "user-fcm-token-from-firebase-sdk",
        "firebase_enabled": true
    }
}
```

---

### 6.2 Toggle Firebase Notifications
**Endpoint:** `POST /api/v1/notifications/toggle-firebase`

**Description:** Enable or disable Firebase push notifications

**Authentication:** Required (Bearer Token)

**Request Body:**
```json
{
    "enabled": true
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم تفعيل الإشعارات",
    "data": {
        "firebase_enabled": true
    }
}
```

---

### 6.3 Get Notifications
**Endpoint:** `GET /api/v1/notifications`

**Description:** Get all notifications for authenticated user

**Authentication:** Required (Bearer Token)

**Query Parameters:**
- `per_page` (optional): Number of items per page (default: 15)
- `read` (optional): Filter by read status (true/false/null for all)

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم جلب الإشعارات بنجاح",
    "data": {
        "notifications": [
            {
                "id": 1,
                "type": "transaction_received",
                "title": "تم استلام تحويل",
                "body": "تم استلام مبلغ 50.00 نقطة من أحمد محمد",
                "data": {
                    "transaction_type": "transfer",
                    "amount": 50.00,
                    "from_user": "أحمد محمد",
                    "reference_id": "550e8400-e29b-41d4-a716-446655440000"
                },
                "read": false,
                "read_at": null,
                "created_at": "2024-01-01 12:00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 72
        }
    }
}
```

---

### 6.4 Get Unread Count
**Endpoint:** `GET /api/v1/notifications/unread-count`

**Description:** Get count of unread notifications

**Authentication:** Required (Bearer Token)

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم جلب عدد الإشعارات غير المقروءة بنجاح",
    "data": {
        "unread_count": 5
    }
}
```

---

### 6.5 Mark Notification as Read
**Endpoint:** `POST /api/v1/notifications/{id}/read`

**Description:** Mark a specific notification as read

**Authentication:** Required (Bearer Token)

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم تحديد الإشعار كمقروء"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "الإشعار غير موجود"
}
```

---

### 6.6 Mark All Notifications as Read
**Endpoint:** `POST /api/v1/notifications/read-all`

**Description:** Mark all notifications as read for authenticated user

**Authentication:** Required (Bearer Token)

**Success Response (200):**
```json
{
    "success": true,
    "message": "تم تحديد 5 إشعار كمقروء",
    "data": {
        "marked_count": 5
    }
}
```

---

## Notification Types

- `transaction_received`: User received a transfer
- `transaction_sent`: User sent a transfer
- `payment_received`: Store received a payment
- `payment_sent`: User sent a payment to store

## When Notifications are Sent

Notifications are automatically sent when:
- ✅ Transfer is successful (both sender and receiver get notifications)
- ✅ Payment to store is successful (both user and store get notifications)

## Firebase Configuration

To enable Firebase push notifications:
1. Get Firebase Server Key from Firebase Console
2. Add `FIREBASE_SERVER_KEY` to `.env` file
3. Users must provide FCM token via API
4. Users can enable/disable Firebase notifications

See `FIREBASE_SETUP.md` for detailed setup instructions.

---

## Notes

- All error messages are in Arabic
- All timestamps are in server timezone
- Amounts are in decimal format (2 decimal places)
- Currency is always "points"
- Reference IDs are UUIDs for transaction tracking
- Notifications are stored in database for history
- Unread notifications count is available in profile endpoint

