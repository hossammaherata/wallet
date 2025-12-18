<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";

import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Swal from 'sweetalert2'; // Import SweetAlert

const { categories } = usePage().props;

const form = useForm({
    id: '',
    name: '',
    priority: '',
    category_id: '', // Add category_id to the form
});

const Store = () => {
    form.post(route('admin.brand.store'), {
        errorBag: 'Store',
        preserveScroll: true,
        onSuccess: () => {
            form.reset(),
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Brand Created successfully.',
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
        <PageHeader title="Create Brand" pageTitle="All Brands" :href="route('admin.brand.index')" />
        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader title="Create Brand" />

                    <FormSection @submitted="Store">
                        <template #form>
                            <div class="mb-3">
                                <InputLabel for="id" value="id" />
                                <TextInput
                                    id="id"
                                    v-model="form.id"
                                    type="number"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.id }"
                                />
                                <InputError :message="form.errors.id" class="mt-2" />
                            </div>


                            <div class="mb-3">
                                <InputLabel for="name" value="Name" />
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
                                <InputLabel for="priority" value="Priority" />
                                <TextInput
                                    id="priority"
                                    v-model="form.priority"
                                    type="number"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.priority }"
                                />
                                <InputError :message="form.errors.priority" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel for="category_id" value="Category" />
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="form-select mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.category_id }"
                                >
                                    <option value="" disabled>Select a category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.category_id" class="mt-2" />
                            </div>
                        </template>

                        <template #actions>
                            <BButton variant="primary w-30" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save</BButton>
                            <p v-if="form.recentlySuccessful" class="alert alert-success mt-3">Brand Created successfully.</p>
                        </template>
                    </FormSection>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
