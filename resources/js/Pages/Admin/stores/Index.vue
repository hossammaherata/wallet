<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import CardHeader from '@/common/card-header.vue';
import { BContainer, BRow, BCol, BCard, BCardBody, BTable, BPagination, BButton, BBadge } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';

const { stores, keyword } = usePage().props;

// Form to handle page change and preserve keyword
const form = useForm({ page: 1, keyword: keyword || '' });

// Function to handle page change
const getResourese = (event, page) => {
    form.page = page || form.page;
    form.get(route('admin.stores.index'), {}, {
        preserveState: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatBalance = (balance) => {
    return parseFloat(balance || 0).toFixed(2);
};

const deleteStore = (storeId) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.stores.destroy', storeId), {
                onSuccess: () => {
                    getResourese();
                    Swal.fire('Deleted!', 'Store has been deleted.', 'success');
                }
            });
        }
    });
};

const updateStatus = (storeId, currentStatus) => {
    const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
    router.post(route('admin.stores.updateStatus', storeId), {
        status: newStatus
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getResourese();
            Swal.fire('Success!', 'Store status updated successfully.', 'success');
        }
    });
};

const updateWalletStatus = (storeId, currentWalletStatus) => {
    const newStatus = currentWalletStatus === 'active' ? 'locked' : 'active';
    router.post(route('admin.stores.updateWalletStatus', storeId), {
        status: newStatus
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            getResourese();
            const statusText = newStatus === 'locked' ? 'Locked' : 'Active';
            Swal.fire('Success!', `Wallet status updated to: ${statusText}`, 'success');
        }
    });
};

const resetSearch = () => {
    form.keyword = '';
    form.page = 1;
    form.get(route('admin.stores.index'), {}, {
        preserveState: true,
    });
};
</script>

<template>
    <Layout>
        <Head title="Stores" />
        <PageHeader title="Stores" pageTitle="Stores Management" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader 
                        title="Stores List"
                        :model="form"
                        SearchButton="true"
                        @getResourese="getResourese"
                        @reset="resetSearch"
                    >
                        <template #actions>
                            <Link :href="route('admin.stores.create')" class="btn btn-primary">
                                <i class="ri-add-line align-middle me-1"></i> Add New Store
                            </Link>
                        </template>
                    </CardHeader>
                    <BCardBody>
                        <!-- Total count display -->
                        <div class="mb-3">
                            <span class="text-muted">Total Stores: <strong>{{ stores.total }}</strong></span>
                        </div>

                        <div class="table-responsive">
                            <BTable 
                                striped 
                                hover 
                                :items="stores.data" 
                                :fields="[
                                    { key: 'row_number', label: '#', sortable: false },
                                    { key: 'name', label: 'Store Name', sortable: true },
                                    { key: 'phone', label: 'Phone', sortable: true },
                                    { key: 'email', label: 'Email', sortable: true },
                                    { key: 'balance', label: 'Balance', sortable: true },
                                    { key: 'status', label: 'Status', sortable: true },
                                    { key: 'wallet_status', label: 'Wallet Status', sortable: true },
                                    { key: 'created_at', label: 'Created At', sortable: true },
                                    { key: 'actions', label: 'Actions' }
                                ]"
                                class="table-nowrap"
                            >
                                <template #cell(row_number)="{ index }">
                                    {{ (stores.current_page - 1) * stores.per_page + index + 1 }}
                                </template>

                                <template #cell(balance)="{ item }">
                                    <span class="fw-bold">{{ formatBalance(item.wallet?.balance) }} Points</span>
                                </template>

                                <template #cell(status)="{ item }">
                                    <BBadge :variant="item.status === 'active' ? 'success' : 'danger'">
                                        {{ item.status === 'active' ? 'Active' : 'Suspended' }}
                                    </BBadge>
                                </template>

                                <template #cell(wallet_status)="{ item }">
                                    <BBadge v-if="item.wallet" :variant="item.wallet.status === 'active' ? 'success' : 'danger'">
                                        {{ item.wallet.status === 'active' ? 'Active' : 'Locked' }}
                                    </BBadge>
                                    <span v-else class="text-muted">-</span>
                                </template>

                                <template #cell(created_at)="{ item }">
                                    {{ formatDate(item.created_at) }}
                                </template>

                                <template #cell(actions)="{ item }">
                                    <Link 
                                        :href="route('admin.stores.show', item.id)" 
                                        class="btn btn-info btn-sm me-1"
                                    >
                                        View
                                    </Link>
                                    <BButton 
                                        variant="warning" 
                                        size="sm" 
                                        class="me-1"
                                        @click="updateStatus(item.id, item.status)"
                                    >
                                        {{ item.status === 'active' ? 'Suspend' : 'Activate' }}
                                    </BButton>
                                    <BButton 
                                        v-if="item.wallet"
                                        :variant="item.wallet.status === 'active' ? 'warning' : 'success'"
                                        size="sm" 
                                        class="me-1"
                                        @click="updateWalletStatus(item.id, item.wallet.status)"
                                    >
                                        <i :class="item.wallet.status === 'active' ? 'ri-lock-line' : 'ri-lock-unlock-line'"></i>
                                        {{ item.wallet.status === 'active' ? 'Lock Wallet' : 'Unlock Wallet' }}
                                    </BButton>
                                    <Link 
                                        :href="route('admin.stores.edit', item.id)" 
                                        class="btn btn-primary btn-sm me-1"
                                    >
                                        Edit
                                    </Link>
                                    <BButton 
                                        variant="danger" 
                                        size="sm"
                                        @click="deleteStore(item.id)"
                                    >
                                        Delete
                                    </BButton>
                                </template>
                            </BTable>
                        </div>

                        <div v-if="stores.last_page > 1" class="d-flex justify-content-center mt-3">
                            <BPagination 
                                v-model="stores.current_page"
                                :total-rows="stores.total"
                                :per-page="stores.per_page"
                                @page-click="getResourese"
                            />
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

