<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BContainer, BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge, BFormSelect } from 'bootstrap-vue-next';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { transactions, filters } = usePage().props;

// Form to handle page change and preserve filters
const form = useForm({ 
    page: 1, 
    keyword: filters?.keyword || '',
    status: filters?.status || '',
    type: filters?.type || '',
    date_from: filters?.date_from || '',
    date_to: filters?.date_to || '',
});

// Function to handle page change
const getResourese = (event, page) => {
    form.page = page || form.page;
    form.get(route('admin.transactions.index'), {}, {
        preserveState: true,
    });
};

// Function to apply filters
const applyFilters = () => {
    form.page = 1;
    form.get(route('admin.transactions.index'), {}, {
        preserveState: true,
    });
};

// Function to clear filters
const clearFilters = () => {
    form.keyword = '';
    form.status = '';
    form.type = '';
    form.date_from = '';
    form.date_to = '';
    form.page = 1;
    form.get(route('admin.transactions.index'), {}, {
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

const formatAmount = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const getStatusBadge = (status) => {
    const badges = {
        'success': 'outline-success',
        'pending': 'outline-warning',
        'failed': 'outline-danger'
    };
    return badges[status] || 'secondary';
};

const getTypeBadge = (type) => {
    const badges = {
        'purchase': 'outline-primary',
        'transfer': 'outline-info',
        'credit': 'outline-success',
        // 'debit': 'danger',
        'refund': 'outline-warning'
    };
    return badges[type] || 'outline-secondary';
};
</script>

<template>
    <Layout>
        <Head :title="t('t-transactions')" />
        <PageHeader :title="t('t-transactions')" :pageTitle="t('t-transactions-management')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-transactions-list')" />
                    <BCardBody>
                        <!-- Filters -->
                        <div class="mb-4">
                            <BRow>
                                <BCol md="3">
                                    <div class="mb-3">
                                        <label class="form-label">{{ t('t-search') }}</label>
                                        <input 
                                            v-model="form.keyword" 
                                            type="text" 
                                            class="form-control" 
                                            :placeholder="t('t-reference-id-name-phone')"
                                        />
                                    </div>
                                </BCol>
                                <BCol md="2">
                                    <div class="mb-3">
                                        <label class="form-label">{{ t('t-status') }}</label>
                                        <BFormSelect v-model="form.status">
                                            <option value="">{{ t('t-all') }}</option>
                                            <option value="success">{{ t('t-success') }}</option>
                                            <option value="pending">{{ t('t-pending') }}</option>
                                            <option value="failed">{{ t('t-failed') }}</option>
                                        </BFormSelect>
                                    </div>
                                </BCol>
                                <BCol md="2">
                                    <div class="mb-3">
                                        <label class="form-label">{{ t('t-type') }}</label>
                                        <BFormSelect v-model="form.type">
                                            <option value="">{{ t('t-all') }}</option>
                                            <option value="purchase">{{ t('t-purchase') }}</option>
                                            <option value="transfer">{{ t('t-transfer') }}</option>
                                            <option value="credit">{{ t('t-credit') }}</option>
                                            <!-- <option value="debit">Debit</option> -->
                                            <!-- <option value="refund">Refund</option> -->
                                        </BFormSelect>
                                    </div>
                                </BCol>
                                <BCol md="2">
                                    <div class="mb-3">
                                        <label class="form-label">{{ t('t-date-from') }}</label>
                                        <input 
                                            v-model="form.date_from" 
                                            type="date" 
                                            class="form-control"
                                        />
                                    </div>
                                </BCol>
                                <BCol md="2">
                                    <div class="mb-3">
                                        <label class="form-label">{{ t('t-date-to') }}</label>
                                        <input 
                                            v-model="form.date_to" 
                                            type="date" 
                                            class="form-control"
                                        />
                                    </div>
                                </BCol>
                            </BRow>
                            <BRow>
                                <BCol md="12">
                                    <div class="mb-3">
                                        <BButton variant="primary" @click="applyFilters" class="me-2">
                                            <i class="ri-search-line me-1"></i> {{ t('t-apply-filters') }}
                                        </BButton>
                                        <BButton variant="outline-primary" @click="clearFilters">
                                            <i class="ri-refresh-line me-1"></i> {{ t('t-reset') }}
                                        </BButton>
                                    </div>
                                </BCol>
                            </BRow>
                        </div>

                        <!-- Total count display -->
                        <div class="mb-3">
                            <span class="text-muted">{{ t('t-total-transactions') }}: <strong>{{ transactions.total }}</strong></span>
                        </div>

                        <div class="table-responsive">
                            <BTable 
                                striped 
                                hover 
                                :items="transactions.data" 
                                :fields="[
                                    { key: 'row_number', label: '#', sortable: false },
                                    { key: 'reference_id', label: t('t-reference-id'), sortable: true },
                                    { key: 'from_user', label: t('t-from'), sortable: false },
                                    { key: 'to_user', label: t('t-to'), sortable: false },
                                    { key: 'amount', label: t('t-amount'), sortable: true },
                                    { key: 'type', label: t('t-type'), sortable: true },
                                    { key: 'status', label: t('t-status'), sortable: true },
                                    { key: 'created_at', label: t('t-created-at'), sortable: true },
                                    { key: 'actions', label: t('t-actions') }
                                ]"
                                class="table-nowrap"
                            >
                                <template #cell(row_number)="{ index }">
                                    {{ (transactions.current_page - 1) * transactions.per_page + index + 1 }}
                                </template>

                                <template #cell(reference_id)="{ item }">
                                    <code>{{ item.reference_id }}</code>
                                </template>

                                <template #cell(from_user)="{ item }">
                                    <span v-if="item.from_wallet?.user">
                                        {{ item.from_wallet.user.name }}<br>
                                        <small class="text-muted">{{ item.from_wallet.user.phone }}</small>
                                    </span>
                                    <BBadge v-else variant="outline-secondary">{{ t('t-system') }}</BBadge>
                                </template>

                                <template #cell(to_user)="{ item }">
                                    <span v-if="item.to_wallet?.user">
                                        {{ item.to_wallet.user.name }}<br>
                                        <small class="text-muted">{{ item.to_wallet.user.phone }}</small>
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </template>

                                <template #cell(amount)="{ item }">
                                    <span 
                                        class="fw-bold" 
                                        :class="{
                                            'text-success': item.is_credit || (item.has_credit && !item.has_debit),
                                            'text-danger': item.is_debit || (item.has_debit && !item.has_credit)
                                        }"
                                    >
                                        <i 
                                            v-if="item.is_credit || (item.has_credit && !item.has_debit)" 
                                            class="ri-arrow-down-line text-success"
                                        ></i>
                                        <i 
                                            v-else-if="item.is_debit || (item.has_debit && !item.has_credit)" 
                                            class="ri-arrow-up-line text-danger"
                                        ></i>
                                        {{ item.display_amount || formatAmount(item.amount) }} {{ t('t-points') }}
                                    </span>
                                </template>

                                <template #cell(type)="{ item }">
                                    <BBadge :variant="getTypeBadge(item.type)">
                                        {{ item.type }}
                                    </BBadge>
                                </template>

                                <template #cell(status)="{ item }">
                                    <BBadge :variant="getStatusBadge(item.status)">
                                        {{ item.status }}
                                    </BBadge>
                                </template>

                                <template #cell(created_at)="{ item }">
                                    {{ formatDate(item.created_at) }}
                                </template>

                                <template #cell(actions)="{ item }">
                                    <Link 
                                        :href="route('admin.transactions.show', item.id)" 
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        {{ t('t-view') }}
                                    </Link>
                                </template>
                            </BTable>
                        </div>

                        <div v-if="transactions.last_page > 1" class="d-flex justify-content-center mt-3">
                            <BPagination 
                                v-model="transactions.current_page"
                                :total-rows="transactions.total"
                                :per-page="transactions.per_page"
                                @page-click="getResourese"
                            />
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

