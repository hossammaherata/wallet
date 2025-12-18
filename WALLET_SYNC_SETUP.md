# Wallet System Integration - Setup Guide

## Overview

This integration syncs users from Wallet System and sends notifications automatically.

## Features

1. **User Sync (Pull)**: Automatically syncs users from Wallet System via cron job
2. **Notification Webhooks**: Automatically sends notifications to Wallet System when created in Midan

## Configuration

Add these to your `.env` file:

```env
# Wallet System API Configuration
WALLET_API_URL=http://46.62.241.66
WALLET_API_KEY=mdn-wallet-9088db076fcd7f0fad256821f8a3c3c9cba4cdcab915559e7c894d5c4e7bc564
WALLET_WEBHOOK_ENABLED=true
```

## Database Migrations

Run migrations to add required tables:

```bash
php artisan migrate
```

This will create:
- `external_refs` column in `users` table (JSON field to store Wallet System user IDs)
- `wallet_sync_logs` table (tracks sync history)

## Cron Job Setup

The sync command runs automatically every hour. Make sure your cron is set up:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually:

```bash
# Normal sync (only new/updated users since last sync)
php artisan wallet:sync-users

# Force full sync (fetch all users)
php artisan wallet:sync-users --force
```

## How It Works

### User Sync

1. **Cron Job** runs `wallet:sync-users` every hour
2. **Fetches users** from Wallet System API based on last sync date
3. **Processes in batches** (100 users at a time) to prevent server overload
4. **Creates new users** if they don't exist in Midan
5. **Updates existing users** if data changed
6. **Creates wallet** automatically for new users
7. **Stores external_refs** to link Midan user with Wallet System user

### Notification Webhooks

1. When a **notification is created** in Midan (via `NotificationService::send()`)
2. **Automatically sends webhook** to Wallet System
3. **Translates Arabic to English** (simple translation for common messages)
4. **Logs errors** but doesn't fail notification creation if webhook fails

## Data Mapping

### User Sync

| Wallet System Field | Midan Field | Notes |
|---------------------|-------------|-------|
| `id` | `external_refs->wallet` | Stored in JSON field |
| `email` | `email` | Used for matching |
| `phone` | `phone` | Used for matching |
| `name` | `name` | User name |
| `is_active` | `status` | `active` or `suspended` |

### Notification Webhook

| Midan Field | Wallet System Field | Notes |
|-------------|---------------------|-------|
| `title` (Arabic) | `title_ar` | Direct mapping |
| `body` (Arabic) | `body_ar` | Direct mapping |
| `title` (translated) | `title` | Simple English translation |
| `body` (translated) | `body` | Simple English translation |
| `type` | `notification_type` | Notification type |
| `id` | `external_id` | Format: `midan-notif-{id}` |
| `data` | `metadata` | Additional data |

## Monitoring

Check sync logs:

```php
use App\Models\WalletSyncLog;

// Get last sync
$lastSync = WalletSyncLog::latest()->first();

// View sync history
$syncs = WalletSyncLog::orderBy('created_at', 'desc')->paginate(20);
```

## Troubleshooting

### Sync Not Working

1. Check API credentials in `.env`
2. Verify Wallet System API is accessible
3. Check logs: `storage/logs/laravel.log`
4. Run manually: `php artisan wallet:sync-users --force`

### Notifications Not Sending

1. Check `WALLET_WEBHOOK_ENABLED=true` in `.env`
2. Verify API URL and key are correct
3. Check logs for webhook errors
4. Test manually by creating a notification

## API Endpoints Used

### Wallet System → Midan

- `GET /api/sync/users/` - Fetch users (called by Midan)

### Midan → Wallet System

- `POST /api/wallet/webhook/notification/` - Send notification (called by Midan)

## Notes

- **Batch Processing**: Users are processed in batches of 100 to prevent server overload
- **Incremental Sync**: Only fetches users created/updated since last sync (unless `--force` is used)
- **Automatic Wallet Creation**: Wallets are created automatically for new users
- **Error Handling**: Failed syncs are logged but don't stop the process
- **Translation**: Simple English translation for notifications (can be improved with translation service)


