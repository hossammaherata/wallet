<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import { usePage, Link, router } from '@inertiajs/vue3';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BBadge } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { user, transactions } = usePage().props;

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

const formatAmount = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const getStatusBadge = (status) => {
    const badges = {
        'success': 'success',
        'pending': 'warning',
        'failed': 'danger'
    };
    return badges[status] || 'secondary';
};

const getTypeBadge = (type) => {
    const badges = {
        'purchase': 'primary',
        'transfer': 'info',
        'credit': 'success',
        'debit': 'danger',
        'refund': 'warning'
    };
    return badges[type] || 'secondary';
};

const getResourese = (event, page) => {
    router.get(route('admin.users.show', user.id), { page }, {
        preserveState: true,
    });
};

const updateWalletStatus = (event) => {
    const form = event.target;
    const formData = new FormData(form);
    const status = formData.get('status');
    
    router.post(route('admin.users.updateWalletStatus', user.id), {
        status: status
    }, {
        preserveState: false,
        preserveScroll: true,
        onSuccess: (page) => {
            // Show success message
            const statusText = status === 'locked' ? t('t-locked') : t('t-active');
            Swal.fire({
                icon: 'success',
                title: t('t-success'),
                text: `${t('t-wallet-status-updated')} ${statusText}`,
                timer: 2000,
                showConfirmButton: false
            });
            
            // Reload the page completely to update wallet status
            router.reload();
        },
        onError: (errors) => {
            Swal.fire({
                icon: 'error',
                title: t('t-error'),
                text: errors.message || t('t-an-error-occurred'),
            });
        }
    });
};
</script>

<template>
    <Layout>
        <PageHeader :title="t('t-user-details')" :pageTitle="t('t-all-users')" :href="route('admin.users.index')">
            <template #actions>
                <Link :href="route('admin.users.edit', user.id)" class="btn btn-primary me-2">
                    {{ t('t-edit-user') }}
                </Link>
                <Link :href="route('admin.users.index')" class="btn btn-outline-primary">
                    {{ t('t-back-to-list') }}
                </Link>
            </template>
        </PageHeader>

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-user-information')" />

                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-full-name') }}</label>
                                    <p class="form-control-plaintext">{{ user.name }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-username') }}</label>
                                    <p class="form-control-plaintext">{{ user.user_name }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-email-address') }}</label>
                                    <p class="form-control-plaintext">{{ user.email }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-user-type') }}</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-success">{{ user.type }}</span>
                                    </p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-created-at') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-last-updated') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(user.updated_at) }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow v-if="user.email_verified_at">
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-email-verified-at') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(user.email_verified_at) }}</p>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Wallet Information -->
        <BRow class="mt-4">
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-wallet-information')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-current-balance') }}</label>
                                    <p class="form-control-plaintext">
                                        <span class="h4 text-primary fw-bold">
                                            {{ formatAmount(user.wallet?.balance || 0) }} {{ t('t-points') }}
                                        </span>
                                    </p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-wallet-status') }}</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <BBadge :variant="user.wallet?.status === 'active' ? 'success' : 'danger'">
                                            {{ user.wallet?.status === 'active' ? 'Active' : (user.wallet?.status === 'locked' ? 'Locked' : 'N/A') }}
                                        </BBadge>
                                        <form 
                                            v-if="user.wallet" 
                                            @submit.prevent="updateWalletStatus"
                                            class="d-inline"
                                        >
                                            <input type="hidden" name="status" :value="user.wallet.status === 'active' ? 'locked' : 'active'" />
                                            <button 
                                                type="submit" 
                                                class="btn btn-sm"
                                                :class="user.wallet.status === 'active' ? 'btn-warning' : 'btn-success'"
                                            >
                                                <i :class="user.wallet.status === 'active' ? 'ri-lock-line' : 'ri-lock-unlock-line'"></i>
                                                {{ user.wallet.status === 'active' ? 'Lock Wallet' : 'Unlock Wallet' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Transactions -->
        <BRow class="mt-4">
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader title="Transactions History" />
                    <BCardBody>
                        <div v-if="transactions && transactions.data && transactions.data.length > 0">
                            <div class="mb-3">
                                <span class="text-muted">Total Transactions: <strong>{{ transactions.total }}</strong></span>
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
                                        { key: 'created_at', label: t('t-date'), sortable: true },
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
                                        <BBadge v-else variant="secondary">SYSTEM</BBadge>
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
                                                'text-success': item.is_credit,
                                                'text-danger': item.is_debit
                                            }"
                                        >
                                            <i 
                                                v-if="item.is_credit" 
                                                class="ri-arrow-down-line text-success"
                                            ></i>
                                            <i 
                                                v-else-if="item.is_debit" 
                                                class="ri-arrow-up-line text-danger"
                                            ></i>
                                            {{ item.display_amount || formatAmount(item.amount) }} Points
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
                                            class="btn btn-info btn-sm"
                                        >
                                            View
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
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted">No transactions found</p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
