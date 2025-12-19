<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BBadge, BButton } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const { store, notifications } = usePage().props;

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

// Handle pagination
const getResourese = (event, page) => {
    router.get(route('store.notifications'), { page }, {
        preserveState: true,
    });
};

// Mark notification as read
const markAsRead = (notificationId) => {
    router.post(route('store.notifications.read', notificationId), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Notification will be updated automatically
        }
    });
};

// Mark all as read
const markAllAsRead = () => {
    Swal.fire({
        title: t('t-are-you-sure-mark-all'),
        text: t('t-mark-all-as-read-question'),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: t('t-yes-mark-all-as-read')
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('store.notifications.read-all'), {}, {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: t('t-success'),
                        text: t('t-all-notifications-marked'),
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Layout>
        <Head :title="t('t-notifications')" />
        <PageHeader :title="t('t-notifications')" :pageTitle="t('t-store-dashboard')" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">{{ t('t-notifications') }}</h5>
                            <BButton 
                                v-if="notifications && notifications.data && notifications.data.length > 0"
                                variant="primary" 
                                size="sm"
                                @click="markAllAsRead"
                            >
                                <i class="ri-check-double-line me-1"></i>
                                {{ t('t-mark-all-as-read') }}
                            </BButton>
                        </div>

                        <div v-if="notifications && notifications.data && notifications.data.length > 0">
                            <div class="mb-3">
                                <span class="text-muted">{{ t('t-total-transactions-count') }}: <strong>{{ notifications.total }}</strong></span>
                            </div>

                            <div class="table-responsive">
                                <BTable 
                                    striped 
                                    hover 
                                    :items="notifications.data" 
                                    :fields="[
                                        { key: 'read_status', label: '', sortable: false },
                                        { key: 'title', label: t('t-title'), sortable: false },
                                        { key: 'body', label: t('t-message'), sortable: false },
                                        { key: 'time_ago', label: t('t-time'), sortable: false },
                                        { key: 'actions', label: t('t-actions') }
                                    ]"
                                    class="table-nowrap"
                                >
                                    <template #cell(read_status)="{ item }">
                                        <span v-if="!item.read" class="badge bg-primary rounded-circle" style="width: 10px; height: 10px;"></span>
                                        <span v-else class="text-muted">✓</span>
                                    </template>

                                    <template #cell(title)="{ item }">
                                        <div>
                                            <strong :class="item.read ? 'text-muted' : ''">{{ item.title }}</strong>
                                        </div>
                                    </template>

                                    <template #cell(body)="{ item }">
                                        <div :class="item.read ? 'text-muted' : ''">
                                            {{ item.body }}
                                        </div>
                                    </template>

                                    <template #cell(time_ago)="{ item }">
                                        <div>
                                            <small class="text-muted">{{ item.time_ago }}</small>
                                            <br>
                                            <small class="text-muted">{{ formatDate(item.created_at) }}</small>
                                        </div>
                                    </template>

                                    <template #cell(actions)="{ item }">
                                        <BButton 
                                            v-if="!item.read"
                                            variant="outline-primary" 
                                            size="sm"
                                            @click="markAsRead(item.id)"
                                        >
                                            <i class="ri-check-line me-1"></i>
                                            {{ t('t-mark-as-read') }}
                                        </BButton>
                                        <span v-else class="text-muted small">{{ t('t-read') }}</span>
                                    </template>
                                </BTable>
                            </div>

                            <div v-if="notifications.last_page > 1" class="d-flex justify-content-center mt-3">
                                <BPagination 
                                    v-model="notifications.current_page"
                                    :total-rows="notifications.total"
                                    :per-page="notifications.per_page"
                                    @page-click="getResourese"
                                />
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <i class="ri-notification-off-line" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-3">{{ t('t-no-notifications-found') }}</p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

<style scoped>
.badge.bg-primary {
    display: inline-block;
}
</style>




