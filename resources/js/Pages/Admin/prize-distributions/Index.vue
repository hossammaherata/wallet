<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge, BFormSelect, BFormInput } from 'bootstrap-vue-next';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { distributions, filters } = usePage().props;

const form = useForm({ 
    page: 1, 
    keyword: filters?.keyword || '',
    event_type: filters?.event_type || '',
    status: filters?.status || '',
    date_from: filters?.date_from || '',
    date_to: filters?.date_to || '',
});

const getResources = (event, page) => {
    form.page = page || form.page;
    form.get(route('admin.prize-distributions.index'), {}, {
        preserveState: true,
    });
};

const applyFilters = () => {
    form.page = 1;
    form.get(route('admin.prize-distributions.index'), {}, {
        preserveState: true,
    });
};

const clearFilters = () => {
    form.keyword = '';
    form.event_type = '';
    form.status = '';
    form.date_from = '';
    form.date_to = '';
    form.page = 1;
    form.get(route('admin.prize-distributions.index'), {}, {
        preserveState: true,
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'completed': 'success',
        'processing': 'warning',
        'failed': 'danger',
        'pending': 'secondary'
    };
    return badges[status] || 'secondary';
};

const getEventTypeBadge = (type) => {
    return type === 'ugc' ? 'info' : 'primary';
};

const getEventTitle = (distribution) => {
    return distribution.event_meta?.title || distribution.event_meta?.title_ar || '-';
};
</script>

<template>
    <Layout>
        <Head :title="t('t-prize-distributions')" />
        <PageHeader :title="t('t-prize-distributions')" :pageTitle="t('t-dashboards')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-prize-distributions-list')" />
                    <BCardBody>
                        <!-- Filters -->
                        <div class="mb-4">
                            <BRow>
                                <BCol md="3">
                                    <label class="form-label">{{ t('t-search') }}</label>
                                    <BFormInput
                                        v-model="form.keyword"
                                        :placeholder="t('t-search-by-event')"
                                        class="mb-2"
                                    />
                                </BCol>
                                <BCol md="2">
                                    <label class="form-label">{{ t('t-event-type') }}</label>
                                    <BFormSelect
                                        v-model="form.event_type"
                                        :options="[
                                            { value: '', text: t('t-all-types') },
                                            { value: 'ugc', text: t('t-ugc') },
                                            { value: 'nomination', text: t('t-nomination') }
                                        ]"
                                        class="mb-2"
                                    />
                                </BCol>
                                <BCol md="2">
                                    <label class="form-label">{{ t('t-status') }}</label>
                                    <BFormSelect
                                        v-model="form.status"
                                        :options="[
                                            { value: '', text: t('t-all-statuses') },
                                            { value: 'completed', text: t('t-completed') },
                                            { value: 'processing', text: t('t-processing') },
                                            { value: 'failed', text: t('t-failed') },
                                            { value: 'pending', text: t('t-pending') }
                                        ]"
                                        class="mb-2"
                                    />
                                </BCol>
                                <BCol md="2">
                                    <label class="form-label">{{ t('t-date-from') }}</label>
                                    <BFormInput
                                        v-model="form.date_from"
                                        type="date"
                                        class="mb-2"
                                    />
                                </BCol>
                                <BCol md="2">
                                    <label class="form-label">{{ t('t-date-to') }}</label>
                                    <BFormInput
                                        v-model="form.date_to"
                                        type="date"
                                        class="mb-2"
                                    />
                                </BCol>
                                <BCol md="1">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <BButton variant="primary" @click="applyFilters" class="mb-2 w-100">
                                        {{ t('t-search') }}
                                    </BButton>
                                </BCol>
                            </BRow>
                            <BRow>
                                <BCol>
                                    <BButton variant="outline-secondary" size="sm" @click="clearFilters">
                                        {{ t('t-clear-filters') }}
                                    </BButton>
                                </BCol>
                            </BRow>
                        </div>

                        <!-- Table -->
                        <BTable
                            :items="distributions.data"
                            :fields="[
                                { key: 'event_id', label: t('t-event-id'), sortable: true },
                                { key: 'event_type', label: t('t-type'), sortable: true },
                                { key: 'title', label: t('t-title') },
                                { key: 'winners_count', label: t('t-winners') },
                                { key: 'processed_count', label: t('t-success') },
                                { key: 'failed_count', label: t('t-failed') },
                                { key: 'status', label: t('t-status') },
                                { key: 'created_at', label: t('t-date'), sortable: true },
                                { key: 'actions', label: t('t-actions') }
                            ]"
                            striped
                            hover
                            responsive
                        >
                            <template #cell(event_type)="{ item }">
                                <BBadge :variant="getEventTypeBadge(item.event_type)">
                                    {{ item.event_type === 'ugc' ? t('t-ugc') : t('t-nomination') }}
                                </BBadge>
                            </template>

                            <template #cell(title)="{ item }">
                                {{ getEventTitle(item) }}
                            </template>

                            <template #cell(status)="{ item }">
                                <BBadge :variant="getStatusBadge(item.status)">
                                    {{ item.status === 'completed' ? t('t-completed') : 
                                       item.status === 'processing' ? t('t-processing') : 
                                       item.status === 'failed' ? t('t-failed') : t('t-pending') }}
                                </BBadge>
                            </template>

                            <template #cell(created_at)="{ item }">
                                {{ formatDate(item.created_at) }}
                            </template>

                            <template #cell(actions)="{ item }">
                                <Link
                                    :href="route('admin.prize-distributions.show', item.id)"
                                    class="btn btn-sm btn-primary"
                                >
                                    {{ t('t-view-details') }}
                                </Link>
                            </template>
                        </BTable>

                        <!-- Pagination -->
                        <div v-if="distributions.last_page > 1" class="mt-3">
                            <BPagination
                                v-model="form.page"
                                :total-rows="distributions.total"
                                :per-page="distributions.per_page"
                                @page-click="getResources"
                            />
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

