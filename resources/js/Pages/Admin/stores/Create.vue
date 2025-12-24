<script setup>
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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    status: 'active',
    password: '',
    password_confirmation: '',
    photo: null,
});

const submit = () => {
    form.post(route('admin.stores.store'), {
        onSuccess: () => {
            Swal.fire({
                title: t('t-success'),
                text: t('t-store-created-successfully'),
                icon: 'success',
                confirmButtonText: 'OK'
            });
        },
        onError: () => {
            Swal.fire({
                title: t('t-error'),
                text: t('t-something-went-wrong'),
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
};
</script>

<template>
    <Layout>
        <PageHeader :title="t('t-create-store')" :pageTitle="t('t-create-new-store')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-create-store-information')" />
                    <BCardBody>
                        <FormSection @submitted="submit">
                            <template #form>
                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="name" :value="t('t-store-name-required')" />
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
                                            <InputLabel for="phone" :value="t('t-phone-required')" />
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
                                            <InputLabel for="email" :value="t('t-email-label')" />
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
                                            <InputLabel :value="t('t-status-required')" />
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
                                                        {{ t('t-active') }}
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
                                                        {{ t('t-suspended') }}
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
                                            <InputLabel for="photo" :value="t('t-profile-photo')" />
                                            <input 
                                                type="file" 
                                                id="photo"
                                                @input="form.photo = $event.target.files[0]"
                                                accept="image/*"
                                                class="form-control"
                                                :class="{ 'is-invalid': form.errors.photo }"
                                            />
                                            <small class="text-muted">{{ t('t-max-size') }}</small>
                                            <InputError :message="form.errors.photo" />
                                        </div>
                                    </BCol>
                                </BRow>

                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="password" :value="t('t-password-required')" />
                                            <TextInput 
                                                id="password" 
                                                v-model="form.password" 
                                                type="password" 
                                                class="form-control" 
                                                required 
                                                :class="{ 'is-invalid': form.errors.password }" 
                                            />
                                            <InputError :message="form.errors.password" />
                                        </div>
                                    </BCol>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="password_confirmation" :value="t('t-confirm-password-required')" />
                                            <TextInput 
                                                id="password_confirmation" 
                                                v-model="form.password_confirmation" 
                                                type="password" 
                                                class="form-control" 
                                                required 
                                                :class="{ 'is-invalid': form.errors.password_confirmation }" 
                                            />
                                            <InputError :message="form.errors.password_confirmation" />
                                        </div>
                                    </BCol>
                                </BRow>
                            </template>

                            <template #actions>
                                <BButton 
                                    variant="primary" 
                                    type="submit" 
                                    :class="{ 'opacity-25': form.processing }" 
                                    :disabled="form.processing"
                                >
                                    {{ t('t-create-store-button') }}
                                </BButton>
                                <Link :href="route('admin.stores.index')" class="btn btn-outline-primary ms-2">
                                    {{ t('t-back-to-stores') }}
                                </Link>
                            </template>
                        </FormSection>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

