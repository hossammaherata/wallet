<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BBadge } from 'bootstrap-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { statistics, prizesByStatus, ticketsByStatus } = usePage().props;

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
</script>

<template>
    <Layout>
        <Head :title="t('t-prizes-statistics')" />
        <PageHeader :title="t('t-prizes-statistics')" :pageTitle="t('t-prizes-management')" :href="route('admin.prizes.index')" />

        <BRow>
            <!-- Main Statistics -->
            <BCol lg="12" class="mb-4">
                <BCard no-body>
                    <CardHeader :title="t('t-statistics')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-total-points-added') }}</h5>
                                    <h3 class="mb-0 text-primary">
                                        {{ formatAmount(statistics.total_points_added) }} {{ t('t-points') }}
                                    </h3>
                                </div>
                            </BCol>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-total-points-awarded') }}</h5>
                                    <h3 class="mb-0 text-success">
                                        {{ formatAmount(statistics.total_points_awarded) }} {{ t('t-points') }}
                                    </h3>
                                </div>
                            </BCol>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-remaining-points') }}</h5>
                                    <h3 class="mb-0" :class="statistics.remaining_points > 0 ? 'text-warning' : 'text-danger'">
                                        {{ formatAmount(statistics.remaining_points) }} {{ t('t-points') }}
                                    </h3>
                                </div>
                            </BCol>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-total-winners') }}</h5>
                                    <h3 class="mb-0 text-info">
                                        {{ statistics.total_winners }}
                                    </h3>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>

            <!-- Additional Statistics -->
            <BCol md="6" class="mb-4">
                <BCard no-body>
                    <CardHeader :title="t('t-prizes-by-status')" />
                    <BCardBody>
                        <BTable
                            :items="prizesByStatus"
                            :fields="[
                                { key: 'status', label: t('t-prize-status') },
                                { key: 'count', label: t('t-count') },
                                { key: 'total_amount', label: t('t-total-amount') }
                            ]"
                            striped
                            hover
                        >
                            <template #cell(status)="{ item }">
                                <BBadge :variant="getStatusBadge(item.status)">
                                    {{ getStatusText(item.status) }}
                                </BBadge>
                            </template>

                            <template #cell(total_amount)="{ item }">
                                <strong>{{ formatAmount(item.total_amount) }} {{ t('t-points') }}</strong>
                            </template>
                        </BTable>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol md="6" class="mb-4">
                <BCard no-body>
                    <CardHeader :title="t('t-tickets-by-status')" />
                    <BCardBody>
                        <BTable
                            :items="ticketsByStatus"
                            :fields="[
                                { key: 'status', label: t('t-prize-status') },
                                { key: 'count', label: t('t-count') },
                                { key: 'total_amount', label: t('t-total-amount') }
                            ]"
                            striped
                            hover
                        >
                            <template #cell(status)="{ item }">
                                <BBadge :variant="getStatusBadge(item.status)">
                                    {{ getStatusText(item.status) }}
                                </BBadge>
                            </template>

                            <template #cell(total_amount)="{ item }">
                                <strong>{{ formatAmount(item.total_amount) }} {{ t('t-points') }}</strong>
                            </template>
                        </BTable>
                    </BCardBody>
                </BCard>
            </BCol>

            <!-- Summary -->
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-summary')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="4">
                                <strong>{{ t('t-total-prizes') }}:</strong> {{ statistics.total_prizes }}
                            </BCol>
                            <BCol md="4">
                                <strong>{{ t('t-total-tickets') }}:</strong> {{ statistics.total_tickets }}
                            </BCol>
                            <BCol md="4">
                                <strong>{{ t('t-total-winners') }}:</strong> {{ statistics.total_winners }}
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>
            </BCol>

            <div class="mt-3">
                <Link :href="route('admin.prizes.index')" class="btn btn-secondary">
                    {{ t('t-back-to-list') }}
                </Link>
            </div>
        </BRow>
    </Layout>
</template>

