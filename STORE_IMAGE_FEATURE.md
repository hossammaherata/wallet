# Store Image Feature - Documentation

## Overview
تم إضافة ميزة رفع وتعديل صورة المتجر (Store) للمسؤول والمتجر نفسه.

## What Was Added

### 1. Database Changes
- ✅ Migration: `add_store_image_to_users_table`
- ✅ Field: `store_image` (nullable string) في جدول `users`
- ✅ تم إضافة `store_image` إلى `fillable` في `User` model

### 2. Admin Panel Features

#### Create Store Page (`resources/js/Pages/Admin/stores/Create.vue`)
- ✅ إضافة حقل رفع الصورة
- ✅ Validation: Max 2MB, JPG/PNG/GIF
- ✅ معالجة رفع الصورة في `StoreController::store()`

#### Edit Store Page (`resources/js/Pages/Admin/stores/Edit.vue`)
- ✅ عرض الصورة الحالية
- ✅ إمكانية تعديل الصورة
- ✅ Preview للصورة الجديدة قبل الحفظ
- ✅ حذف الصورة القديمة عند رفع صورة جديدة

#### Show Store Page (`resources/js/Pages/Admin/stores/Show.vue`)
- ✅ عرض صورة المتجر في صفحة التفاصيل
- ✅ Responsive design

### 3. Store Dashboard Features

#### New Profile Page (`resources/js/Pages/Store/Profile.vue`)
- ✅ صفحة كاملة لتعديل بيانات المتجر
- ✅ رفع وتعديل صورة المتجر
- ✅ تعديل الاسم، الهاتف، البريد
- ✅ تغيير كلمة المرور
- ✅ واجهة عربية كاملة

#### Routes Added
```php
Route::get('/store/profile', [StoreDashboardController::class, 'profile'])->name('store.profile');
Route::post('/store/profile', [StoreDashboardController::class, 'updateProfile'])->name('store.profile.update');
```

### 4. API Resource
- ✅ تحديث `StoreResource` لإرجاع رابط الصورة
- ✅ الصورة تُرجع كـ full URL: `asset('storage/' . $this->store_image)`

## Storage Configuration

الصور تُحفظ في:
```
storage/app/public/stores/
```

تأكد من تشغيل:
```bash
php artisan storage:link
```

## Validation Rules

```php
'store_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
```

- Maximum size: 2MB
- Allowed formats: JPEG, JPG, PNG, GIF

## Image Handling

### Upload
```php
if ($request->hasFile('store_image')) {
    $image = $request->file('store_image');
    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    $imagePath = $image->storeAs('stores', $imageName, 'public');
    $data['store_image'] = $imagePath;
}
```

### Delete Old Image
```php
if ($store->store_image && Storage::disk('public')->exists($store->store_image)) {
    Storage::disk('public')->delete($store->store_image);
}
```

## Frontend Implementation

### Vue Components
- Used `useForm` from Inertia.js
- File input with `@input` or `@change` event
- Preview with `URL.createObjectURL()`

### Example
```vue
<input 
    type="file" 
    @change="handleImageChange"
    accept="image/*"
    class="form-control"
/>

<script>
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.store_image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};
</script>
```

## Access URLs

### Admin Panel
- Create: `/admin/stores/create`
- Edit: `/admin/stores/{id}/edit`
- Show: `/admin/stores/{id}`

### Store Dashboard
- Profile: `/store/profile`

## Files Modified

1. ✅ `database/migrations/2025_12_17_230326_add_store_image_to_users_table.php`
2. ✅ `app/Models/User.php`
3. ✅ `app/Http/Resources/StoreResource.php`
4. ✅ `app/Http/Controllers/Admin/StoreController.php`
5. ✅ `app/Http/Controllers/Store/StoreDashboardController.php`
6. ✅ `resources/js/Pages/Admin/stores/Create.vue`
7. ✅ `resources/js/Pages/Admin/stores/Edit.vue`
8. ✅ `resources/js/Pages/Admin/stores/Show.vue`
9. ✅ `resources/js/Pages/Store/Profile.vue` (NEW)
10. ✅ `routes/web.php`

## Testing

1. Create new store with image
2. Edit existing store and upload new image
3. View store details to see image
4. Store login and update profile image
5. Verify old images are deleted when new ones are uploaded

## Notes

- الصور محفوظة في `storage/app/public/stores/`
- تنسيق اسم الملف: `timestamp_uniqid.extension`
- الصور القديمة تُحذف تلقائياً عند رفع صورة جديدة
- الصور اختيارية (nullable)

