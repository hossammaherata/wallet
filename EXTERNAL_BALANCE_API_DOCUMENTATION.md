# External Balance Update API Documentation

## نظرة عامة
هذه الـ API مخصصة للأنظمة الخارجية لتحديث أرصدة المستخدمين بشكل مجمّع (Bulk Update).

## المصادقة (Authentication)

### Headers المطلوبة:
```http
X-API-Token: your-api-token
X-API-Secret: your-api-secret
```

يتم تعيين هذه القيم في ملف `.env`:
```env
API_AUTH_TOKEN=your-secure-token-here
API_AUTH_SECRET=your-secure-secret-here
```

## Endpoint

### Update User Balances

**URL:** `/api/external/balances/update`  
**Method:** `POST`  
**Content-Type:** `application/json`

#### Request Body

```json
{
  "reference_id": "unique-reference-12345",
  "balances": {
    "1": 250.00,
    "2": 350.00,
    "5": 1000.50
  }
}
```

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| reference_id | string | Yes | معرّف مرجعي فريد لتتبع العملية ومنع التكرار |
| balances | object | Yes | كائن يحتوي على user_id كمفتاح والرصيد كقيمة |

#### Response Examples

##### نجاح كامل (200 OK)
```json
{
  "success": true,
  "message": "تم تحديث جميع الأرصدة بنجاح",
  "data": {
    "reference_id": "unique-reference-12345",
    "processed_count": 3,
    "failed_count": 0,
    "total_count": 3
  }
}
```

##### نجاح جزئي (207 Multi-Status)
```json
{
  "success": true,
  "message": "تم تحديث بعض الأرصدة بنجاح، ولكن حدثت أخطاء في بعضها",
  "data": {
    "reference_id": "unique-reference-12345",
    "processed_count": 2,
    "failed_count": 1,
    "total_count": 3,
    "errors": [
      "User ID 999 not found"
    ]
  }
}
```

##### Reference ID مكرر (200 OK)
```json
{
  "success": true,
  "message": "تم معالجة هذا المرجع مسبقاً",
  "data": {
    "reference_id": "unique-reference-12345",
    "message": "This reference ID has already been processed",
    "processed_at": "2025-01-20T10:30:00.000000Z",
    "processed_count": 3,
    "failed_count": 0
  }
}
```

##### خطأ في التحقق (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "reference_id": ["Reference ID is required"],
    "balances": ["Balances array is required"]
  }
}
```

##### خطأ في المصادقة (401 Unauthorized)
```json
{
  "status": "error",
  "message": "Invalid API credentials"
}
```

##### فشل كامل (400 Bad Request)
```json
{
  "success": false,
  "message": "فشل تحديث جميع الأرصدة",
  "data": {
    "reference_id": "unique-reference-12345",
    "processed_count": 0,
    "failed_count": 3,
    "total_count": 3,
    "errors": [
      "User ID 100 not found",
      "User ID 200 not found",
      "User ID 300 not found"
    ]
  }
}
```

## ميزات الـ API

### 1. منع التكرار (Idempotency)
- يتم استخدام `reference_id` لتتبع العمليات
- إذا تم إرسال نفس `reference_id` مرتين، سيتم إرجاع نتيجة العملية الأولى
- لا يتم تحديث الأرصدة مرة أخرى

### 2. معالجة مجمّعة (Bulk Processing)
- يمكن تحديث أرصدة متعددة في طلب واحد
- إذا فشل تحديث بعض المستخدمين، يتم الاستمرار مع البقية
- يتم إرجاع تقرير مفصّل بعدد النجاحات والفشل

### 3. إنشاء محافظ تلقائياً
- إذا لم يكن للمستخدم محفظة، يتم إنشاؤها تلقائياً
- الرصيد الافتراضي: 0

### 4. تسجيل الأخطاء (Logging)
- يتم تسجيل جميع الأخطاء في ملف الـ log
- يتم حفظ تفاصيل كل عملية في جدول `external_balance_updates`

### 5. Rate Limiting
- الحد الأقصى: 60 طلب في الدقيقة

## مثال على استخدام cURL

```bash
curl -X POST https://your-domain.com/api/external/balances/update \
  -H "Content-Type: application/json" \
  -H "X-API-Token: your-api-token" \
  -H "X-API-Secret: your-api-secret" \
  -d '{
    "reference_id": "payment-batch-20250120-001",
    "balances": {
      "1": 250.00,
      "2": 350.00,
      "5": 1000.50
    }
  }'
```

## مثال على استخدام PHP

```php
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://your-domain.com/api/external/balances/update');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

$data = [
    'reference_id' => 'payment-batch-20250120-001',
    'balances' => [
        '1' => 250.00,
        '2' => 350.00,
        '5' => 1000.50
    ]
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$headers = [
    'Content-Type: application/json',
    'X-API-Token: your-api-token',
    'X-API-Secret: your-api-secret'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
```

## جدول قاعدة البيانات

### external_balance_updates
يتم حفظ جميع العمليات في هذا الجدول لتتبع السجل ومنع التكرار:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | المعرّف الفريد |
| reference_id | string | المعرّف المرجعي (فريد) |
| balances | json | الأرصدة المرسلة |
| processed_count | int | عدد التحديثات الناجحة |
| failed_count | int | عدد التحديثات الفاشلة |
| status | enum | الحالة: pending, processing, completed, failed |
| error_message | text | رسائل الأخطاء (إن وجدت) |
| created_at | timestamp | تاريخ الإنشاء |
| updated_at | timestamp | تاريخ آخر تحديث |

## ملاحظات مهمة

1. **Reference ID يجب أن يكون فريداً**: استخدم معرّف فريد لكل دفعة (Batch)
2. **User ID يجب أن يكون موجوداً**: التأكد من وجود المستخدم قبل الإرسال
3. **الرصيد لا يمكن أن يكون سالباً**: القيم السالبة سيتم رفضها
4. **الحد الأقصى للرصيد**: 999,999,999.99
5. **يتم استبدال الرصيد**: ليس إضافة، بل استبدال كامل للرصيد

## التشغيل

### 1. تشغيل Migration
```bash
php artisan migrate
```

### 2. تحديث credentials في .env
```env
API_AUTH_TOKEN=your-secure-token-here
API_AUTH_SECRET=your-secure-secret-here
```

### 3. اختبار الـ API
استخدم Postman أو أي أداة لاختبار الـ API مع Headers المطلوبة.

## الأمان

- **لا تشارك** الـ API Token والـ Secret
- **قم بتغيير** القيم الافتراضية في الإنتاج
- **استخدم HTTPS** فقط في الإنتاج
- **تتبع** جميع الطلبات في الـ logs

