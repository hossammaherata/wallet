# Wallet System - User Integration Guide

This document describes the user synchronization integration between **Midan Nights** and the **Wallet System**.

## Overview

The Wallet System integrates with Midan Nights for:
- **User Sync** - Bi-directional user registration/updates
- **Pull Sync** - Both systems can fetch users from each other
- **Points Integration** - (See `wallet_integration.md` for wallet/points features)

---

## Configuration

### Midan Nights Settings

Add these environment variables to Midan Nights:

```bash
# Wallet System Configuration
WALLET_API_URL=https://wallet.example.com/api
WALLET_API_KEY=your-api-key-to-call-wallet         # Midan uses this to call Wallet
WALLET_WEBHOOK_KEY=wallet-key-to-call-midan        # Wallet uses this to call Midan
```

### Wallet System Settings

Your system should configure:

```bash
# Midan Nights Integration
MIDAN_API_URL=https://midan-nights.com/api
MIDAN_WEBHOOK_KEY=wallet-key-to-call-midan         # Same as WALLET_WEBHOOK_KEY above
```

---

## API Endpoints

### Base URL

All Midan Nights sync endpoints are under:
```
https://midan-nights.com/api/sync/
```

### Authentication

All requests must include the API key header:
```
X-API-Key: wallet-key-to-call-midan
```

---

## Endpoint: Notify User Registered

Call this webhook when a user registers on the Wallet system.

### Request

```http
POST /api/sync/webhook/user-registered/
X-API-Key: wallet-key-to-call-midan
Content-Type: application/json
```

```json
{
    "system": "wallet",
    "email": "user@example.com",
    "phone": "+966501234567",
    "external_user_id": "wallet-user-789",
    "metadata": {
        "wallet_created_at": "2025-12-17T10:00:00Z",
        "initial_balance": 0
    }
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `system` | string | Yes | Must be `"wallet"` |
| `email` | string | No* | User's email |
| `phone` | string | No* | User's phone (E.164 format) |
| `external_user_id` | string | Yes | User's ID in Wallet system |
| `metadata` | object | No | Additional wallet data |

*Either `email` or `phone` is required.

### Response (Success)

```json
{
    "success": true,
    "code": "SUCCESS",
    "user_id": 123,
    "is_new_user": true
}
```

| Field | Description |
|-------|-------------|
| `user_id` | Midan Nights user ID |
| `is_new_user` | `true` if user was created, `false` if existing |

### Response (Error)

```json
{
    "success": false,
    "code": "INVALID_PAYLOAD",
    "message": "Either email or phone is required"
}
```

---

## Endpoint: Notify User Updated

Call this webhook when a user's data is updated on the Wallet system.

### Request

```http
POST /api/sync/webhook/user-updated/
X-API-Key: wallet-key-to-call-midan
Content-Type: application/json
```

```json
{
    "system": "wallet",
    "external_user_id": "wallet-user-789",
    "email": "newemail@example.com",
    "phone": "+966509876543",
    "metadata": {
        "kyc_verified": true
    }
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `system` | string | Yes | Must be `"wallet"` |
| `external_user_id` | string | Yes | User's ID in Wallet system |
| `email` | string | No | New email (if changed) |
| `phone` | string | No | New phone (if changed) |
| `metadata` | object | No | Additional data to store |

### Response (Success)

```json
{
    "success": true,
    "code": "SUCCESS",
    "message": "User updated",
    "user_id": 123
}
```

### Response (User Not Found)

```json
{
    "success": false,
    "code": "USER_NOT_FOUND",
    "message": "No user found with external_id wallet-user-789 on wallet"
}
```

---

## Endpoint: Fetch Users (Pull Sync)

Fetch users from Midan Nights for synchronization.

### Request

```http
GET /api/sync/users/?count=100
X-API-Key: wallet-key-to-call-midan
```

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `count` | int | No* | Get most recent X users (max: 1000) |
| `since` | date | No* | Get users created on/after this date (YYYY-MM-DD) |
| `until` | date | No | Get users created before this date |
| `offset` | int | No | Pagination offset (default: 0) |
| `include_profiles` | bool | No | Include profile data (default: false) |

*Either `count` OR `since` is required.

### Examples

```bash
# Get most recent 100 users
GET /api/sync/users/?count=100

# Get users since January 1, 2025
GET /api/sync/users/?since=2025-01-01

# Get users in date range with profiles
GET /api/sync/users/?since=2025-01-01&until=2025-06-01&include_profiles=true

# Paginate through results
GET /api/sync/users/?since=2025-01-01&offset=100
```

### Response

```json
{
    "success": true,
    "code": "SUCCESS",
    "count": 100,
    "total": 1500,
    "offset": 0,
    "has_more": true,
    "filters": {
        "since": "2025-01-01",
        "until": null
    },
    "users": [
        {
            "id": 123,
            "email": "user@example.com",
            "phone": "+966501234567",
            "email_verified": true,
            "phone_verified": true,
            "is_active": true,
            "created_at": "2025-12-17T10:00:00Z",
            "updated_at": "2025-12-17T12:00:00Z",
            "external_refs": {
                "wallet": "wallet-user-789",
                "tickets": "tickets-user-456"
            },
            "profiles": [
                {
                    "id": 1,
                    "name": "Ahmed",
                    "birthdate": "1990-01-15"
                }
            ]
        }
    ]
}
```

### Pagination

To fetch all users, keep calling with increasing offset until `has_more: false`:

```python
offset = 0
all_users = []

while True:
    response = requests.get(
        f"{MIDAN_API_URL}/sync/users/?since=2025-01-01&offset={offset}",
        headers={"X-API-Key": API_KEY}
    )
    data = response.json()

    all_users.extend(data["users"])

    if not data["has_more"]:
        break

    offset += len(data["users"])
```

---

## Incoming Webhooks (Midan → Wallet)

Midan Nights will call your system when users register/update.

### User Registered Webhook

When a user registers on Midan Nights:

```http
POST {WALLET_API_URL}/webhook/user-registered/
X-API-Key: {WALLET_API_KEY}
Content-Type: application/json
```

```json
{
    "user_id": 123,
    "email": "user@example.com",
    "phone": "+966501234567",
    "email_verified": true,
    "phone_verified": true,
    "created_at": "2025-12-17T10:00:00Z",
    "profiles": [
        {
            "id": 1,
            "name": "Ahmed",
            "birthdate": "1990-01-15"
        }
    ]
}
```

### Expected Response

```json
{
    "success": true,
    "external_user_id": "wallet-user-789"
}
```

If you return `external_user_id`, Midan will store it for future reference.

---

## Response Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `SUCCESS` | 200 | Operation completed successfully |
| `UNAUTHORIZED` | 401 | Invalid or missing API key |
| `INVALID_PAYLOAD` | 400 | Request body validation failed |
| `INVALID_PARAMS` | 400 | Query parameters validation failed |
| `INVALID_SYSTEM` | 400 | Unknown system code |
| `SYSTEM_DISABLED` | 400 | System is disabled |
| `USER_NOT_FOUND` | 404 | User not found |
| `INTERNAL_ERROR` | 500 | Server error |

---

## User Data Mapping

| Midan Field | Wallet Field | Notes |
|-------------|--------------|-------|
| `id` | `midan_user_id` | Midan's internal user ID |
| `email` | `email` | Unique per user |
| `phone` | `phone` | E.164 format (+966...) |
| `email_verified` | - | Whether email is verified |
| `phone_verified` | - | Whether phone is verified |
| `external_refs.wallet` | `id` | Your user ID stored on Midan |

---

## Related: Wallet Points Integration

For wallet balance and transaction webhooks, see **`wallet_integration.md`**:

- **Points Earned** - Midan notifies Wallet when user earns points
- **Points Consumed** - Wallet notifies Midan when user spends points
- **Points Transferred** - Wallet notifies Midan of user-to-user transfers

---

## Implementation Checklist

### Wallet System Should Implement:

- [ ] **Outgoing webhook: user-registered** - Call Midan when user registers
- [ ] **Outgoing webhook: user-updated** - Call Midan when user data changes
- [ ] **Incoming webhook handler** - Accept user-registered from Midan
- [ ] **Pull sync endpoint** - `/sync/users/` for Midan to fetch your users

### Wallet System Should Store:

- [ ] **Midan user ID** - Store `user_id` from webhook responses
- [ ] **API Key** - Securely store the webhook API key

---

## Security Notes

1. **API Key Validation** - Always validate `X-API-Key` header
2. **HTTPS Required** - All communication must use HTTPS in production
3. **Rate Limiting** - Midan limits to 60 requests/minute per API key
4. **IP Whitelisting** (Optional) - Consider whitelisting Midan's IP addresses
5. **Duplicate Prevention** - Handle duplicate webhooks gracefully (idempotent operations)

---

## Testing

### Test Webhook (curl)

```bash
# Register user on Midan
curl -X POST "https://midan-nights.com/api/sync/webhook/user-registered/" \
  -H "X-API-Key: wallet-key-to-call-midan" \
  -H "Content-Type: application/json" \
  -d '{
    "system": "wallet",
    "email": "test@example.com",
    "external_user_id": "wallet-test-123"
  }'
```

### Test Pull Sync (curl)

```bash
# Fetch recent users
curl -X GET "https://midan-nights.com/api/sync/users/?count=10" \
  -H "X-API-Key: wallet-key-to-call-midan"
```

### Test User Update (curl)

```bash
# Update user on Midan
curl -X POST "https://midan-nights.com/api/sync/webhook/user-updated/" \
  -H "X-API-Key: wallet-key-to-call-midan" \
  -H "Content-Type: application/json" \
  -d '{
    "system": "wallet",
    "external_user_id": "wallet-test-123",
    "metadata": {"kyc_verified": true}
  }'
```

---

## Example: Full Sync Workflow

### 1. Initial Setup

```python
# Wallet System: Fetch all existing Midan users
import requests

API_KEY = "wallet-key-to-call-midan"
MIDAN_URL = "https://midan-nights.com/api"

def sync_all_midan_users():
    offset = 0
    while True:
        response = requests.get(
            f"{MIDAN_URL}/sync/users/",
            params={"since": "2020-01-01", "offset": offset},
            headers={"X-API-Key": API_KEY}
        )
        data = response.json()

        for user in data["users"]:
            # Create/update user in Wallet system
            create_or_update_wallet_user(
                midan_id=user["id"],
                email=user["email"],
                phone=user["phone"]
            )

        if not data["has_more"]:
            break
        offset += len(data["users"])
```

### 2. Ongoing Sync (Webhooks)

```python
# Wallet System: Handle incoming webhook from Midan
@app.route("/webhook/user-registered/", methods=["POST"])
def handle_midan_user_registered():
    # Verify API key
    if request.headers.get("X-API-Key") != EXPECTED_API_KEY:
        return {"success": False, "code": "UNAUTHORIZED"}, 401

    data = request.json
    midan_user_id = data["user_id"]
    email = data.get("email")
    phone = data.get("phone")

    # Create wallet for new user
    wallet_user = create_wallet_user(email=email, phone=phone)
    wallet_user.midan_id = midan_user_id
    wallet_user.save()

    return {
        "success": True,
        "external_user_id": str(wallet_user.id)
    }
```

### 3. Notify Midan of New Wallet Users

```python
# Wallet System: Notify Midan when user registers
def notify_midan_user_registered(wallet_user):
    response = requests.post(
        f"{MIDAN_URL}/sync/webhook/user-registered/",
        headers={"X-API-Key": API_KEY},
        json={
            "system": "wallet",
            "email": wallet_user.email,
            "phone": wallet_user.phone,
            "external_user_id": str(wallet_user.id)
        }
    )

    if response.json().get("success"):
        # Store Midan user ID
        wallet_user.midan_id = response.json()["user_id"]
        wallet_user.save()
```

---

## Contact

For integration support:
- **Technical Contact**: [Your contact info]
- **API Documentation**: https://midan-nights.com/api/docs/

---

*Last Updated: 2025-12-17*
