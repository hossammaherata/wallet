<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BButton, BBadge } from 'bootstrap-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    transaction: Object
});

const formatDate = (date) => {
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
</script>

<template>
    <Layout>
        <PageHeader :title="t('t-transaction-details')" :pageTitle="t('t-transaction-information')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-transaction-details')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="6">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="40%">{{ t('t-reference-id') }}</th>
                                            <td><code>{{ transaction.reference_id }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('t-amount') }}</th>
                                            <td><strong>{{ formatAmount(transaction.amount) }} {{ t('t-points') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('t-type') }}</th>
                                            <td>
                                                <BBadge :variant="getTypeBadge(transaction.type)">
                                                    {{ transaction.type }}
                                                </BBadge>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('t-status') }}</th>
                                            <td>
                                                <BBadge :variant="getStatusBadge(transaction.status)">
                                                    {{ transaction.status }}
                                                </BBadge>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('t-created-at') }}</th>
                                            <td>{{ formatDate(transaction.created_at) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </BCol>
                            <BCol md="6">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="40%">{{ t('t-from') }}</th>
                                            <td>
                                                <span v-if="transaction.from_wallet?.user">
                                                    <strong>{{ transaction.from_wallet.user.name }}</strong><br>
                                                    <small class="text-muted">{{ transaction.from_wallet.user.phone }}</small>
                                                </span>
                                                <BBadge v-else variant="secondary">{{ t('t-system') }}</BBadge>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('t-to') }}</th>
                                            <td>
                                                <span v-if="transaction.to_wallet?.user">
                                                    <strong>{{ transaction.to_wallet.user.name }}</strong><br>
                                                    <small class="text-muted">{{ transaction.to_wallet.user.phone }}</small>
                                                </span>
                                                <span v-else class="text-muted">-</span>
                                            </td>
                                        </tr>
                                        <tr v-if="transaction.meta">
                                            <th>{{ t('t-additional-info') }}</th>
                                            <td>
                                                <pre class="mb-0">{{ JSON.stringify(transaction.meta, null, 2) }}</pre>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </BCol>
                        </BRow>

                        <div class="mt-3">
                            <Link :href="route('admin.transactions.index')" class="btn btn-secondary">
                                {{ t('t-back-to-transactions') }}
                            </Link>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

