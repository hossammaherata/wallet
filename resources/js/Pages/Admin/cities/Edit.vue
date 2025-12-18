<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3';
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
    city: Object
});

const form = useForm({
    shamel_id: props.city.shamel_id,
    name_ar: props.city.name_ar || '',
    name_heb: props.city.name_heb || '',
});

const Update = () => {
    form.put(route('admin.cities.update', props.city.id), {
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'City updated successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        },
        onError: () => {
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
        <PageHeader title="Edit City" pageTitle="Edit City" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader title="Edit City Information" />
                    <BCardBody>
                        <FormSection @submitted="Update">
                            <template #form>
                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="shamel_id" value="Shamel ID" />
                                            <TextInput 
                                                id="shamel_id" 
                                                v-model="form.shamel_id" 
                                                type="text" 
                                                class="form-control" 
                                                required 
                                                :class="{ 'is-invalid': form.errors.shamel_id }" 
                                            />
                                            <InputError :message="form.errors.shamel_id" />
                                        </div>
                                    </BCol>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="name_ar" value="Arabic Name" />
                                            <TextInput 
                                                id="name_ar" 
                                                v-model="form.name_ar" 
                                                type="text" 
                                                class="form-control" 
                                                :class="{ 'is-invalid': form.errors.name_ar }" 
                                            />
                                            <InputError :message="form.errors.name_ar" />
                                        </div>
                                    </BCol>
                                </BRow>

                                <BRow>
                                    <BCol md="6">
                                        <div class="mb-3">
                                            <InputLabel for="name_heb" value="Hebrew Name" />
                                            <TextInput 
                                                id="name_heb" 
                                                v-model="form.name_heb" 
                                                type="text" 
                                                class="form-control" 
                                                :class="{ 'is-invalid': form.errors.name_heb }" 
                                            />
                                            <InputError :message="form.errors.name_heb" />
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
                                    Update City
                                </BButton>
                                <Link :href="route('admin.cities.index')" class="btn btn-secondary ms-2">
                                    Back to Cities
                                </Link>
                            </template>
                        </FormSection>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
