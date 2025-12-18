<script>
import { Link, router } from '@inertiajs/vue3';

export default {
  props: {
    title: {
      type: String,
      default: "",
    },
    pageTitle: {
      type: String,
      default: "",
    },
    href: {
      type: String,
      default: "javascript: void(0);",
    }
  },
};
</script>

<template>
  <BRow>
    <BCol cols="12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">{{ title }}</h4>
        <div class="page-title-right d-flex align-items-center">
          <ol class="breadcrumb m-0 me-3">
            <li class="breadcrumb-item">
              <BLink :href="href">{{ pageTitle }}</BLink>
            </li>
            <li class="breadcrumb-item active">{{ title }}</li>
          </ol>
          <slot name="actions"></slot> <!-- Move slot inside the div -->
        </div>
      </div>
    </BCol>
  </BRow>
</template>
