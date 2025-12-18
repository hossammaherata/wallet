# Wallet System Integration Guide

This document describes the integration between Midan Nights and an external wallet system.

## Overview

The wallet system provides:
- **User wallet balance** - Stored locally, source of truth for spendable points
- **Transaction history** - Complete audit trail of all point movements
- **Incoming webhooks** - External system notifies us of point consumption/transfers
- **Outgoing notifications** - We notify external system when users earn points

## Architecture

```
┌─────────────────────┐                    ┌─────────────────────┐
│                     │                    │                     │
│   Midan Nights      │◄──── Webhooks ────►│  External Wallet    │
│   (This System)     │                    │     System          │
│                     │                    │                     │
└─────────────────────┘                    └─────────────────────┘
         │
         ▼
┌─────────────────────┐
│   UserWallet        │ ─── Source of truth for balance
│   WalletTransaction │ ─── Complete transaction history
└─────────────────────┘
```

## Data Models

### UserWallet
Stores user's current point balance.

| Field | Type | Description |
|-------|------|-------------|
| user | OneToOne | Link to User |
| balance | Integer | Current spendable points |
| created_at | DateTime | Wallet creation time |
| updated_at | DateTime | Last update time |

### WalletTransaction
Records all point movements for audit trail.

| Field | Type | Description |
|-------|------|-------------|
| user | ForeignKey | User who owns transaction |
| transaction_type | String | Type of transaction (see below) |
| amount | Integer | Points (+credit, -debit) |
| balance_after | Integer | Balance after transaction |
| description | String | English description |
| description_ar | String | Arabic description |
| reference_id | String | Internal reference (race_id, etc.) |
| external_id | String | External system transaction ID |
| counterparty_user | ForeignKey | Other user (for transfers) |
| metadata | JSON | Additional data |
| created_at | DateTime | Transaction time |

### Transaction Types

| Type | Description |
|------|-------------|
| `earned` | Points earned from predictions |
| `consumed` | Points spent externally |
| `transfer_in` | Points received from another user |
| `transfer_out` | Points sent to another user |
| `migration` | Initial migration from old system |
| `adjustment` | Admin correction |
| `refund` | Points refunded |

---

## API Endpoints

### User Endpoints (Authenticated)

#### GET /api/wallet/me/
Get current user's wallet balance and recent transactions.

**Response:**
```json
{
    "balance": 150,
    "recent_transactions": [
        {
            "id": 123,
            "transaction_type": "earned",
            "transaction_type_display": "Earned from Prediction",
            "amount": 10,
            "balance_after": 150,
            "description": "Points earned from The Saudi Cup",
            "created_at": "2025-12-16T12:00:00Z"
        }
    ],
    "updated_at": "2025-12-16T12:00:00Z"
}
```

#### GET /api/wallet/me/transactions/
Get full transaction history with pagination.

**Query Parameters:**
- `limit` - Number of transactions (default: 20)
- `offset` - Pagination offset (default: 0)

**Response:**
```json
{
    "count": 45,
    "limit": 20,
    "offset": 0,
    "transactions": [...]
}
```

---

## Webhook Endpoints (External System → Midan)

All webhooks require `X-API-Key` header for authentication.

### POST /api/wallet/webhook/notification/
External system sends notification to a user.

**Headers:**
```
X-API-Key: <WALLET_WEBHOOK_API_KEY>
Content-Type: application/json
```

**Request Body:**
```json
{
    "user_id": 123,
    "email": "user@example.com",
    "phone": "+966501234567",
    "title": "Points Redeemed",
    "title_ar": "تم استبدال النقاط",
    "body": "You've successfully redeemed 100 points",
    "body_ar": "تم استبدال 100 نقطة بنجاح",
    "notification_type": "wallet",
    "external_id": "notif-123",
    "metadata": {}
}
```

**Note:** At least one of `user_id`, `email`, or `phone` must be provided.

**Response (Success):**
```json
{
    "success": true,
    "code": "SUCCESS",
    "message": "Notification created successfully",
    "notification_id": 456
}
```

---

### POST /api/wallet/webhook/points-consumed/
External system notifies that user consumed points (e.g., purchase in external store).

**Headers:**
```
X-API-Key: <WALLET_WEBHOOK_API_KEY>
Content-Type: application/json
```

**Request Body:**
```json
{
    "user_id": 123,
    "email": "user@example.com",
    "phone": "+966501234567",
    "amount": 100,
    "description": "Purchase at partner store",
    "description_ar": "شراء من متجر شريك",
    "external_id": "ext-tx-789",
    "metadata": {
        "store_name": "Partner Store",
        "order_id": "ORD-123"
    }
}
```

**Response (Success):**
```json
{
    "success": true,
    "code": "SUCCESS",
    "message": "Points consumed successfully",
    "transaction_id": 789,
    "new_balance": 50
}
```

**Response (Insufficient Balance):**
```json
{
    "success": false,
    "code": "INSUFFICIENT_BALANCE",
    "message": "Insufficient balance",
    "current_balance": 50,
    "requested_amount": 100
}
```

---

### POST /api/wallet/webhook/points-transferred/
External system notifies that user transferred points to another user.

**Headers:**
```
X-API-Key: <WALLET_WEBHOOK_API_KEY>
Content-Type: application/json
```

**Request Body:**
```json
{
    "from_user_id": 123,
    "from_email": "sender@example.com",
    "from_phone": "+966501234567",
    "to_user_id": 456,
    "to_email": "recipient@example.com",
    "to_phone": "+966507654321",
    "amount": 50,
    "description": "Gift transfer",
    "description_ar": "هدية",
    "external_id": "transfer-101",
    "metadata": {}
}
```

**Response (Success):**
```json
{
    "success": true,
    "code": "SUCCESS",
    "message": "Transfer processed successfully",
    "from_transaction_id": 101,
    "to_transaction_id": 102,
    "from_new_balance": 100,
    "to_new_balance": 150
}
```

---

## Outgoing Webhook (Midan → External System)

When configured, Midan notifies the external system when users earn points from predictions.

### Configuration (Environment Variables)
```bash
WALLET_EXTERNAL_ENABLED=true
WALLET_EXTERNAL_API_URL=https://wallet.example.com/api
WALLET_EXTERNAL_API_KEY=your-external-api-key
```

### POST {WALLET_EXTERNAL_API_URL}/webhook/points-earned/
Midan sends this when user earns points from race predictions.

**Headers:**
```
X-API-Key: <WALLET_EXTERNAL_API_KEY>
Content-Type: application/json
```

**Request Body:**
```json
{
    "user_id": 123,
    "email": "user@example.com",
    "phone": "+966501234567",
    "amount": 10,
    "transaction_type": "earned",
    "description": "Points earned from The Saudi Cup",
    "reference_id": "race_45_prediction_789",
    "timestamp": "2025-12-16T12:00:00Z"
}
```

---

## Response Codes

| Code | Description |
|------|-------------|
| `SUCCESS` | Operation completed successfully |
| `UNAUTHORIZED` | Invalid or missing API key |
| `INVALID_PAYLOAD` | Request body validation failed |
| `USER_NOT_FOUND` | User not found by ID/email/phone |
| `INSUFFICIENT_BALANCE` | User doesn't have enough points |
| `DUPLICATE_TRANSACTION` | Transaction with this external_id already processed |
| `INTERNAL_ERROR` | Server error |

---

## Race Points Integration

When race results are announced:
1. Points are calculated for each prediction
2. `UserChampionshipPoints` is updated (existing behavior)
3. **NEW:** Points are credited to `UserWallet`
4. **NEW:** `WalletTransaction` is created with type `earned`
5. **NEW:** External wallet system is notified (if enabled)

---

## Migration Script

To migrate existing championship points to wallets:

```bash
# Preview (dry run)
python manage.py migrate_points_to_wallet --dry-run

# Execute migration
python manage.py migrate_points_to_wallet
```

This will:
1. Sum all `UserChampionshipPoints` for each user
2. Create `UserWallet` with that balance
3. Create `WalletTransaction` with type `migration` for audit

---

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `WALLET_WEBHOOK_API_KEY` | `wallet-webhook-secret-...` | API key for incoming webhooks |
| `WALLET_EXTERNAL_ENABLED` | `false` | Enable outgoing notifications |
| `WALLET_EXTERNAL_API_URL` | `https://wallet.example.com/api` | External wallet API base URL |
| `WALLET_EXTERNAL_API_KEY` | `external-wallet-api-key-here` | API key for outgoing requests |

---

## Security Considerations

1. **API Key Validation** - All webhooks require valid `X-API-Key` header
2. **Duplicate Prevention** - External transactions checked by `external_id`
3. **Atomic Transactions** - Database operations wrapped in atomic blocks
4. **Webhook Logging** - All requests logged for audit trail
5. **Balance Verification** - Deductions check balance before processing
