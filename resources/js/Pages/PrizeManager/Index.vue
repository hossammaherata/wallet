<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BBadge, BAlert } from 'bootstrap-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { tickets, today } = usePage().props;

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

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
        <Head :title="t('t-available-prizes-today')" />
        <PageHeader :title="t('t-available-prizes-today')" :pageTitle="t('t-dashboards')" />

        <BRow>
            <BCol lg="12">
                <BAlert variant="info" show class="mb-4">
                    <strong>{{ t('t-note') }}:</strong> {{ t('t-prizes-available-today-only') }} ({{ formatDate(today) }})
                </BAlert>

                <BCard no-body v-if="tickets && tickets.length > 0">
                    <CardHeader :title="t('t-available-prizes-today')" />
                    <BCardBody>
                        <BTable
                            :items="tickets"
                            :fields="[
                                { key: 'prize.name', label: t('t-prize-name') },
                                { key: 'date', label: t('t-ticket-date') },
                                { key: 'total_winners', label: t('t-total-winners-count') },
                                { key: 'current_winners_count', label: t('t-current-winners') },
                                { key: 'remaining_amount', label: t('t-remaining-amount') },
                                { key: 'status', label: t('t-prize-status') },
                                { key: 'actions', label: t('t-actions') }
                            ]"
                            striped
                            hover
                            responsive
                        >
                            <template #cell(date)="{ item }">
                                {{ formatDate(item.date) }}
                            </template>

                            <template #cell(remaining_amount)="{ item }">
                                <strong :class="item.remaining_amount > 0 ? 'text-success' : 'text-danger'">
                                    {{ formatAmount(item.remaining_amount) }} {{ t('t-points') }}
                                </strong>
                            </template>

                            <template #cell(status)="{ item }">
                                <BBadge :variant="getStatusBadge(item.status)">
                                    {{ getStatusText(item.status) }}
                                </BBadge>
                            </template>

                            <template #cell(actions)="{ item }">
                                <Link
                                    :href="route('prize-manager.tickets.show', item.id)"
                                    class="btn btn-sm btn-primary"
                                >
                                    <i class="ri-eye-line me-1"></i>
                                    {{ t('t-view-and-manage') }}
                                </Link>
                            </template>
                        </BTable>
                    </BCardBody>
                </BCard>

                <BCard no-body v-else>
                    <BCardBody>
                        <BAlert variant="warning" show>
                            {{ t('t-no-prizes-available') }}
                        </BAlert>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

