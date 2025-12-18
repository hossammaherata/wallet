# CRM API Documentation

## Overview
This API provides secure endpoints for external CRM integration. All endpoints require authentication using API token and secret.

## Authentication
All API requests must include the following headers:
- `X-API-Token`: Your API token
- `X-API-Secret`: Your API secret

## Base URL
```
https://yourdomain.com/api/crm
```

## Endpoints

### Cities

#### Create/Update Cities
**POST** `/cities`

Creates new cities or updates existing ones based on `shamel_id`.

**Headers:**
```
Content-Type: application/json
X-API-Token: your-api-token
X-API-Secret: your-api-secret
```

**Request Body:**
```json
{
    "cities": [
        {
            "shamel_id": "CITY001",
            "name_ar": "الرياض",
            "name_heb": "ריאד"
        },
        {
            "shamel_id": "CITY002", 
            "name_ar": "جدة",
            "name_heb": "ג'דה"
        }
    ]
}
```

**Response (Success):**
```json
{
    "status": "success",
    "message": "Processed 2 cities successfully"
}
```

**Response (With Errors):**
```json
{
    "status": "success",
    "message": "Processed 1 cities successfully",
    "errors": [
        {
            "index": 1,
            "shamel_id": "CITY002",
            "error": "Database connection failed"
        }
    ]
}
```

**Response (Validation Error):**
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "cities.0.name_ar": ["The cities.0.name_ar field is required."]
    }
}
```

**Response (Authentication Error):**
```json
{
    "status": "error",
    "message": "Invalid API credentials"
}
```

## Setup Instructions

1. **Configure API Credentials:**
   Add the following to your `.env` file:
   ```
   API_AUTH_TOKEN=your-secure-token-here
   API_AUTH_SECRET=your-secure-secret-here
   ```

2. **Generate Secure Credentials:**
   You can use the following PHP code to generate secure tokens:
   ```php
   echo bin2hex(random_bytes(32));
   ```

3. **Rate Limiting:**
   The API is rate limited to 100 requests per minute by default. You can configure this in the `.env` file:
   ```
   API_RATE_LIMIT=100
   API_RATE_LIMIT_DECAY=1
   ```

## Error Codes

- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized (Invalid credentials)
- `422` - Validation Error
- `429` - Too Many Requests (Rate limited)
- `500` - Internal Server Error

## Security Notes

- Always use HTTPS in production
- Keep your API credentials secure
- Rotate credentials regularly
- Monitor API usage for suspicious activity
