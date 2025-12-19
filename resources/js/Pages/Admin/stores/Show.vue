<script setup>
import { ref } from 'vue';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import { usePage, Link, router, useForm } from '@inertiajs/vue3';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BBadge, BModal, BButton, BFormInput, BFormTextarea } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { store, transactions } = usePage().props;

// Debug: Log store data to console
console.log('Store Data:', store);
console.log('Profile Photo Path:', store.profile_photo_path);
console.log('Profile Photo URL:', store.profile_photo_url);

const showExternalPaymentModal = ref(false);
const externalPaymentForm = useForm({
    amount: '',
    note: ''
});

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

const recordExternalPayment = () => {
    if (!externalPaymentForm.amount || parseFloat(externalPaymentForm.amount) <= 0) {
        Swal.fire({
            icon: 'error',
            title: t('t-error'),
            text: t('t-please-enter-valid-amount')
        });
        return;
    }

    externalPaymentForm.post(route('admin.stores.recordExternalPayment', store.id), {
        preserveState: false,
        preserveScroll: true,
        onSuccess: () => {
            showExternalPaymentModal.value = false;
            externalPaymentForm.reset();
            Swal.fire({
                icon: 'success',
                title: t('t-success'),
                text: t('t-external-payment-recorded'),
                timer: 2000,
                showConfirmButton: false
            });
            router.reload();
        },
        onError: (errors) => {
            Swal.fire({
                icon: 'error',
                title: t('t-error'),
                text: errors.message || errors.amount?.[0] || t('t-an-error-occurred')
            });
        }
    });
};

const getResourese = (event, page) => {
    router.get(route('admin.stores.show', store.id), { page }, {
        preserveState: true,
    });
};

const updateWalletStatus = (event) => {
    const form = event.target;
    const formData = new FormData(form);
    const status = formData.get('status');
    
    router.post(route('admin.stores.updateWalletStatus', store.id), {
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
        <PageHeader :title="t('t-store-details')" :pageTitle="t('t-stores-management')" :href="route('admin.stores.index')">
            <template #actions>
                <Link :href="route('admin.stores.edit', store.id)" class="btn btn-warning me-2">
                    {{ t('t-edit-store') }}
                </Link>
                <Link :href="route('admin.stores.index')" class="btn btn-secondary">
                    {{ t('t-back-to-list') }}
                </Link>
            </template>
        </PageHeader>

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-store-information')" />
                    <BCardBody>
                        <!-- Profile Photo -->
                        <BRow>
                            <BCol md="12">
                                <div class="mb-4 text-center">
                                    <div v-if="store.profile_photo_url" class="mb-3">
                                        <img 
                                            :src="store.profile_photo_url" 
                                            alt="Profile Photo" 
                                            class="img-thumbnail rounded-circle"
                                            style="width: 150px; height: 150px; object-fit: cover;"
                                        />
                                    </div>
                                    <div v-else class="mb-3">
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                            <i class="ri-store-2-line" style="font-size: 3rem; color: #999;"></i>
                                        </div>
                                        <p class="text-muted mt-2 small">{{ t('t-no-profile-photo') }}</p>
                                    </div>
                                </div>
                            </BCol>
                        </BRow>
                        
                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-store-name') }}</label>
                                    <p class="form-control-plaintext">{{ store.name }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-phone') }}</label>
                                    <p class="form-control-plaintext">{{ store.phone || '-' }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-email-address') }}</label>
                                    <p class="form-control-plaintext">{{ store.email || '-' }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-status') }}</label>
                                    <p class="form-control-plaintext">
                                        <BBadge :variant="store.status === 'active' ? 'success' : 'danger'">
                                            {{ store.status || 'N/A' }}
                                        </BBadge>
                                    </p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-created-at') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(store.created_at) }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-last-updated') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(store.updated_at) }}</p>
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
                            <BCol md="4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-current-balance') }}</label>
                                    <p class="form-control-plaintext">
                                        <span class="h4 text-primary fw-bold">
                                            {{ formatAmount(store.wallet?.balance || 0) }} {{ t('t-points') }}
                                        </span>
                                    </p>
                                    <small class="text-muted">{{ t('t-debt-to-administration') }}</small>
                                </div>
                            </BCol>
                            <BCol md="4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-wallet-status') }}</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <BBadge :variant="store.wallet?.status === 'active' ? 'success' : 'danger'">
                                            {{ store.wallet?.status === 'active' ? t('t-active') : (store.wallet?.status === 'locked' ? t('t-locked') : 'N/A') }}
                                        </BBadge>
                                        <form 
                                            v-if="store.wallet" 
                                            @submit.prevent="updateWalletStatus"
                                            class="d-inline"
                                        >
                                            <input type="hidden" name="status" :value="store.wallet.status === 'active' ? 'locked' : 'active'" />
                                            <button 
                                                type="submit" 
                                                class="btn btn-sm"
                                                :class="store.wallet.status === 'active' ? 'btn-warning' : 'btn-success'"
                                            >
                                                <i :class="store.wallet.status === 'active' ? 'ri-lock-line' : 'ri-lock-unlock-line'"></i>
                                                {{ store.wallet.status === 'active' ? t('t-lock-wallet') : t('t-unlock-wallet') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </BCol>
                            <BCol md="4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-record-external-payment') }}</label>
                                    <div>
                                        <button 
                                            type="button" 
                                            class="btn btn-primary"
                                            @click="showExternalPaymentModal = true"
                                        >
                                            <i class="ri-money-dollar-circle-line me-1"></i>
                                            {{ t('t-record-payment') }}
                                        </button>
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
                    <CardHeader :title="t('t-transactions-history')" />
                    <BCardBody>
                        <div v-if="transactions && transactions.data && transactions.data.length > 0">
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
                                        <span v-if="item.type === 'debit' && item.meta && item.meta.payment_type === 'external'">
                                            <BBadge variant="warning">{{ t('t-external-payment') }}</BBadge>
                                        </span>
                                        <span v-else-if="item.from_wallet?.user">
                                            {{ item.from_wallet.user.name }}<br>
                                            <small class="text-muted">{{ item.from_wallet.user.phone }}</small>
                                        </span>
                                        <BBadge v-else variant="secondary">{{ t('t-system') }}</BBadge>
                                    </template>

                                    <template #cell(to_user)="{ item }">
                                        <span v-if="item.type === 'debit' && item.meta && item.meta.payment_type === 'external'" class="text-muted">
                                            -
                                        </span>
                                        <span v-else-if="item.to_wallet?.user">
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
                                            class="btn btn-info btn-sm"
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
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted">{{ t('t-no-transactions-found') }}</p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- External Payment Modal -->
        <BModal 
            v-model="showExternalPaymentModal" 
            :title="t('t-record-external-payment')" 
            hide-footer
            size="md"
        >
            <form @submit.prevent="recordExternalPayment">
                <div class="mb-3">
                    <label class="form-label">{{ t('t-amount') }} <span class="text-danger">*</span></label>
                    <BFormInput
                        v-model="externalPaymentForm.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        :placeholder="t('t-enter-amount')"
                        required
                    />
                    <div v-if="externalPaymentForm.errors.amount" class="text-danger small mt-1">
                        {{ externalPaymentForm.errors.amount }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ t('t-note-optional') }}</label>
                    <BFormTextarea
                        v-model="externalPaymentForm.note"
                        rows="3"
                        :placeholder="t('t-enter-note-about-payment')"
                    />
                </div>

                <div class="mb-3">
                    <small class="text-muted">
                        {{ t('t-current-balance') }}: <strong>{{ formatAmount(store.wallet?.balance || 0) }} {{ t('t-points') }}</strong><br>
                        {{ t('t-after-payment') }}: <strong>{{ formatAmount((store.wallet?.balance || 0) - (parseFloat(externalPaymentForm.amount) || 0)) }} {{ t('t-points') }}</strong>
                    </small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <BButton variant="secondary" @click="showExternalPaymentModal = false">
                        {{ t('t-cancel') }}
                    </BButton>
                    <BButton 
                        variant="primary" 
                        type="submit"
                        :disabled="externalPaymentForm.processing"
                    >
                        <span v-if="externalPaymentForm.processing">{{ t('t-recording') }}</span>
                        <span v-else>{{ t('t-record-payment') }}</span>
                    </BButton>
                </div>
            </form>
        </BModal>
    </Layout>
</template>

