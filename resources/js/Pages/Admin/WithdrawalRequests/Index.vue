<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BContainer, BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge, BFormSelect, BFormInput } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { withdrawalRequests, status, keyword } = usePage().props;

// Initialize form with filters
const getInitialStatus = () => {
    if (!status || status === '0' || status === 0 || status === '') {
        return 'all';
    }
    return status;
};

const form = useForm({ 
    page: 1,
    status: getInitialStatus(),
    keyword: keyword || ''
});

const getResources = (event, page) => {
    form.page = page || form.page;
    const params = {};
    if (form.status && form.status !== 'all') {
        params.status = form.status;
    }
    if (form.keyword) {
        params.keyword = form.keyword;
    }
    form.get(route('admin.withdrawal-requests.index'), params, {
        preserveState: true,
    });
};

// Apply filters
const applyFilters = () => {
    form.page = 1;
    const params = {};
    if (form.status && form.status !== 'all') {
        params.status = form.status;
    }
    if (form.keyword) {
        params.keyword = form.keyword;
    }
    form.get(route('admin.withdrawal-requests.index'), params, {
        preserveState: true,
    });
};

// Clear filters
const clearFilters = () => {
    form.status = 'all';
    form.keyword = '';
    form.page = 1;
    form.get(route('admin.withdrawal-requests.index'), {}, {
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

const approveRequest = (requestId) => {
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
        cancelButtonText: t('t-cancel') || 'إلغاء',
        preConfirm: () => {
            return {
                admin_notes: document.getElementById('admin-notes').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.withdrawal-requests.approve', requestId), {
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
                        router.reload({ only: ['withdrawalRequests', 'status', 'keyword'] });
                    });
                },
                onError: (errors) => {
                    Swal.fire(t('t-error'), errors.message || t('t-an-error-occurred'), 'error');
                }
            });
        }
    });
};

const rejectRequest = (requestId) => {
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
        cancelButtonText: t('t-cancel') || 'إلغاء',
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
            router.post(route('admin.withdrawal-requests.reject', requestId), {
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
                        router.reload({ only: ['withdrawalRequests', 'status', 'keyword'] });
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
        <Head :title="t('t-withdrawal-requests')" />
        
        <PageHeader :title="t('t-withdrawal-requests')" :pageTitle="t('t-dashboard')">
            <template #actions>
                <Link :href="route('admin.users.index')" class="btn btn-outline-primary">
                    {{ t('t-back') }}
                </Link>
            </template>
        </PageHeader>

        <BContainer fluid>
            <BRow>
                <BCol lg="12">
                    <BCard no-body>
                        <CardHeader :title="t('t-withdrawal-requests')" />
                        <BCardBody>
                            <!-- Filters -->
                            <div class="mb-4">
                                <BRow>
                                    <BCol md="4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ t('t-search-by-name') }}</label>
                                            <BFormInput 
                                                v-model="form.keyword" 
                                                type="text" 
                                                :placeholder="t('t-reference-id-name-phone')"
                                                @keyup.enter="applyFilters"
                                            />
                                        </div>
                                    </BCol>
                                    <BCol md="3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ t('t-filter-by-status') }}</label>
                                            <BFormSelect v-model="form.status">
                                                <option value="all">{{ t('t-all') }}</option>
                                                <option value="pending">{{ t('t-pending') }}</option>
                                                <option value="approved">{{ t('t-approved') }}</option>
                                                <option value="rejected">{{ t('t-rejected') }}</option>
                                                <option value="cancelled">{{ t('t-cancelled') }}</option>
                                            </BFormSelect>
                                        </div>
                                    </BCol>
                                    <BCol md="5" class="d-flex align-items-end">
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

                            <div v-if="withdrawalRequests && withdrawalRequests.data && withdrawalRequests.data.length > 0">
                                <div class="mb-3">
                                    <span class="text-muted">{{ t('t-total-requests') }}: <strong>{{ withdrawalRequests.total }}</strong></span>
                                </div>

                                <div class="table-responsive">
                                    <BTable 
                                        striped 
                                        hover 
                                        :items="withdrawalRequests.data" 
                                        :fields="[
                                            { key: 'row_number', label: '#', sortable: false },
                                            { key: 'user', label: t('t-user') || 'المستخدم', sortable: false },
                                            { key: 'bank_account', label: t('t-bank-account'), sortable: false },
                                            { key: 'amount', label: t('t-amount'), sortable: true },
                                            { key: 'fee', label: t('t-fee'), sortable: true },
                                            { key: 'status', label: t('t-status'), sortable: true },
                                            { key: 'created_at', label: t('t-created-at'), sortable: true },
                                            { key: 'admin', label: t('t-admin') || 'من قبل', sortable: false },
                                            { key: 'actions', label: t('t-actions') }
                                        ]"
                                        class="table-nowrap"
                                    >
                                        <template #cell(row_number)="{ index }">
                                            {{ (withdrawalRequests.current_page - 1) * withdrawalRequests.per_page + index + 1 }}
                                        </template>

                                        <template #cell(user)="{ item }">
                                            <div v-if="item.user">
                                                <strong>{{ item.user.name }}</strong><br>
                                                <small class="text-muted">{{ item.user.phone }}</small>
                                            </div>
                                            <span v-else class="text-muted">-</span>
                                        </template>

                                        <template #cell(bank_account)="{ item }">
                                            <div v-if="item.bank_account">
                                                <strong>{{ item.bank_account.bank_name }}</strong><br>
                                                <small class="text-muted">{{ item.bank_account.account_number }}</small><br>
                                                <small class="text-muted">{{ item.bank_account.account_holder_name }}</small>
                                            </div>
                                            <span v-else class="text-muted">-</span>
                                        </template>

                                        <template #cell(amount)="{ item }">
                                            <span class="fw-bold text-primary">
                                                {{ formatAmount(item.amount) }} {{ t('t-points') || 'نقطة' }}
                                            </span>
                                        </template>

                                        <template #cell(fee)="{ item }">
                                            <span v-if="item.fee > 0" class="fw-bold text-danger">
                                                {{ formatAmount(item.fee) }} {{ t('t-points') || 'نقطة' }}
                                            </span>
                                            <span v-else class="text-muted">-</span>
                                        </template>

                                        <template #cell(status)="{ item }">
                                            <BBadge :variant="getStatusBadge(item.status)">
                                                {{ getStatusText(item.status) }}
                                            </BBadge>
                                        </template>

                                        <template #cell(created_at)="{ item }">
                                            {{ formatDate(item.created_at) }}
                                        </template>

                                        <template #cell(admin)="{ item }">
                                            <div v-if="item.admin">
                                                <strong>{{ item.admin.name }}</strong><br>
                                                <small class="text-muted">{{ formatDate(item.approved_at || item.rejected_at) }}</small>
                                            </div>
                                            <span v-else class="text-muted">-</span>
                                        </template>

                                        <template #cell(actions)="{ item }">
                                            <Link 
                                                :href="route('admin.withdrawal-requests.show', item.id)" 
                                                class="btn btn-info btn-sm me-1"
                                            >
                                                {{ t('t-view') }}
                                            </Link>
                                            <BButton 
                                                v-if="item.status === 'pending'"
                                                variant="success" 
                                                size="sm" 
                                                class="me-1"
                                                @click="approveRequest(item.id)"
                                            >
                                                {{ t('t-approve') }}
                                            </BButton>
                                            <BButton 
                                                v-if="item.status === 'pending'"
                                                variant="danger" 
                                                size="sm"
                                                @click="rejectRequest(item.id)"
                                            >
                                                {{ t('t-reject') }}
                                            </BButton>
                                        </template>
                                    </BTable>
                                </div>

                                <div v-if="withdrawalRequests.last_page > 1" class="d-flex justify-content-center mt-3">
                                    <BPagination 
                                        v-model="withdrawalRequests.current_page"
                                        :total-rows="withdrawalRequests.total"
                                        :per-page="withdrawalRequests.per_page"
                                        @page-click="getResources"
                                    />
                                </div>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-muted">{{ t('t-no-withdrawal-requests') }}</p>
                            </div>
                        </BCardBody>
                    </BCard>
                </BCol>
            </BRow>
        </BContainer>
    </Layout>
</template>

