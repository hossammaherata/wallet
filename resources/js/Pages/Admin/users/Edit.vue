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

// Accept `user` as props, pre-filled with data from the backend
const { user } = usePage().props;

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    status: user.status || 'active',
});

const Update = () => {
    form.put(route('admin.users.update', user.id), {
        errorBag: 'Update',
        preserveScroll: true,
        onSuccess: () => {
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'User updated successfully.',
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
        <PageHeader title="Edit User" pageTitle="All Users" :href="route('admin.users.index')" />
        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader title="Edit User" />

                    <FormSection @submitted="Update">
                        <template #form>
                            <div class="mb-3">
                                <InputLabel for="name" value="Full Name" />
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
                                <InputLabel for="email" value="Email Address" />
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
                                <InputLabel for="phone" value="Phone Number" />
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
                                <InputLabel value="Status" />
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
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>

                        </template>

                        <template #actions>
                            <BButton
                                variant="primary w-30"
                                type="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Update
                            </BButton>
                            <p v-if="form.recentlySuccessful" class="alert alert-success mt-3">User Updated Successfully.</p>
                        </template>
                    </FormSection>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
