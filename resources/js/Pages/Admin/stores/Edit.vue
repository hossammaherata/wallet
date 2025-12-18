<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { BRow, BCol, BCard, BCardBody, BButton } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
    store: Object
});

// Debug: Log store data
console.log('Edit Store Data:', props.store);
console.log('Profile Photo URL:', props.store.profile_photo_url);

const imagePreview = ref(props.store.profile_photo_url || null);

const form = useForm({
    _method: 'PUT',
    name: props.store.name || '',
    email: props.store.email || '',
    phone: props.store.phone || '',
    status: props.store.status || 'active',
    password: '',
    password_confirmation: '',
    photo: null,
});

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    // Ensure photo file is properly set
    const photoInput = document.getElementById('photo');
    if (photoInput && photoInput.files && photoInput.files[0]) {
        form.photo = photoInput.files[0];
    }
    
    form.post(route('admin.stores.update', props.store.id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Store updated successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        },
        onError: (errors) => {
            console.log('Errors:', errors);
            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
};
</script>

<template>
    <Layout>
        <PageHeader title="Edit Store" pageTitle="Edit Store Information" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader title="Edit Store Information" />
                    <BCardBody>
                        <FormSection @submitted="submit">
                            <template #form>
                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="name" value="Store Name *" />
                                            <TextInput 
                                                id="name" 
                                                v-model="form.name" 
                                                type="text" 
                                                class="form-control" 
                                                required 
                                                :class="{ 'is-invalid': form.errors.name }" 
                                            />
                                            <InputError :message="form.errors.name" />
                                        </div>
                                    </BCol>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="phone" value="Phone *" />
                                            <TextInput 
                                                id="phone" 
                                                v-model="form.phone" 
                                                type="text" 
                                                class="form-control" 
                                                required 
                                                :class="{ 'is-invalid': form.errors.phone }" 
                                            />
                                            <InputError :message="form.errors.phone" />
                                        </div>
                                    </BCol>
                                </BRow>

                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="email" value="Email" />
                                            <TextInput 
                                                id="email" 
                                                v-model="form.email" 
                                                type="email" 
                                                class="form-control" 
                                                :class="{ 'is-invalid': form.errors.email }" 
                                            />
                                            <InputError :message="form.errors.email" />
                                        </div>
                                    </BCol>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel value="Status *" />
                                            <div class="d-flex gap-4 mt-2">
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="radio" 
                                                        name="status" 
                                                        id="status_active" 
                                                        value="active" 
                                                        v-model="form.status"
                                                        :class="{ 'is-invalid': form.errors.status }"
                                                    />
                                                    <label class="form-check-label" for="status_active">
                                                        Active
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="radio" 
                                                        name="status" 
                                                        id="status_suspended" 
                                                        value="suspended" 
                                                        v-model="form.status"
                                                        :class="{ 'is-invalid': form.errors.status }"
                                                    />
                                                    <label class="form-check-label" for="status_suspended">
                                                        Suspended
                                                    </label>
                                                </div>
                                            </div>
                                            <InputError :message="form.errors.status" />
                                        </div>
                                    </BCol>
                                </BRow>

                                <BRow>
                                    <BCol md="12">
                                        <div class="mb-3">
                                            <InputLabel for="photo" value="Profile Photo" />
                                            <input 
                                                type="file" 
                                                id="photo"
                                                @change="handleImageChange"
                                                accept="image/*"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.photo }"
                                            />
                                            <small class="text-muted">Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                                            <InputError :message="form.errors.photo" />
                                            
                                            <!-- Image Preview -->
                                            <div v-if="imagePreview" class="mt-3">
                                                <img :src="imagePreview" alt="Profile Photo" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                            </div>
                                        </div>
                                    </BCol>
                                </BRow>

                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="password" value="Password (Leave blank to keep current)" />
                                            <TextInput 
                                                id="password" 
                                                v-model="form.password" 
                                                type="password" 
                                                class="form-control" 
                                                :class="{ 'is-invalid': form.errors.password }" 
                                            />
                                            <InputError :message="form.errors.password" />
                                        </div>
                                    </BCol>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="password_confirmation" value="Confirm Password" />
                                            <TextInput 
                                                id="password_confirmation" 
                                                v-model="form.password_confirmation" 
                                                type="password" 
                                                class="form-control" 
                                                :class="{ 'is-invalid': form.errors.password_confirmation }" 
                                            />
                                            <InputError :message="form.errors.password_confirmation" />
                                        </div>
                                    </BCol>
                                </BRow>

                                <BRow v-if="store.wallet">
                                    <BCol md="12">
                                        <div class="alert alert-info">
                                            <strong>Wallet Balance:</strong> {{ parseFloat(store.wallet.balance || 0).toFixed(2) }} Points
                                        </div>
                                    </BCol>
                                </BRow>
                            </template>

                            <template #actions>
                                <BButton 
                                    variant="success" 
                                    type="submit" 
                                    :class="{ 'opacity-25': form.processing }" 
                                    :disabled="form.processing"
                                >
                                    Update Store
                                </BButton>
                                <Link :href="route('admin.stores.index')" class="btn btn-secondary ms-2">
                                    Back to Stores
                                </Link>
                            </template>
                        </FormSection>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

