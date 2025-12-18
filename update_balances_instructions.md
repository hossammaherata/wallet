# تعليمات إضافة الرصيد للمستخدمين

## ملف JSON لإضافة الرصيد

استخدم الملف `update_balances_example.json` كقالب لإضافة الرصيد للمستخدمين.

## الصيغة المطلوبة

```json
{
  "reference_id": "unique-reference-id",
  "balances": {
    "external_refs_value": amount,
    "external_refs_value": amount
  }
}
```

## مثال

```json
{
  "reference_id": "balance-update-2025-12-17-001",
  "balances": {
    "1": 250.00,
    "100": 250.00,
    "101": 250.00,
    "102": 350.50,
    "103": 100.00
  }
}
```

## ملاحظات مهمة

1. **reference_id**: يجب أن يكون فريد لكل عملية (للمنع من المعالجة المكررة)
2. **balances**: 
   - المفاتيح هي `external_refs` من جدول users (Wallet System ID)
   - القيم هي المبلغ المراد إضافته (يمكن أن يكون موجب أو سالب)
3. **المبلغ الإيجابي**: يضيف نقاط (credit)
4. **المبلغ السالب**: يخصم نقاط (debit)

## كيفية الاستخدام

### باستخدام cURL:

```bash
curl -X POST "http://your-domain.com/api/external/balances/update" \
  -H "X-API-Token: your-api-token" \
  -H "X-API-Secret: your-api-secret" \
  -H "Content-Type: application/json" \
  -d @update_balances_example.json
```

### باستخدام Postman:

1. Method: `POST`
2. URL: `http://your-domain.com/api/external/balances/update`
3. Headers:
   - `X-API-Token`: your-api-token
   - `X-API-Secret`: your-api-secret
   - `Content-Type`: application/json
4. Body: اختر `raw` و `JSON`، ثم الصق محتوى الملف

## أمثلة على المبالغ

```json
{
  "reference_id": "example-001",
  "balances": {
    "1": 100.00,    // إضافة 100 نقطة
    "2": -50.00,    // خصم 50 نقطة
    "3": 250.50     // إضافة 250.5 نقطة
  }
}
```

## الاستجابة المتوقعة

### نجاح:
```json
{
  "success": true,
  "message": "تم تحديث جميع الأرصدة بنجاح",
  "data": {
    "reference_id": "balance-update-2025-12-17-001",
    "processed_count": 10,
    "failed_count": 0
  }
}
```

### خطأ (مستخدم غير موجود):
```json
{
  "success": true,
  "message": "تم تحديث بعض الأرصدة بنجاح، ولكن حدثت أخطاء في بعضها",
  "data": {
    "reference_id": "balance-update-2025-12-17-001",
    "processed_count": 8,
    "failed_count": 2,
    "errors": [
      "User with Wallet System ID 999 not found",
      "User with Wallet System ID 888 not found"
    ]
  }
}
```

## الحصول على external_refs للمستخدمين

لرؤية جميع المستخدمين مع external_refs:

```bash
php artisan tinker
```

ثم:
```php
App\Models\User::select('id', 'name', 'email', 'external_refs')
    ->whereNotNull('external_refs')
    ->get()
    ->toArray();
```


