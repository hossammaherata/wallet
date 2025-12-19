<script setup>
import { defineProps, defineEmits } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  title: {
    type: String,
    default: "",
  },
  SearchButton: {
    type: Boolean,
    default: false,
  },
  model: {
    type: [Object, Array],
    default: () => ({ keyword: "" })  // Default value for the 'model' prop
  },
});

const emit = defineEmits(["getResourese", "reset"]);
const submit = () => {
  emit("getResourese", props.model); // Emit the event with the updated props.model
};
const reset = () => {
  props.model.keyword = "";
  emit("reset", props.model); // Emit reset event
};
</script>

<template>
  <b-card-header class="align-items-center d-flex">
    <b-card-title class="mb-0 flex-grow-1">{{ title }}</b-card-title>

    <div class="d-flex align-items-center gap-2">
      <!-- Actions Slot -->
      <div v-if="$slots.actions">
        <slot name="actions"></slot>
      </div>

      <!-- Search Section -->
      <div v-if="SearchButton" class="d-flex align-items-center">
        <!-- Search Input -->
        <b-form-input
          type="text"
          v-model="model.keyword"
          :placeholder="t('t-search') + '....'"
          class="mr-2"
          @keyup.enter="submit"
        />
        <!-- Search Button -->
        <b-button @click.prevent="submit" style="margin-left:15px;" variant="primary">
          <i class="fas fa-search"></i> {{ t('t-search') }}
        </b-button>
        <!-- Reset Button -->
        <b-button 
          @click.prevent="reset" 
          style="margin-left:10px;" 
          variant="secondary"
        >
          <i class="ri-refresh-line"></i> {{ t('t-reset') }}
        </b-button>
      </div>
    </div>
  </b-card-header>
</template>
