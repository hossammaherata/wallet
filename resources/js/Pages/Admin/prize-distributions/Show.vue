<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BBadge, BAlert } from 'bootstrap-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { distribution, winners } = usePage().props;

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
        'failed': 'danger'
    };
    return badges[status] || 'secondary';
};

const getCategoryText = (category) => {
    const categories = {
        'attendance_fan': t('t-attendance-fan'),
        'online_fan': t('t-online-fan'),
        'ugc_creator': t('t-ugc-creator')
    };
    return categories[category] || category;
};

const getEventTitle = () => {
    return distribution.event_meta?.title || distribution.event_meta?.title_ar || '-';
};

const successfulWinners = winners.filter(w => w.status === 'success');
const failedWinners = winners.filter(w => w.status === 'failed');
</script>

<template>
    <Layout>
        <Head :title="t('t-prize-distribution-details')" />
        <PageHeader :title="t('t-prize-distribution-details')" :pageTitle="t('t-prize-distributions')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body class="mb-4">
                    <CardHeader :title="t('t-event-information')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="3">
                                <strong>{{ t('t-event-id') }}:</strong> {{ distribution.event_id }}
                            </BCol>
                            <BCol md="3">
                                <strong>{{ t('t-type') }}:</strong>
                                <BBadge :variant="distribution.event_type === 'ugc' ? 'info' : 'primary'" class="ms-2">
                                    {{ distribution.event_type === 'ugc' ? t('t-ugc') : t('t-nomination') }}
                                </BBadge>
                            </BCol>
                            <BCol md="3">
                                <strong>{{ t('t-status') }}:</strong>
                                <BBadge :variant="distribution.status === 'completed' ? 'success' : 
                                                   distribution.status === 'failed' ? 'danger' : 'warning'" class="ms-2">
                                    {{ distribution.status === 'completed' ? t('t-completed') : 
                                       distribution.status === 'failed' ? t('t-failed') : t('t-processing') }}
                                </BBadge>
                            </BCol>
                            <BCol md="3">
                                <strong>{{ t('t-date') }}:</strong> {{ formatDate(distribution.created_at) }}
                            </BCol>
                        </BRow>
                        <BRow class="mt-3">
                            <BCol md="12">
                                <strong>{{ t('t-title') }}:</strong> {{ getEventTitle() }}
                            </BCol>
                        </BRow>
                        <BRow class="mt-3">
                            <BCol md="4">
                                <strong>{{ t('t-total-winners') }}:</strong> {{ winners.length }}
                            </BCol>
                            <BCol md="4">
                                <strong>{{ t('t-success') }}:</strong> <span class="text-success">{{ distribution.processed_count }}</span>
                            </BCol>
                            <BCol md="4">
                                <strong>{{ t('t-failed') }}:</strong> <span class="text-danger">{{ distribution.failed_count }}</span>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>

                <!-- Successful Winners -->
                <BCard no-body class="mb-4" v-if="successfulWinners.length > 0">
                    <CardHeader :title="t('t-successful-winners')" />
                    <BCardBody>
                        <BTable
                            :items="successfulWinners"
                            :fields="[
                                { key: 'position', label: t('t-position'), sortable: true },
                                { key: 'category', label: t('t-category') },
                                { key: 'user_name', label: t('t-user-name') },
                                { key: 'email', label: t('t-email') },
                                { key: 'phone', label: t('t-phone') },
                                { key: 'points_scored', label: t('t-points-scored') },
                                { key: 'prize_amount', label: t('t-prize-amount') },
                                { key: 'transaction_id', label: t('t-transaction-id') }
                            ]"
                            striped
                            hover
                            responsive
                        >
                            <template #cell(category)="{ item }">
                                {{ getCategoryText(item.category) }}
                            </template>

                            <template #cell(user_name)="{ item }">
                                {{ item.user?.name || '-' }}
                            </template>

                            <template #cell(prize_amount)="{ item }">
                                <strong class="text-success">{{ formatAmount(item.prize_amount) }} {{ t('t-points') }}</strong>
                            </template>

                            <template #cell(transaction_id)="{ item }">
                                <Link
                                    v-if="item.transaction_id"
                                    :href="route('admin.transactions.show', item.transaction_id)"
                                    class="btn btn-sm btn-link"
                                >
                                    #{{ item.transaction_id }}
                                </Link>
                                <span v-else>-</span>
                            </template>
                        </BTable>
                    </BCardBody>
                </BCard>

                <!-- Failed Winners -->
                <BCard no-body v-if="failedWinners.length > 0">
                    <CardHeader :title="t('t-failed-winners')" />
                    <BCardBody>
                        <BAlert variant="warning" show>
                            <strong>{{ t('t-note') }}:</strong> {{ t('t-failed-winners-note') }}
                        </BAlert>
                        <BTable
                            :items="failedWinners"
                            :fields="[
                                { key: 'position', label: t('t-position') },
                                { key: 'category', label: t('t-category') },
                                { key: 'midan_user_id', label: t('t-midan-user-id') },
                                { key: 'email', label: t('t-email') },
                                { key: 'phone', label: t('t-phone') },
                                { key: 'error_message', label: t('t-failure-reason') }
                            ]"
                            striped
                            hover
                            responsive
                        >
                            <template #cell(category)="{ item }">
                                {{ getCategoryText(item.category) }}
                            </template>

                            <template #cell(error_message)="{ item }">
                                <span class="text-danger">{{ item.error_message || t('t-unknown') }}</span>
                            </template>
                        </BTable>
                    </BCardBody>
                </BCard>

                <div class="mt-3">
                    <Link :href="route('admin.prize-distributions.index')" class="btn btn-secondary">
                        {{ t('t-back-to-list') }}
                    </Link>
                </div>
            </BCol>
        </BRow>
    </Layout>
</template>

