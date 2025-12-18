# Firebase Cloud Messaging (FCM) Setup Guide

## Overview
This system uses Firebase Cloud Messaging (FCM) to send push notifications to mobile applications.

## Prerequisites
1. Firebase project created at [Firebase Console](https://console.firebase.google.com/)
2. Server Key from Firebase Cloud Messaging settings

## Configuration

### 1. Get Firebase Server Key
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Go to **Project Settings** (gear icon)
4. Go to **Cloud Messaging** tab
5. Copy the **Server Key** (under Cloud Messaging API (Legacy))

### 2. Add to .env file
Add the following to your `.env` file:

```env
FIREBASE_SERVER_KEY=your-server-key-here
```

### 3. Enable Firebase for Users
- Users can enable/disable Firebase notifications via API
- Default: `firebase_enabled = true`
- Users must provide FCM token via API endpoint

## API Endpoints

### Update FCM Token
**POST** `/api/v1/notifications/fcm-token`

**Request:**
```json
{
    "fcm_token": "user-fcm-token-here"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم تحديث رمز FCM بنجاح",
    "data": {
        "fcm_token": "user-fcm-token-here",
        "firebase_enabled": true
    }
}
```

### Toggle Firebase Notifications
**POST** `/api/v1/notifications/toggle-firebase`

**Request:**
```json
{
    "enabled": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم تفعيل الإشعارات",
    "data": {
        "firebase_enabled": true
    }
}
```

### Get Notifications
**GET** `/api/v1/notifications?per_page=15&read=false`

**Query Parameters:**
- `per_page`: Number of items per page (default: 15)
- `read`: Filter by read status (true/false/null for all)

**Response:**
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

### Get Unread Count
**GET** `/api/v1/notifications/unread-count`

**Response:**
```json
{
    "success": true,
    "message": "تم جلب عدد الإشعارات غير المقروءة بنجاح",
    "data": {
        "unread_count": 5
    }
}
```

### Mark Notification as Read
**POST** `/api/v1/notifications/{id}/read`

**Response:**
```json
{
    "success": true,
    "message": "تم تحديد الإشعار كمقروء"
}
```

### Mark All Notifications as Read
**POST** `/api/v1/notifications/read-all`

**Response:**
```json
{
    "success": true,
    "message": "تم تحديد 5 إشعار كمقروء",
    "data": {
        "marked_count": 5
    }
}
```

## Notification Types

### 1. Transaction Received (`transaction_received`)
Sent when a user receives a transfer from another user.

### 2. Transaction Sent (`transaction_sent`)
Sent when a user successfully sends a transfer to another user.

### 3. Payment Received (`payment_received`)
Sent to stores when they receive a payment from a user.

### 4. Payment Sent (`payment_sent`)
Sent to users when they successfully pay a store.

## When Notifications are Sent

Notifications are automatically sent when:
- ✅ Transfer is successful (both sender and receiver get notifications)
- ✅ Payment to store is successful (both user and store get notifications)

## Firebase Configuration

### Android Setup
1. Add `google-services.json` to your Android app
2. Configure Firebase in your Android project
3. Get FCM token using Firebase SDK
4. Send token to API endpoint

### iOS Setup
1. Add `GoogleService-Info.plist` to your iOS app
2. Configure Firebase in your iOS project
3. Get FCM token using Firebase SDK
4. Send token to API endpoint

## Testing

### Test Notification
You can test notifications using Firebase Console:
1. Go to Firebase Console > Cloud Messaging
2. Click "Send test message"
3. Enter FCM token
4. Send test notification

## Troubleshooting

### Notifications not received
1. Check if `FIREBASE_SERVER_KEY` is set in `.env`
2. Verify user has `firebase_enabled = true`
3. Verify user has valid `fcm_token`
4. Check Laravel logs for Firebase errors
5. Verify FCM token is valid (not expired)

### Firebase errors in logs
- Check Firebase Server Key is correct
- Verify FCM token format
- Check network connectivity to Firebase servers

## Security Notes

- FCM Server Key should be kept secret
- Never commit `.env` file to version control
- Use environment variables for sensitive data
- Regularly rotate Firebase Server Key if compromised

