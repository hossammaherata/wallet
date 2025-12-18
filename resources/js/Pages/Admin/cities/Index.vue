<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BContainer, BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';

const { cities, keyword } = usePage().props;

// Form to handle page change and preserve keyword
const form = useForm({ page: 1, keyword: keyword || '' });

// Function to handle page change
const getResourese = (event, page) => {
    form.page = page || form.page;
    form.get(route('admin.cities.index'), {}, {
        preserveState: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Layout>
        <Head title="Cities" />
        <PageHeader title="Cities" pageTitle="Cities Management" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader 
                        title="Cities List"
                        :model="form"
                        SearchButton="true"
                        @getResourese="getResourese"
                    />
                    <BCardBody>
                        <!-- Total count display -->
                        <div class="mb-3">
                            <span class="text-muted">Total Cities: <strong>{{ cities.total }}</strong></span>
                        </div>

                        <div class="table-responsive">
                            <BTable 
                                striped 
                                hover 
                                :items="cities.data" 
                                :fields="[
                                    { key: 'row_number', label: '#', sortable: false },
                                    { key: 'shamel_id', label: 'Shamel ID', sortable: true },
                                    { key: 'name_ar', label: 'Arabic Name', sortable: true },
                                    { key: 'name_heb', label: 'Hebrew Name', sortable: true },
                                    { key: 'created_at', label: 'Created At', sortable: true },
                                    { key: 'actions', label: 'Actions' }
                                ]"
                                class="table-nowrap"
                            >
                                <template #cell(row_number)="{ index }">
                                    {{ (cities.current_page - 1) * cities.per_page + index + 1 }}
                                </template>

                                <template #cell(name_ar)="{ item }">
                                    <span v-if="item.name_ar">{{ item.name_ar }}</span>
                                    <BBadge v-else variant="secondary">Not Set</BBadge>
                                </template>

                                <template #cell(name_heb)="{ item }">
                                    <span v-if="item.name_heb">{{ item.name_heb }}</span>
                                    <BBadge v-else variant="secondary">Not Set</BBadge>
                                </template>

                                <template #cell(created_at)="{ item }">
                                    {{ formatDate(item.created_at) }}
                                </template>

                                <template #cell(actions)="{ item }">
                                    <Link 
                                        :href="route('admin.cities.edit', item.id)" 
                                        class="btn btn-primary btn-sm me-1"
                                    >
                                        Edit
                                    </Link>
                                </template>
                            </BTable>
                        </div>

                        <div v-if="cities.last_page > 1" class="d-flex justify-content-center mt-3">
                            <BPagination 
                                v-model="cities.current_page"
                                :total-rows="cities.total"
                                :per-page="cities.per_page"
                                @page-click="getResourese"
                            />
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
