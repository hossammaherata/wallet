<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";

import { ref } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    status: 'active',
});

const Store = () => {
    form.post(route('admin.users.store'), {
        errorBag: 'Store',
        preserveScroll: true,
        onSuccess: () => {
            form.reset(),
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: t('t-success'),
                text: t('t-user-created-successfully'),
            });
        },
        onError: () => {
            // Handle errors if needed
        },
    });
};
</script>

<template>
    <Layout>
        <PageHeader :title="t('t-create-user')" :pageTitle="t('t-all-users')" :href="route('admin.users.index')" />
        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-create-user')" />

                    <FormSection @submitted="Store">
                        <template #form>
                            <div class="mb-3">
                                <InputLabel for="name" :value="t('t-full-name')" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.name }"
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel for="email" :value="t('t-email-address')" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.email }"
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel for="phone" :value="t('t-phone-number')" />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.phone }"
                                />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel :value="t('t-status')" />
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
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>

                        </template>

                        <template #actions>
                            <BButton variant="primary w-30" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">{{ t('t-save') }}</BButton>
                            <p v-if="form.recentlySuccessful" class="alert alert-success mt-3">{{ t('t-user-created-successfully') }}</p>
                        </template>
                    </FormSection>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
