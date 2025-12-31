<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BCardHeader, BCardTitle, BTable, BBadge, BButton, BFormSelect } from 'bootstrap-vue-next';
import { useI18n } from 'vue-i18n';
import getChartColorsArray from '@/common/getChartColorsArray';

const { t } = useI18n();

const { 
    stats, 
    transactionsByType, 
    transactionsByStatus, 
    withdrawalsByStatus,
    transactionsOverTime,
    usersOverTime,
    recentTransactions,
    recentWithdrawals,
    days 
} = usePage().props;

// Format numbers
const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US').format(num || 0);
};

const formatAmount = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Chart data for Transactions by Type (Pie Chart)
const transactionsByTypeChart = computed(() => {
    const labels = transactionsByType.map(item => {
        const typeNames = {
            'purchase': t('t-purchase') || 'شراء',
            'transfer': t('t-transfer') || 'تحويل',
            'withdrawal': t('t-withdrawal-requests') || 'سحب',
            'credit': t('t-credit') || 'إضافة',
            'debit': t('t-debit') || 'خصم',
            'refund': t('t-refund') || 'استرداد'
        };
        return typeNames[item.type] || item.type;
    });
    
    const series = transactionsByType.map(item => item.count);
    
    return {
        series: series,
        chartOptions: {
            chart: {
                height: 350,
                type: 'pie',
            },
            labels: labels,
            legend: {
                position: 'bottom',
            },
            colors: getChartColorsArray('["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info", "--vz-secondary"]'),
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(1) + '%';
                }
            }
        }
    };
});

// Chart data for Transactions by Status (Donut Chart)
const transactionsByStatusChart = computed(() => {
    const labels = transactionsByStatus.map(s => {
        const statusNames = {
            'success': t('t-success') || 'نجح',
            'pending': t('t-pending') || 'قيد الانتظار',
            'failed': t('t-failed') || 'فشل'
        };
        return statusNames[s.status] || s.status;
    });
    
    const series = transactionsByStatus.map(s => s.count);
    
    return {
        series: series,
        chartOptions: {
            chart: {
                height: 350,
                type: 'donut',
            },
            labels: labels,
            legend: {
                position: 'bottom',
            },
            colors: getChartColorsArray('["--vz-success", "--vz-warning", "--vz-danger"]'),
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(1) + '%';
                }
            }
        }
    };
});

// Chart data for Transactions Over Time (Line Chart)
const transactionsOverTimeChart = computed(() => {
    const dates = transactionsOverTime.map(item => formatDate(item.date));
    const amounts = transactionsOverTime.map(item => parseFloat(item.total_amount));
    const fees = transactionsOverTime.map(item => parseFloat(item.total_fee));
    
    return {
        series: [
            {
                name: t('t-amount') || 'المبلغ',
                data: amounts
            },
            {
                name: t('t-fee') || 'الرسوم',
                data: fees
            }
        ],
        chartOptions: {
            chart: {
                height: 350,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: dates
            },
            colors: getChartColorsArray('["--vz-primary", "--vz-danger"]'),
            legend: {
                position: 'top',
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        }
    };
});

// Chart data for Users Over Time (Area Chart)
const usersOverTimeChart = computed(() => {
    const dates = usersOverTime.map(item => formatDate(item.date));
    const counts = usersOverTime.map(item => item.count);
    
    return {
        series: [{
            name: t('t-users') || 'المستخدمين',
            data: counts
        }],
        chartOptions: {
            chart: {
                height: 350,
                type: 'area',
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: dates
            },
            colors: getChartColorsArray('["--vz-success"]'),
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        }
    };
});

// Chart data for Withdrawals by Status (Bar Chart)
const withdrawalsByStatusChart = computed(() => {
    const labels = withdrawalsByStatus.map(w => {
        const statusNames = {
            'pending': t('t-pending') || 'قيد الانتظار',
            'approved': t('t-approved') || 'موافق عليها',
            'rejected': t('t-rejected') || 'مرفوضة',
            'cancelled': t('t-cancelled') || 'ملغاة'
        };
        return statusNames[w.status] || w.status;
    });
    
    const amounts = withdrawalsByStatus.map(w => parseFloat(w.total_amount));
    const fees = withdrawalsByStatus.map(w => parseFloat(w.total_fee));
    
    return {
        series: [
            {
                name: t('t-amount') || 'المبلغ',
                data: amounts
            },
            {
                name: t('t-fee') || 'الرسوم',
                data: fees
            }
        ],
        chartOptions: {
            chart: {
                height: 350,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: labels
            },
            colors: getChartColorsArray('["--vz-primary", "--vz-danger"]'),
            fill: {
                opacity: 1
            },
            legend: {
                position: 'top',
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        }
    };
});

// Change days filter
const changeDays = (newDays) => {
    router.get(route('admin.dashboard'), { days: newDays }, {
        preserveState: true,
    });
};

// Get status badge
const getStatusBadge = (status) => {
    const badges = {
        'success': 'success',
        'pending': 'warning',
        'failed': 'danger',
        'approved': 'success',
        'rejected': 'danger',
        'cancelled': 'secondary'
    };
    return badges[status] || 'secondary';
};

// Get type badge
const getTypeBadge = (type) => {
    const badges = {
        'purchase': 'primary',
        'transfer': 'info',
        'withdrawal': 'warning',
        'credit': 'success',
        'debit': 'danger',
        'refund': 'secondary'
    };
    return badges[type] || 'secondary';
};
</script>

<template>
    <Layout>
        <Head :title="t('t-dashboards')" />
        <PageHeader :title="t('t-dashboards')" :pageTitle="t('t-dashboards')" />
        
        <!-- Filter -->
        <BRow class="mb-3">
            <BCol md="12" class="text-end">
                <BFormSelect 
                    :model-value="days" 
                    @update:model-value="changeDays"
                    style="width: 200px; display: inline-block;"
                >
                    <option :value="7">آخر 7 أيام</option>
                    <option :value="30">آخر 30 يوم</option>
                    <option :value="60">آخر 60 يوم</option>
                    <option :value="90">آخر 90 يوم</option>
                    <option :value="365">آخر سنة</option>
                </BFormSelect>
            </BCol>
        </BRow>

        <!-- Statistics Cards -->
        <BRow>
            <BCol xl="3" md="6">
                <BCard no-body class="card-animate">
                    <BCardBody>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">{{ t('t-users') }}</p>
                                <h4 class="fs-22 fw-semibold mb-0">
                                    <span class="counter-value">{{ formatNumber(stats.total_users) }}</span>
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-primary-subtle rounded">
                                    <span class="avatar-title bg-primary rounded fs-18">
                                        <i class="ri-user-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <BBadge variant="success" class="me-1">{{ stats.active_users }} {{ t('t-active') }}</BBadge>
                            <BBadge variant="warning" class="me-1">{{ stats.suspended_users }} {{ t('t-suspended') }}</BBadge>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol xl="3" md="6">
                <BCard no-body class="card-animate">
                    <BCardBody>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">{{ t('t-stores') }}</p>
                                <h4 class="fs-22 fw-semibold mb-0">
                                    <span class="counter-value">{{ formatNumber(stats.total_stores) }}</span>
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-success-subtle rounded">
                                    <span class="avatar-title bg-success rounded fs-18">
                                        <i class="ri-store-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <BBadge variant="success">{{ stats.active_stores }} {{ t('t-active') }}</BBadge>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol xl="3" md="6">
                <BCard no-body class="card-animate">
                    <BCardBody>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">{{ t('t-transactions') }}</p>
                                <h4 class="fs-22 fw-semibold mb-0">
                                    <span class="counter-value">{{ formatNumber(stats.total_transactions) }}</span>
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-info-subtle rounded">
                                    <span class="avatar-title bg-info rounded fs-18">
                                        <i class="ri-exchange-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2">{{ formatAmount(stats.total_transactions_amount) }} {{ t('t-points') }}</span>
                            </p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol xl="3" md="6">
                <BCard no-body class="card-animate">
                    <BCardBody>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">{{ t('t-fee') }}</p>
                                <h4 class="fs-22 fw-semibold mb-0">
                                    <span class="counter-value text-danger">{{ formatAmount(stats.total_fees_collected) }}</span>
                                    <span class="text-muted fs-14 ms-1">{{ t('t-points') }}</span>
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-danger-subtle rounded">
                                    <span class="avatar-title bg-danger rounded fs-18">
                                        <i class="ri-money-dollar-circle-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="mb-0 text-muted">
                                <small>{{ t('t-total-fees-collected') || 'إجمالي الرسوم المحصلة' }}</small>
                            </p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Additional Statistics -->
        <BRow>
            <BCol xl="3" md="6">
                <BCard no-body class="card-animate">
                    <BCardBody>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">{{ t('t-withdrawal-requests') }}</p>
                                <h4 class="fs-22 fw-semibold mb-0">
                                    <span class="counter-value">{{ formatNumber(stats.total_withdrawals) }}</span>
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-warning-subtle rounded">
                                    <span class="avatar-title bg-warning rounded fs-18">
                                        <i class="ri-bank-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="mb-0 text-muted">
                                <span class="text-primary">{{ formatAmount(stats.total_withdrawals_amount) }} {{ t('t-points') }}</span>
                                <BBadge variant="warning" class="ms-2">{{ stats.pending_withdrawals }} {{ t('t-pending') }}</BBadge>
                            </p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Charts Row 1 -->
        <BRow>
            <BCol xl="6">
                <BCard no-body>
                    <CardHeader :title="t('t-transactions-by-type') || 'المعاملات حسب النوع'" />
                    <BCardBody>
                        <apexchart 
                            v-if="transactionsByTypeChart.series.length > 0"
                            class="apex-charts" 
                            height="350" 
                            dir="ltr" 
                            :series="transactionsByTypeChart.series"
                            :options="transactionsByTypeChart.chartOptions"
                        ></apexchart>
                        <p v-else class="text-center text-muted">{{ t('t-no-data') || 'لا توجد بيانات' }}</p>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol xl="6">
                <BCard no-body>
                    <CardHeader :title="t('t-transactions-by-status') || 'المعاملات حسب الحالة'" />
                    <BCardBody>
                        <apexchart 
                            v-if="transactionsByStatusChart.series.length > 0"
                            class="apex-charts" 
                            height="350" 
                            dir="ltr" 
                            :series="transactionsByStatusChart.series"
                            :options="transactionsByStatusChart.chartOptions"
                        ></apexchart>
                        <p v-else class="text-center text-muted">{{ t('t-no-data') || 'لا توجد بيانات' }}</p>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Charts Row 2 -->
        <BRow>
            <BCol xl="8">
                <BCard no-body>
                    <CardHeader :title="t('t-transactions-over-time') || 'المعاملات عبر الزمن'" />
                    <BCardBody>
                        <apexchart 
                            v-if="transactionsOverTimeChart.series[0].data.length > 0"
                            class="apex-charts" 
                            height="350" 
                            dir="ltr" 
                            :series="transactionsOverTimeChart.series"
                            :options="transactionsOverTimeChart.chartOptions"
                        ></apexchart>
                        <p v-else class="text-center text-muted">{{ t('t-no-data') || 'لا توجد بيانات' }}</p>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol xl="4">
                <BCard no-body>
                    <CardHeader :title="t('t-new-users') || 'المستخدمين الجدد'" />
                    <BCardBody>
                        <apexchart 
                            v-if="usersOverTimeChart.series[0].data.length > 0"
                            class="apex-charts" 
                            height="350" 
                            dir="ltr" 
                            :series="usersOverTimeChart.series"
                            :options="usersOverTimeChart.chartOptions"
                        ></apexchart>
                        <p v-else class="text-center text-muted">{{ t('t-no-data') || 'لا توجد بيانات' }}</p>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Charts Row 3 -->
        <BRow>
            <BCol xl="12">
                <BCard no-body>
                    <CardHeader :title="t('t-withdrawals-by-status') || 'طلبات السحب حسب الحالة'" />
                    <BCardBody>
                        <apexchart 
                            v-if="withdrawalsByStatusChart.series[0].data.length > 0"
                            class="apex-charts" 
                            height="350" 
                            dir="ltr" 
                            :series="withdrawalsByStatusChart.series"
                            :options="withdrawalsByStatusChart.chartOptions"
                        ></apexchart>
                        <p v-else class="text-center text-muted">{{ t('t-no-data') || 'لا توجد بيانات' }}</p>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Recent Transactions and Withdrawals -->
        <BRow>
            <BCol xl="6">
                <BCard no-body>
                    <CardHeader :title="t('t-recent-transactions') || 'آخر المعاملات'" />
                    <BCardBody>
                        <div class="table-responsive">
                            <BTable 
                                striped 
                                hover 
                                :items="recentTransactions" 
                                :fields="[
                                    { key: 'reference_id', label: t('t-reference-id') },
                                    { key: 'type', label: t('t-type') },
                                    { key: 'amount', label: t('t-amount') },
                                    { key: 'fee', label: t('t-fee') },
                                    { key: 'created_at', label: t('t-date') }
                                ]"
                                class="table-nowrap"
                            >
                                <template #cell(reference_id)="{ item }">
                                    <code class="small">{{ item.reference_id.substring(0, 8) }}...</code>
                                </template>
                                <template #cell(type)="{ item }">
                                    <BBadge :variant="getTypeBadge(item.type)">
                                        {{ item.type }}
                                    </BBadge>
                                </template>
                                <template #cell(amount)="{ item }">
                                    <strong>{{ formatAmount(item.amount) }} {{ t('t-points') }}</strong>
                                </template>
                                <template #cell(fee)="{ item }">
                                    <span v-if="item.fee > 0" class="text-danger">{{ formatAmount(item.fee) }} {{ t('t-points') }}</span>
                                    <span v-else class="text-muted">-</span>
                                </template>
                                <template #cell(created_at)="{ item }">
                                    {{ formatDate(item.created_at) }}
                                </template>
                            </BTable>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol xl="6">
                <BCard no-body>
                    <CardHeader :title="t('t-recent-withdrawals') || 'آخر طلبات السحب'" />
                    <BCardBody>
                        <div class="table-responsive">
                            <BTable 
                                striped 
                                hover 
                                :items="recentWithdrawals" 
                                :fields="[
                                    { key: 'user_name', label: t('t-user') },
                                    { key: 'amount', label: t('t-amount') },
                                    { key: 'fee', label: t('t-fee') },
                                    { key: 'status', label: t('t-status') },
                                    { key: 'created_at', label: t('t-date') }
                                ]"
                                class="table-nowrap"
                            >
                                <template #cell(user_name)="{ item }">
                                    <strong>{{ item.user_name }}</strong>
                                </template>
                                <template #cell(amount)="{ item }">
                                    <strong>{{ formatAmount(item.amount) }} {{ t('t-points') }}</strong>
                                </template>
                                <template #cell(fee)="{ item }">
                                    <span v-if="item.fee > 0" class="text-danger">{{ formatAmount(item.fee) }} {{ t('t-points') }}</span>
                                    <span v-else class="text-muted">-</span>
                                </template>
                                <template #cell(status)="{ item }">
                                    <BBadge :variant="getStatusBadge(item.status)">
                                        {{ item.status }}
                                    </BBadge>
                                </template>
                                <template #cell(created_at)="{ item }">
                                    {{ formatDate(item.created_at) }}
                                </template>
                            </BTable>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

<style scoped>
.card-animate {
    transition: transform 0.2s;
}

.card-animate:hover {
    transform: translateY(-5px);
}

.counter-value {
    font-weight: 600;
}
</style>

