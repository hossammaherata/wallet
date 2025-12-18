<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/StoreLayout.vue';
import { BRow, BCol, BCard, BCardBody, BButton } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';

const { store } = usePage().props;

const imagePreview = ref(store.profile_photo_url || null);

const form = useForm({
    name: store.name || '',
    email: store.email || '',
    phone: store.phone || '',
    photo: null,
    password: '',
    password_confirmation: '',
});

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('store.profile.update'), {
                        onSuccess: () => {
                            Swal.fire({
                                title: 'نجح!',
                                text: 'تم تحديث الملف الشخصي بنجاح',
                                icon: 'success',
                                confirmButtonText: 'حسناً'
                            });
                            form.reset('password', 'password_confirmation', 'photo');
                        },
        onError: (errors) => {
            Swal.fire({
                title: 'خطأ!',
                text: 'حدث خطأ أثناء التحديث',
                icon: 'error',
                confirmButtonText: 'حسناً'
            });
        }
    });
};
</script>

<template>
    <Layout>
        <BRow>
            <BCol lg="12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0">الملف الشخصي</h4>
                </div>
            </BCol>
        </BRow>

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <BCardBody>
                        <form @submit.prevent="submit">
                            <!-- Profile Photo -->
                            <BRow>
                                <BCol md="12">
                                    <div class="mb-4 text-center">
                                        <label class="form-label fw-semibold d-block">الصورة الشخصية</label>
                                        <div v-if="imagePreview" class="mb-3">
                                            <img 
                                                :src="imagePreview" 
                                                alt="Profile Photo" 
                                                class="img-thumbnail rounded-circle"
                                                style="width: 150px; height: 150px; object-fit: cover;"
                                            />
                                        </div>
                                        <div v-else class="mb-3">
                                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                                <i class="ri-store-2-line" style="font-size: 3rem; color: #999;"></i>
                                            </div>
                                        </div>
                                        <input 
                                            type="file" 
                                            id="photo"
                                            @change="handleImageChange"
                                            accept="image/*"
                                            class="form-control"
                                            style="max-width: 400px; margin: 0 auto;"
                                            :class="{ 'is-invalid': form.errors.photo }"
                                        />
                                        <small class="text-muted d-block mt-2">الحد الأقصى للحجم: 2 ميجا. الصيغ المدعومة: JPG, PNG, GIF</small>
                                        <div v-if="form.errors.photo" class="invalid-feedback d-block">
                                            {{ form.errors.photo }}
                                        </div>
                                    </div>
                                </BCol>
                            </BRow>

                            <!-- Store Information -->
                            <BRow>
                                <BCol md="6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-semibold">اسم المتجر *</label>
                                        <input 
                                            v-model="form.name" 
                                            type="text" 
                                            class="form-control" 
                                            id="name"
                                            required
                                            :class="{ 'is-invalid': form.errors.name }"
                                        />
                                        <div v-if="form.errors.name" class="invalid-feedback">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>
                                </BCol>
                                <BCol md="6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label fw-semibold">رقم الهاتف *</label>
                                        <input 
                                            v-model="form.phone" 
                                            type="text" 
                                            class="form-control" 
                                            id="phone"
                                            required
                                            :class="{ 'is-invalid': form.errors.phone }"
                                        />
                                        <div v-if="form.errors.phone" class="invalid-feedback">
                                            {{ form.errors.phone }}
                                        </div>
                                    </div>
                                </BCol>
                            </BRow>

                            <BRow>
                                <BCol md="12">
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                                        <input 
                                            v-model="form.email" 
                                            type="email" 
                                            class="form-control" 
                                            id="email"
                                            :class="{ 'is-invalid': form.errors.email }"
                                        />
                                        <div v-if="form.errors.email" class="invalid-feedback">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>
                                </BCol>
                            </BRow>

                            <!-- Password Change -->
                            <hr class="my-4">
                            <h5 class="mb-3">تغيير كلمة المرور</h5>
                            
                            <BRow>
                                <BCol md="6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">كلمة المرور الجديدة</label>
                                        <input 
                                            v-model="form.password" 
                                            type="password" 
                                            class="form-control" 
                                            id="password"
                                            :class="{ 'is-invalid': form.errors.password }"
                                        />
                                        <small class="text-muted">اتركه فارغاً للإبقاء على كلمة المرور الحالية</small>
                                        <div v-if="form.errors.password" class="invalid-feedback">
                                            {{ form.errors.password }}
                                        </div>
                                    </div>
                                </BCol>
                                <BCol md="6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label fw-semibold">تأكيد كلمة المرور</label>
                                        <input 
                                            v-model="form.password_confirmation" 
                                            type="password" 
                                            class="form-control" 
                                            id="password_confirmation"
                                            :class="{ 'is-invalid': form.errors.password_confirmation }"
                                        />
                                        <div v-if="form.errors.password_confirmation" class="invalid-feedback">
                                            {{ form.errors.password_confirmation }}
                                        </div>
                                    </div>
                                </BCol>
                            </BRow>

                            <!-- Submit Button -->
                            <div class="mt-4">
                                <BButton 
                                    variant="primary" 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="px-4"
                                >
                                    <span v-if="form.processing">جاري الحفظ...</span>
                                    <span v-else>حفظ التغييرات</span>
                                </BButton>
                            </div>
                        </form>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

