<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BBadge, BButton, BAlert } from 'bootstrap-vue-next';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { ticket, statistics } = usePage().props;

const form = useForm({
    phone: '',
    prize_amount: '',
});

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

const submit = () => {
    form.post(route('prize-manager.tickets.add-winner', ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            Swal.fire({
                icon: 'success',
                title: t('t-success-message'),
                text: t('t-winner-added')
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: t('t-error-message'),
                text: t('t-error-adding-winner')
            });
        },
    });
};
</script>

<template>
    <Layout>
        <Head :title="t('t-ticket-details')" />
        <PageHeader :title="t('t-ticket-details')" :pageTitle="t('t-available-prizes-today')" :href="route('prize-manager.dashboard')" />

        <BRow>
            <BCol lg="12">
                <!-- Prize Information -->
                <BCard no-body class="mb-4">
                    <CardHeader :title="t('t-prize-information')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="4">
                                <strong>{{ t('t-prize-name') }}:</strong> {{ ticket.prize.name }}
                            </BCol>
                            <BCol md="4">
                                <strong>{{ t('t-ticket-date') }}:</strong> {{ formatDate(ticket.date) }}
                            </BCol>
                            <BCol md="4">
                                <strong>{{ t('t-prize-status') }}:</strong>
                                <BBadge :variant="getStatusBadge(ticket.status)" class="ms-2">
                                    {{ getStatusText(ticket.status) }}
                                </BBadge>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>

                <!-- Statistics -->
                <BCard no-body class="mb-4">
                    <CardHeader :title="t('t-statistics')" />
                    <BCardBody>
                        <BRow>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-total-winners') }}</h5>
                                    <h3 class="mb-0">{{ ticket.total_winners }}</h3>
                                </div>
                            </BCol>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-current-winners-count') }}</h5>
                                    <h3 class="mb-0">{{ statistics.current_winners_count }}</h3>
                                </div>
                            </BCol>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-total-amount') }}</h5>
                                    <h3 class="mb-0">{{ formatAmount(ticket.total_amount) }} {{ t('t-points') }}</h3>
                                </div>
                            </BCol>
                            <BCol md="3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h5 class="text-muted mb-1">{{ t('t-remaining-amount') }}</h5>
                                    <h3 class="mb-0" :class="statistics.remaining_amount > 0 ? 'text-success' : 'text-danger'">
                                        {{ formatAmount(statistics.remaining_amount) }} {{ t('t-points') }}
                                    </h3>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>

                <!-- Add Winner Form -->
                <BCard no-body class="mb-4" v-if="ticket.status === 'active' && statistics.remaining_winners > 0 && statistics.remaining_amount > 0">
                    <CardHeader :title="t('t-add-winner')" />
                    <BCardBody>
                        <BAlert variant="info" show class="mb-3">
                            <strong>{{ t('t-note') }}:</strong> {{ t('t-add-winner-note') }}
                            {{ t('t-remaining-amount') }}: <strong>{{ formatAmount(statistics.remaining_amount) }} {{ t('t-points') }}</strong>
                            | {{ t('t-remaining-winners') }}: <strong>{{ statistics.remaining_winners }}</strong>
                        </BAlert>

                        <FormSection @submitted="submit">
                            <template #form>
                                <div class="mb-3">
                                    <InputLabel for="phone" :value="t('t-phone-number')" />
                                    <TextInput
                                        id="phone"
                                        v-model="form.phone"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :class="{ 'is-invalid': form.errors.phone }"
                                        :placeholder="t('t-example') + ': 0501234567'"
                                    />
                                    <InputError :message="form.errors.phone" class="mt-2" />
                                </div>

                                <div class="mb-3">
                                    <InputLabel for="prize_amount" :value="t('t-prize-amount-value')" />
                                    <TextInput
                                        id="prize_amount"
                                        v-model="form.prize_amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :max="statistics.remaining_amount"
                                        class="mt-1 block w-full"
                                        :class="{ 'is-invalid': form.errors.prize_amount }"
                                        :placeholder="t('t-example') + ': 100'"
                                    />
                                    <InputError :message="form.errors.prize_amount" class="mt-2" />
                                    <small class="text-muted">
                                        {{ t('t-max-amount') }}: {{ formatAmount(statistics.remaining_amount) }} {{ t('t-points') }}
                                    </small>
                                </div>
                            </template>

                            <template #actions>
                                <BButton variant="primary" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ t('t-add-winner') }}
                                </BButton>
                            </template>
                        </FormSection>
                    </BCardBody>
                </BCard>

                <!-- Winners List -->
                <BCard no-body class="mb-4" v-if="ticket.winners && ticket.winners.length > 0">
                    <CardHeader :title="t('t-winners-list')" />
                    <BCardBody>
                        <BTable
                            :items="ticket.winners"
                            :fields="[
                                { key: 'phone', label: t('t-winner-phone') },
                                { key: 'user.name', label: t('t-winner-name') },
                                { key: 'prize_amount', label: t('t-prize-amount') },
                                { key: 'transaction_id', label: t('t-transaction-number') },
                                { key: 'status', label: t('t-prize-status') },
                                { key: 'created_at', label: t('t-added-date') }
                            ]"
                            striped
                            hover
                            responsive
                        >
                            <template #cell(prize_amount)="{ item }">
                                <strong class="text-success">{{ formatAmount(item.prize_amount) }} {{ t('t-points') }}</strong>
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
                    </BCardBody>
                </BCard>

                <div class="mt-3">
                    <Link :href="route('prize-manager.dashboard')" class="btn btn-secondary">
                        {{ t('t-back-to-list') }}
                    </Link>
                </div>
            </BCol>
        </BRow>
    </Layout>
</template>

