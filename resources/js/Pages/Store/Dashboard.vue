<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge } from 'bootstrap-vue-next';

const { store, stats, transactions } = usePage().props;

// QR Code URL
const qrCodeUrl = computed(() => {
    return route('store.qr-code');
});

const downloadQrCodeUrl = computed(() => {
    return route('store.qr-code.download');
});

// Format date
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

// Format amount
const formatAmount = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

// Handle pagination
const getResourese = (event, page) => {
    router.get(route('store.dashboard'), { page }, {
        preserveState: true,
    });
};

// Print QR Code
const printQrCode = () => {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Store QR Code</title>
                <style>
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                        font-family: Arial, sans-serif;
                    }
                    .qr-container {
                        text-align: center;
                    }
                    .qr-container h2 {
                        margin-bottom: 20px;
                    }
                    .qr-container img {
                        max-width: 500px;
                        height: auto;
                    }
                </style>
            </head>
            <body>
                <div class="qr-container">
                    <h2>${store.name} - QR Code</h2>
                    <img src="${qrCodeUrl.value}" alt="Store QR Code" />
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.onload = () => {
        printWindow.print();
    };
};
</script>

<template>
    <Layout>
        <Head title="Store Dashboard" />
        <PageHeader title="لوحة تحكم المتجر" pageTitle="Dashboard" />

        <!-- Statistics Cards -->
        <BRow class="mb-4">
            <BCol md="4">
                <BCard class="text-center">
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar-sm mx-auto">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-22">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="mb-1">
                            <span>{{ formatAmount(stats.total_payments) }}</span>
                        </h4>
                        <p class="text-muted mb-0">إجمالي المدفوعات</p>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol md="4">
                <BCard class="text-center">
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar-sm mx-auto">
                                <div class="avatar-title bg-success-subtle text-success rounded-circle fs-22">
                                    <i class="ri-file-list-line"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="mb-1">
                            <span>{{ stats.transaction_count }}</span>
                        </h4>
                        <p class="text-muted mb-0">عدد المعاملات</p>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol md="4">
                <BCard class="text-center">
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="avatar-sm mx-auto">
                                <div class="avatar-title bg-info-subtle text-info rounded-circle fs-22">
                                    <i class="ri-wallet-line"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="mb-1">
                            <span>{{ formatAmount(stats.current_balance) }}</span>
                        </h4>
                        <p class="text-muted mb-0">الرصيد الحالي (دين على الإدارة)</p>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- QR Code Section -->
        <BRow class="mb-4">
            <BCol lg="12">
                <BCard no-body>
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">QR Code للمتجر</h5>
                            <div>
                                <BButton 
                                    variant="primary" 
                                    class="me-2"
                                    :href="downloadQrCodeUrl"
                                    target="_blank"
                                >
                                    <i class="ri-download-line me-1"></i>
                                    تحميل
                                </BButton>
                                <BButton 
                                    variant="info"
                                    @click="printQrCode"
                                >
                                    <i class="ri-printer-line me-1"></i>
                                    طباعة
                                </BButton>
                            </div>
                        </div>
                        <div class="text-center p-4 bg-light rounded">
                            <div class="mb-3">
                                <img 
                                    :src="qrCodeUrl" 
                                    alt="Store QR Code" 
                                    class="img-fluid"
                                    style="max-width: 300px;"
                                />
                            </div>
                            <p class="text-muted mb-0">
                                <strong>{{ store.name }}</strong><br>
                                <small>امسح هذا الرمز للدفع</small>
                            </p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Transactions Section -->
        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">سجل المعاملات</h5>
                        </div>

                        <div v-if="transactions && transactions.data && transactions.data.length > 0">
                            <div class="mb-3">
                                <span class="text-muted">إجمالي المعاملات: <strong>{{ transactions.total }}</strong></span>
                            </div>

                            <div class="table-responsive">
                                <BTable 
                                    striped 
                                    hover 
                                    :items="transactions.data" 
                                    :fields="[
                                        { key: 'row_number', label: '#', sortable: false },
                                        { key: 'customer_name', label: 'الوصف', sortable: false },
                                        { key: 'amount', label: 'المبلغ', sortable: true },
                                        { key: 'reference_id', label: 'رقم المرجع', sortable: false },
                                        { key: 'created_at', label: 'التاريخ', sortable: true }
                                    ]"
                                    class="table-nowrap"
                                >
                                    <template #cell(row_number)="{ index }">
                                        {{ (transactions.current_page - 1) * transactions.per_page + index + 1 }}
                                    </template>

                                    <template #cell(customer_name)="{ item }">
                                        <div>
                                            <span v-if="item.customer_name">{{ item.customer_name }}</span>
                                            <span v-else class="text-muted">Unknown</span>
                                            <br v-if="item.transaction_type_label">
                                            <small class="text-muted" v-if="item.transaction_type_label">
                                                {{ item.transaction_type_label }}
                                            </small>
                                            <br v-if="item.note">
                                            <small class="text-muted" v-if="item.note">
                                                Note: {{ item.note }}
                                            </small>
                                        </div>
                                    </template>

                                    <template #cell(amount)="{ item }">
                                        <span 
                                            class="fw-bold"
                                            :class="item.is_credit ? 'text-success' : 'text-danger'"
                                        >
                                            {{ item.amount_formatted || (item.is_credit ? '+' : '-') + formatAmount(item.amount) }} Points
                                        </span>
                                    </template>

                                    <template #cell(reference_id)="{ item }">
                                        <code>{{ item.reference_id }}</code>
                                    </template>

                                    <template #cell(created_at)="{ item }">
                                        {{ item.date_formatted || formatDate(item.created_at) }}
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
                            <p class="text-muted">لا توجد معاملات</p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

<style scoped>
.avatar-sm {
    width: 3rem;
    height: 3rem;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-primary-subtle {
    background-color: rgba(85, 110, 230, 0.1);
}

.bg-success-subtle {
    background-color: rgba(10, 207, 151, 0.1);
}

.bg-info-subtle {
    background-color: rgba(57, 175, 209, 0.1);
}

.text-primary {
    color: #556ee6 !important;
}

.text-success {
    color: #0acf97 !important;
}

.text-info {
    color: #39afd1 !important;
}
</style>

