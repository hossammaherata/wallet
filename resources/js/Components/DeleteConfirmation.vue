<template>
  <div>
    <button @click="confirmDelete" :class="buttonClass">
      <slot></slot>
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import Swal from 'sweetalert2';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js'; // Import the route function

const props = defineProps({
  routeName: String,
  resourceId: [String, Number],
  buttonClass: {
    type: String,
    default: 'btn btn-danger btn-sm'
  }
});

const emit = defineEmits(['deleted']);

const form = useForm();

const confirmDelete = () => {
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
      form.delete(route(props.routeName, props.resourceId), {
        onSuccess: () => {
          Swal.fire(
            'Deleted!',
            'Your resource has been deleted.',
            'success'
          );
          emit('deleted');
        },
      });
    }
  });
};
</script>
