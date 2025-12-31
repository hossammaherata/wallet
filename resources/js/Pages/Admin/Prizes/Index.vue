<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge, BInputGroup, BFormInput, BInputGroupPrepend } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { prizes, filters } = usePage().props;

const form = useForm({ 
    page: 1, 
    keyword: filters?.keyword || '',
    status: filters?.status || ''
});

const getResources = (event, page) => {
    form.page = page || form.page;
    form.get(route('admin.prizes.index'), {}, {
        preserveState: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatAmount = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const getStatusBadge = (status) => {
    const badges = {
        'active': 'success',
        'completed': 'info',
        'cancelled': 'danger'
    };
    return badges[status] || 'secondary';
};

const getStatusText = (status) => {
    const texts = {
        'active': t('t-active'),
        'completed': t('t-completed'),
        'cancelled': t('t-cancelled')
    };
    return texts[status] || status;
};

const deletePrize = (prizeId) => {
    Swal.fire({
        title: t('t-are-you-sure'),
        text: t('t-wont-be-able-to-revert'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: t('t-yes-delete'),
        cancelButtonText: t('t-cancel')
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.prizes.destroy', prizeId), {
                onSuccess: () => {
                    getResources();
                    Swal.fire(t('t-deleted'), t('t-prize-deleted'), 'success');
                }
            });
        }
    });
};

const search = () => {
    form.page = 1;
    form.get(route('admin.prizes.index'), {}, {
        preserveState: true,
    });
};
</script>

<template>
    <Layout>
        <Head :title="t('t-prizes-management')" />
        <PageHeader :title="t('t-prizes-management')" :pageTitle="t('t-dashboards')" />
        
        <BRow>
            <BCol lg="12">
                <BCard no-body class="mb-4">
                    <CardHeader :title="t('t-prizes-list')">
                        <template #actions>
                            <Link :href="route('admin.prizes.statistics')" class="btn btn-info me-2">
                                <i class="ri-bar-chart-line me-1"></i>
                                {{ t('t-prizes-statistics') }}
                            </Link>
                            <Link :href="route('admin.prizes.create')" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>
                                {{ t('t-add-new-prize') }}
                            </Link>
                        </template>
                    </CardHeader>
                    <BCardBody>
                        <!-- Filters -->
                        <BRow class="mb-3">
                            <BCol md="4">
                                <BInputGroup>
                                    <BFormInput
                                        v-model="form.keyword"
                                        :placeholder="t('t-search-by-name')"
                                        @keyup.enter="search"
                                    />
                                    <BInputGroupPrepend>
                                        <BButton variant="primary" @click="search">
                                            <i class="ri-search-line"></i>
                                        </BButton>
                                    </BInputGroupPrepend>
                                </BInputGroup>
                            </BCol>
                            <BCol md="3">
                                <select v-model="form.status" @change="search" class="form-select">
                                    <option value="">{{ t('t-all-statuses') }}</option>
                                    <option value="active">{{ t('t-active') }}</option>
                                    <option value="completed">{{ t('t-completed') }}</option>
                                    <option value="cancelled">{{ t('t-cancelled') }}</option>
                                </select>
                            </BCol>
                        </BRow>

                        <!-- Table -->
                        <BTable
                            :items="prizes.data"
                            :fields="[
                                { key: 'id', label: 'ID', sortable: true },
                                { key: 'name', label: t('t-prize-name'), sortable: true },
                                { key: 'dates', label: t('t-available-dates') },
                                { key: 'total_winners', label: t('t-total-winners-count') },
                                { key: 'total_amount', label: t('t-total-amount') },
                                { key: 'tickets_count', label: t('t-tickets-count') },
                                { key: 'status', label: t('t-prize-status') },
                                { key: 'created_at', label: t('t-created-at') },
                                { key: 'actions', label: t('t-actions') }
                            ]"
                            striped
                            hover
                            responsive
                        >
                            <template #cell(dates)="{ item }">
                                <span v-for="(date, index) in item.dates" :key="index" class="badge bg-info me-1">
                                    {{ formatDate(date) }}
                                </span>
                            </template>

                            <template #cell(total_amount)="{ item }">
                                <strong>{{ formatAmount(item.total_amount) }} {{ t('t-points') }}</strong>
                            </template>

                            <template #cell(status)="{ item }">
                                <BBadge :variant="getStatusBadge(item.status)">
                                    {{ getStatusText(item.status) }}
                                </BBadge>
                            </template>

                            <template #cell(created_at)="{ item }">
                                {{ formatDate(item.created_at) }}
                            </template>

                            <template #cell(actions)="{ item }">
                                <Link
                                    :href="route('admin.prizes.show', item.id)"
                                    class="btn btn-sm btn-info me-1"
                                >
                                    <i class="ri-eye-line"></i>
                                </Link>
                                <Link
                                    :href="route('admin.prizes.edit', item.id)"
                                    class="btn btn-sm btn-warning me-1"
                                >
                                    <i class="ri-edit-line"></i>
                                </Link>
                                <BButton
                                    variant="danger"
                                    size="sm"
                                    @click="deletePrize(item.id)"
                                >
                                    <i class="ri-delete-bin-line"></i>
                                </BButton>
                            </template>
                        </BTable>

                        <!-- Pagination -->
                        <div v-if="prizes.last_page > 1" class="mt-3">
                            <BPagination
                                v-model="form.page"
                                :total-rows="prizes.total"
                                :per-page="prizes.per_page"
                                @page-click="getResources"
                            />
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

