<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import { BRow, BCol, BCard, BCardBody, BTable, BPagination, BBadge, BButton } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';

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
        title: 'Are you sure?',
        text: 'Mark all notifications as read?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark all as read'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('store.notifications.read-all'), {}, {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'All notifications marked as read',
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
        <Head title="Notifications" />
        <PageHeader title="Notifications" pageTitle="Dashboard" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <BCardBody>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title mb-0">Notifications</h5>
                            <BButton 
                                v-if="notifications && notifications.data && notifications.data.length > 0"
                                variant="primary" 
                                size="sm"
                                @click="markAllAsRead"
                            >
                                <i class="ri-check-double-line me-1"></i>
                                Mark All as Read
                            </BButton>
                        </div>

                        <div v-if="notifications && notifications.data && notifications.data.length > 0">
                            <div class="mb-3">
                                <span class="text-muted">Total Notifications: <strong>{{ notifications.total }}</strong></span>
                            </div>

                            <div class="table-responsive">
                                <BTable 
                                    striped 
                                    hover 
                                    :items="notifications.data" 
                                    :fields="[
                                        { key: 'read_status', label: '', sortable: false },
                                        { key: 'title', label: 'Title', sortable: false },
                                        { key: 'body', label: 'Message', sortable: false },
                                        { key: 'time_ago', label: 'Time', sortable: false },
                                        { key: 'actions', label: 'Actions' }
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
                                            Mark as Read
                                        </BButton>
                                        <span v-else class="text-muted small">Read</span>
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
                            <p class="text-muted mt-3">No notifications found</p>
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




