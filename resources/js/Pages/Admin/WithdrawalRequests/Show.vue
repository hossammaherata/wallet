<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import { usePage, Link, router } from '@inertiajs/vue3';
import { BRow, BCol, BCard, BCardBody, BBadge, BButton } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { withdrawalRequest } = usePage().props;

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
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'cancelled': 'secondary'
    };
    return badges[status] || 'secondary';
};

const getStatusText = (status) => {
    const statusMap = {
        'pending': t('t-pending'),
        'approved': t('t-approved'),
        'rejected': t('t-rejected'),
        'cancelled': t('t-cancelled')
    };
    return statusMap[status] || status;
};

const approveRequest = () => {
    Swal.fire({
        title: t('t-approve-request'),
        html: `
            <div class="text-start">
                <label class="form-label">${t('t-admin-notes')} (${t('t-note-optional')})</label>
                <textarea id="admin-notes" class="form-control" rows="3" placeholder="${t('t-note-optional')}"></textarea>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: t('t-approve'),
        cancelButtonText: t('t-cancel'),
        preConfirm: () => {
            return {
                admin_notes: document.getElementById('admin-notes').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.withdrawal-requests.approve', withdrawalRequest.id), {
                admin_notes: result.value.admin_notes
            }, {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: t('t-success'),
                        text: t('t-request-approved'),
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to refresh data
                        router.reload({ only: ['withdrawalRequest'] });
                    });
                },
                onError: (errors) => {
                    Swal.fire(t('t-error'), errors.message || t('t-an-error-occurred'), 'error');
                }
            });
        }
    });
};

const rejectRequest = () => {
    Swal.fire({
        title: t('t-reject-request'),
        html: `
            <div class="text-start">
                <label class="form-label">${t('t-admin-notes')} *</label>
                <textarea id="admin-notes" class="form-control" rows="3" placeholder="${t('t-enter-rejection-reason')}" required></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: t('t-reject'),
        cancelButtonText: t('t-cancel'),
        inputValidator: (value) => {
            if (!document.getElementById('admin-notes').value.trim()) {
                return t('t-notes-required');
            }
        },
        preConfirm: () => {
            const notes = document.getElementById('admin-notes').value.trim();
            if (!notes) {
                Swal.showValidationMessage(t('t-notes-required'));
                return false;
            }
            return {
                admin_notes: notes
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.withdrawal-requests.reject', withdrawalRequest.id), {
                admin_notes: result.value.admin_notes
            }, {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: t('t-success'),
                        text: t('t-request-rejected'),
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to refresh data
                        router.reload({ only: ['withdrawalRequest'] });
                    });
                },
                onError: (errors) => {
                    Swal.fire(t('t-error'), errors.message || t('t-an-error-occurred'), 'error');
                }
            });
        }
    });
};
</script>

<template>
    <Layout>
        <PageHeader :title="t('t-withdrawal-request-details') || 'تفاصيل طلب السحب'" :pageTitle="t('t-withdrawal-requests')" :href="route('admin.withdrawal-requests.index')">
            <template #actions>
                <Link :href="route('admin.withdrawal-requests.index')" class="btn btn-outline-primary">
                    {{ t('t-back-to-list') }}
                </Link>
            </template>
        </PageHeader>

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-withdrawal-request-information') || t('t-user-information')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-user') }}</label>
                                    <p class="form-control-plaintext" v-if="withdrawalRequest.user">
                                        <strong>{{ withdrawalRequest.user.name }}</strong><br>
                                        <small class="text-muted">{{ withdrawalRequest.user.phone }}</small>
                                    </p>
                                    <p v-else class="form-control-plaintext text-muted">-</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-amount') }}</label>
                                    <p class="form-control-plaintext">
                                        <span class="h4 text-primary fw-bold">
                                            {{ formatAmount(withdrawalRequest.amount) }} {{ t('t-points') }}
                                        </span>
                                    </p>
                                </div>
                            </BCol>
                            <BCol md="6" v-if="withdrawalRequest.fee > 0">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-fee') }}</label>
                                    <p class="form-control-plaintext">
                                        <span class="h5 text-danger fw-bold">
                                            {{ formatAmount(withdrawalRequest.fee) }} {{ t('t-points') }}
                                        </span>
                                    </p>
                                </div>
                            </BCol>
                            <BCol md="6" v-if="withdrawalRequest.fee > 0">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-net-amount') }}</label>
                                    <p class="form-control-plaintext">
                                        <span class="h5 text-success fw-bold">
                                            {{ formatAmount(withdrawalRequest.amount - withdrawalRequest.fee) }} {{ t('t-points') }}
                                        </span>
                                    </p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-status') }}</label>
                                    <p class="form-control-plaintext">
                                        <BBadge :variant="getStatusBadge(withdrawalRequest.status)">
                                            {{ getStatusText(withdrawalRequest.status) }}
                                        </BBadge>
                                    </p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-created-at') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(withdrawalRequest.created_at) }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow v-if="withdrawalRequest.approved_at">
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-approved-at') || 'تاريخ الموافقة' }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(withdrawalRequest.approved_at) }}</p>
                                </div>
                            </BCol>
                            <BCol md="6" v-if="withdrawalRequest.admin">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-approved-by') || 'موافق عليه من قبل' }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.admin.name }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow v-if="withdrawalRequest.rejected_at">
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-rejected-at') || 'تاريخ الرفض' }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(withdrawalRequest.rejected_at) }}</p>
                                </div>
                            </BCol>
                            <BCol md="6" v-if="withdrawalRequest.admin">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-rejected-by') || 'مرفوض من قبل' }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.admin.name }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow v-if="withdrawalRequest.admin_notes">
                            <BCol md="12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-admin-notes') }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.admin_notes }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow v-if="withdrawalRequest.status === 'pending'">
                            <BCol md="12">
                                <div class="d-flex gap-2 mt-3">
                                    <BButton variant="success" @click="approveRequest">
                                        {{ t('t-approve') }}
                                    </BButton>
                                    <BButton variant="danger" @click="rejectRequest">
                                        {{ t('t-reject') }}
                                    </BButton>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Bank Account Information -->
        <BRow class="mt-4" v-if="withdrawalRequest.bank_account">
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-bank-account-information') || t('t-bank-account')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-bank-name') }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.bank_account.bank_name }}</p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-account-number') }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.bank_account.account_number }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-account-holder-name') }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.bank_account.account_holder_name }}</p>
                                </div>
                            </BCol>
                            <BCol md="6" v-if="withdrawalRequest.bank_account.iban">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">IBAN</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.bank_account.iban }}</p>
                                </div>
                            </BCol>
                        </BRow>

                        <BRow v-if="withdrawalRequest.bank_account.branch_name">
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-branch-name') }}</label>
                                    <p class="form-control-plaintext">{{ withdrawalRequest.bank_account.branch_name }}</p>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Transaction Information -->
        <BRow class="mt-4" v-if="withdrawalRequest.wallet_transaction">
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-transaction-information')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-reference-id') }}</label>
                                    <p class="form-control-plaintext">
                                        <code>{{ withdrawalRequest.wallet_transaction.reference_id }}</code>
                                    </p>
                                </div>
                            </BCol>
                            <BCol md="6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ t('t-date') }}</label>
                                    <p class="form-control-plaintext">{{ formatDate(withdrawalRequest.wallet_transaction.created_at) }}</p>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

