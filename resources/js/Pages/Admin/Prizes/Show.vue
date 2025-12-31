<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BBadge } from 'bootstrap-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { prize } = usePage().props;

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
        <Head :title="t('t-prize-details')" />
        <PageHeader :title="t('t-prize-details')" :pageTitle="t('t-prizes-management')" :href="route('admin.prizes.index')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body class="mb-4">
                    <CardHeader :title="t('t-prize-information')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <strong>{{ t('t-prize-name') }}:</strong> {{ prize.name }}
                            </BCol>
                            <BCol md="3">
                                <strong>{{ t('t-total-winners-count') }}:</strong> {{ prize.total_winners }}
                            </BCol>
                            <BCol md="3">
                                <strong>{{ t('t-total-amount') }}:</strong> {{ formatAmount(prize.total_amount) }} {{ t('t-points') }}
                            </BCol>
                        </BRow>
                        <BRow class="mt-3">
                            <BCol md="6">
                                <strong>{{ t('t-prize-status') }}:</strong>
                                <BBadge :variant="getStatusBadge(prize.status)" class="ms-2">
                                    {{ getStatusText(prize.status) }}
                                </BBadge>
                            </BCol>
                            <BCol md="6">
                                <strong>{{ t('t-created-at') }}:</strong> {{ formatDate(prize.created_at) }}
                            </BCol>
                        </BRow>
                        <BRow class="mt-3">
                            <BCol md="12">
                                <strong>{{ t('t-available-dates') }}:</strong>
                                <div class="mt-2">
                                    <span v-for="(date, index) in prize.dates" :key="index" class="badge bg-info me-2">
                                        {{ formatDate(date) }}
                                    </span>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>

                <!-- Tickets -->
                <BCard no-body class="mb-4" v-if="prize.tickets && prize.tickets.length > 0">
                    <CardHeader :title="t('t-tickets')" />
                    <BCardBody>
                        <div v-for="ticket in prize.tickets" :key="ticket.id" class="mb-4">
                            <BCard no-body class="border">
                                <BCardBody>
                                    <BRow class="mb-3">
                                        <BCol md="12">
                                            <h5 class="mb-3">
                                                {{ t('t-ticket-date') }}: {{ formatDate(ticket.date) }}
                                                <BBadge :variant="getStatusBadge(ticket.status)" class="ms-2">
                                                    {{ getStatusText(ticket.status) }}
                                                </BBadge>
                                            </h5>
                                        </BCol>
                                    </BRow>
                                    <BRow class="mb-3">
                                        <BCol md="3">
                                            <strong>{{ t('t-total-winners-count') }}:</strong> {{ ticket.total_winners }}
                                        </BCol>
                                        <BCol md="3">
                                            <strong>{{ t('t-current-winners') }}:</strong> {{ ticket.current_winners_count }}
                                        </BCol>
                                        <BCol md="3">
                                            <strong>{{ t('t-total-amount') }}:</strong> {{ formatAmount(ticket.total_amount) }} {{ t('t-points') }}
                                        </BCol>
                                        <BCol md="3">
                                            <strong>{{ t('t-remaining-amount') }}:</strong>
                                            <span :class="ticket.remaining_amount > 0 ? 'text-success' : 'text-danger'">
                                                {{ formatAmount(ticket.remaining_amount) }} {{ t('t-points') }}
                                            </span>
                                        </BCol>
                                    </BRow>

                                    <!-- Winners for this ticket -->
                                    <div v-if="ticket.winners && ticket.winners.length > 0" class="mt-3">
                                        <h6 class="mb-2">{{ t('t-winners-list') }}:</h6>
                                        <BTable
                                            :items="ticket.winners"
                                            :fields="[
                                                { key: 'phone', label: t('t-winner-phone') },
                                                { key: 'user.name', label: t('t-winner-name') },
                                                { key: 'prize_amount', label: t('t-prize-amount') },
                                                { key: 'added_by.name', label: t('t-added-by') },
                                                { key: 'transaction_id', label: t('t-transaction-number') },
                                                { key: 'status', label: t('t-prize-status') },
                                                { key: 'created_at', label: t('t-added-date') }
                                            ]"
                                            striped
                                            hover
                                            responsive
                                            small
                                        >
                                            <template #cell(prize_amount)="{ item }">
                                                <strong class="text-success">{{ formatAmount(item.prize_amount) }} {{ t('t-points') }}</strong>
                                            </template>

                                            <template #cell(added_by.name)="{ item }">
                                                <span v-if="item.added_by">{{ item.added_by.name }}</span>
                                                <span v-else class="text-muted">-</span>
                                            </template>

                                            <template #cell(transaction_id)="{ item }">
                                                <span v-if="item.transaction_id">#{{ item.transaction_id }}</span>
                                                <span v-else>-</span>
                                            </template>

                                            <template #cell(status)="{ item }">
                                                <BBadge :variant="item.status === 'success' ? 'success' : 'danger'">
                                                    {{ item.status === 'success' ? t('t-success') : t('t-failed') }}
                                                </BBadge>
                                            </template>

                                            <template #cell(created_at)="{ item }">
                                                {{ formatDate(item.created_at) }}
                                            </template>
                                        </BTable>
                                    </div>
                                    <div v-else class="mt-3">
                                        <p class="text-muted">{{ t('t-no-winners-yet') }}</p>
                                    </div>
                                </BCardBody>
                            </BCard>
                        </div>
                    </BCardBody>
                </BCard>

                <div class="mt-3">
                    <Link :href="route('admin.prizes.index')" class="btn btn-secondary">
                        {{ t('t-back-to-list') }}
                    </Link>
                    <Link :href="route('admin.prizes.edit', prize.id)" class="btn btn-warning ms-2">
                        {{ t('t-edit') }}
                    </Link>
                </div>
            </BCol>
        </BRow>
    </Layout>
</template>

