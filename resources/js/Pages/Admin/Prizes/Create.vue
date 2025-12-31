<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { BRow, BCol, BCard, BCardBody, BButton } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const dates = ref(['']);

const form = useForm({
    name: '',
    dates: [],
    total_winners: '',
    total_amount: '',
});

const addDate = () => {
    dates.value.push('');
};

const removeDate = (index) => {
    dates.value.splice(index, 1);
};

const submit = () => {
    // Filter out empty dates
    form.dates = dates.value.filter(date => date.trim() !== '');
    
    if (form.dates.length === 0) {
        Swal.fire({
            icon: 'error',
            title: t('t-error-message'),
            text: t('t-must-add-at-least-one-date')
        });
        return;
    }

    form.post(route('admin.prizes.store'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: t('t-success-message'),
                text: t('t-prize-created')
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
        <PageHeader :title="t('t-add-new-prize')" :pageTitle="t('t-prizes-management')" :href="route('admin.prizes.index')" />
        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-add-new-prize')" />

                    <FormSection @submitted="submit">
                        <template #form>
                            <div class="mb-3">
                                <InputLabel for="name" :value="t('t-prize-name')" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.name }"
                                    :placeholder="t('t-example') + ': يوم السباق الأول'"
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel :value="t('t-available-dates')" />
                                <div v-for="(date, index) in dates" :key="index" class="mb-2 d-flex gap-2">
                                    <TextInput
                                        v-model="dates[index]"
                                        type="date"
                                        class="flex-grow-1"
                                        :class="{ 'is-invalid': form.errors[`dates.${index}`] }"
                                    />
                                    <BButton
                                        v-if="dates.length > 1"
                                        variant="danger"
                                        @click="removeDate(index)"
                                    >
                                        <i class="ri-delete-bin-line"></i>
                                    </BButton>
                                </div>
                                <BButton variant="outline-primary" @click="addDate" class="mt-2">
                                    <i class="ri-add-line me-1"></i>
                                    {{ t('t-add-date') }}
                                </BButton>
                                <InputError :message="form.errors.dates" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel for="total_winners" :value="t('t-total-winners-count')" />
                                <TextInput
                                    id="total_winners"
                                    v-model="form.total_winners"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.total_winners }"
                                    :placeholder="t('t-example') + ': 4'"
                                />
                                <InputError :message="form.errors.total_winners" class="mt-2" />
                            </div>

                            <div class="mb-3">
                                <InputLabel for="total_amount" :value="t('t-prize-amount-points')" />
                                <TextInput
                                    id="total_amount"
                                    v-model="form.total_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full"
                                    :class="{ 'is-invalid': form.errors.total_amount }"
                                    :placeholder="t('t-example') + ': 5000'"
                                />
                                <InputError :message="form.errors.total_amount" class="mt-2" />
                                <small class="text-muted">
                                    {{ t('t-ticket-will-be-created-per-date') }}
                                </small>
                            </div>

                        </template>

                        <template #actions>
                            <BButton variant="primary" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ t('t-save') }}
                            </BButton>
                            <Link :href="route('admin.prizes.index')" class="btn btn-secondary ms-2">
                                {{ t('t-cancel') }}
                            </Link>
                        </template>
                    </FormSection>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

