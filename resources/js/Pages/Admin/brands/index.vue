<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import Swal from 'sweetalert2'; // Import SweetAlert

const { brands, keyword } = usePage().props;
import { useForm } from '@inertiajs/vue3';

// Form to handle page change and preserve keyword
const form = useForm({ page: 1, keyword: keyword || '' });

// Function to handle page change
const getResourese = (event, page) => {
  form.page = page || form.page;
  form.get(route('admin.brand.index'), {}, {
    preserveState: true,
  });
};

// Function to handle delete with SweetAlert confirmation
const DeleteItem = (id) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#34c38f',
    cancelButtonColor: '#f46a6a',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      form.delete(route('admin.brand.destroy', id), {
        onSuccess: () => {
          Swal.fire(
            'Deleted!',
            'Your Brand has been deleted.',
            'success'
          );
          getResourese(null, brands.current_page); // Refresh after deletion
        },
      });
    }
  });
};
</script>

<template>
  <Layout>

    <PageHeader title="Brands" pageTitle="All Brands"
>
        <template #actions>
          <Link :href="route('admin.brand.create')" class="btn btn-primary">
            Create Brand
          </Link>
        </template>
      </PageHeader>


    <BRow>
      <BCol lg="12">
        <BCard no-body>
          <CardHeader
            title="Brands List"
            :model="form"
            SearchButton="true"
            @getResourese="getResourese"
          />

          <!-- Table displaying Brands -->
          <BTable :items="brands.data" :fields="['id', 'name',  'created_at', 'updated_at','actions']" :per-page="10">
            <template #cell(id)="data">
              <span>{{ data.item.id }}</span>
            </template>
            <template #cell(name)="data">
              <span>{{ data.item.name }}</span>
            </template>

            <template #cell(created_at)="data">
              <span>{{ data.item.created_at }}</span>
            </template>
            <template #cell(updated_at)="data">
              <span>{{ data.item.updated_at }}</span>
            </template>
            <template #cell(actions)="data">
                <Link :href="route('admin.brand.edit', data.item.id)" class="btn btn-warning btn-sm me-2">
                    Edit
                  </Link>
                <button
                  class="btn btn-danger btn-sm"
                  @click="() => DeleteItem(data.item.id)"
                >
                  Delete
                </button>
              </template>
          </BTable>

          <BCardFooter>
            <BPagination
              v-model="brands.current_page"
              :total-rows="brands.total"
              :per-page="10"
              @page-click="getResourese"
            />
          </BCardFooter>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
