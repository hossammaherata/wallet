<script setup>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";

import { useForm, usePage } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { BRow, BCol, BCard, BCardBody, BButton, BAlert } from 'bootstrap-vue-next';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Accept `configuration` as props
const { configuration } = usePage().props;

// Initialize form with configuration data
const initForm = () => {
    const ugcPrizes = configuration?.ugc_prizes || [0, 0, 0];
    const nominationPrizes = configuration?.nomination_prizes || {
        attendance_fan: [0, 0, 0, 0, 0],
        online_fan: [0, 0, 0, 0, 0],
    };

    return {
        transfer_fee_percentage: configuration?.transfer_fee_percentage || 0,
        withdrawal_fee_percentage: configuration?.withdrawal_fee_percentage || 0,
        ugc_prizes: [...ugcPrizes],
        nomination_prizes: {
            attendance_fan: [...(nominationPrizes.attendance_fan || [0, 0, 0, 0, 0])],
            online_fan: [...(nominationPrizes.online_fan || [0, 0, 0, 0, 0])],
        },
    };
};

const form = useForm(initForm());

const updateConfiguration = () => {
    form.put(route('admin.wallet-configuration.update'), {
        errorBag: 'Update',
        preserveScroll: true,
        onSuccess: () => {
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: t('t-success'),
                text: t('t-settings-updated'),
            });
        },
        onError: () => {
            // Handle errors if needed
        },
    });
};

// Calculate example fees for display
const calculateTransferFeeExample = (amount) => {
    const fee = (amount * form.transfer_fee_percentage) / 100;
    return {
        original: amount,
        fee: fee,
        net: amount - fee
    };
};

const calculateWithdrawalFeeExample = (amount) => {
    const fee = (amount * form.withdrawal_fee_percentage) / 100;
    return {
        original: amount,
        fee: fee,
        net: amount - fee
    };
};

const transferExample = calculateTransferFeeExample(2000);
const withdrawalExample = calculateWithdrawalFeeExample(2000);
</script>

<template>
    <Layout>
        <PageHeader :title="t('t-fee-settings')" :pageTitle="t('t-dashboards')" />
        
        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader :title="t('t-wallet-fee-settings')" />
                    <BCardBody>
                        <BAlert variant="info" show class="mb-4">
                            <strong>{{ t('t-note') }}:</strong> {{ t('t-fee-note') }}
                            <br>
                            <strong>{{ t('t-example') }}:</strong> {{ t('t-fee-example') }}
                        </BAlert>

                        <FormSection @submitted="updateConfiguration">
                            <template #form>
                                <!-- Transfer Fee Section -->
                                <div class="mb-4">
                                    <h5 class="mb-3">{{ t('t-transfer-fee') }}</h5>
                                    
                                    <div class="mb-3">
                                        <InputLabel for="transfer_fee_percentage" :value="t('t-transfer-fee-percentage')" />
                                        <TextInput
                                            id="transfer_fee_percentage"
                                            v-model="form.transfer_fee_percentage"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="mt-1 block w-full"
                                            :class="{ 'is-invalid': form.errors.transfer_fee_percentage }"
                                            placeholder="0.00"
                                        />
                                        <small class="form-text text-muted">
                                            {{ t('t-enter-percentage') }}
                                        </small>
                                        <InputError :message="form.errors.transfer_fee_percentage" class="mt-2" />
                                    </div>

                                    <!-- Example Calculation for Transfer -->
                                    <!-- <div v-if="form.transfer_fee_percentage > 0" class="alert alert-light">
                                        <strong>{{ t('t-calculation-example') }}</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>{{ t('t-original-amount-label') }} {{ transferExample.original.toFixed(2) }} {{ t('t-points') }}</li>
                                            <li>{{ t('t-fee-amount-label') }} ({{ form.transfer_fee_percentage }}%): {{ transferExample.fee.toFixed(2) }} {{ t('t-points') }}</li>
                                            <li>{{ t('t-net-amount-label') }}: {{ transferExample.net.toFixed(2) }} {{ t('t-points') }}</li>
                                        </ul>
                                        <small class="text-muted">
                                            <strong>{{ t('t-note') }}:</strong> {{ t('t-first-transfer-note') }}
                                        </small>
                                    </div> -->
                                </div>

                                <hr class="my-4">

                                <!-- Withdrawal Fee Section -->
                                <div class="mb-4">
                                    <h5 class="mb-3">{{ t('t-withdrawal-fee') }}</h5>
                                    
                                    <div class="mb-3">
                                        <InputLabel for="withdrawal_fee_percentage" :value="t('t-withdrawal-fee-percentage')" />
                                        <TextInput
                                            id="withdrawal_fee_percentage"
                                            v-model="form.withdrawal_fee_percentage"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="mt-1 block w-full"
                                            :class="{ 'is-invalid': form.errors.withdrawal_fee_percentage }"
                                            placeholder="0.00"
                                        />
                                        <small class="form-text text-muted">
                                            {{ t('t-enter-percentage') }}
                                        </small>
                                        <InputError :message="form.errors.withdrawal_fee_percentage" class="mt-2" />
                                    </div>

                                    <!-- Example Calculation for Withdrawal -->
                                    <!-- <div v-if="form.withdrawal_fee_percentage > 0" class="alert alert-light">
                                        <strong>{{ t('t-calculation-example') }}</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>{{ t('t-original-amount-label') }} {{ withdrawalExample.original.toFixed(2) }} {{ t('t-points') }}</li>
                                            <li>{{ t('t-fee-amount-label') }} ({{ form.withdrawal_fee_percentage }}%): {{ withdrawalExample.fee.toFixed(2) }} {{ t('t-points') }}</li>
                                            <li>{{ t('t-net-amount-label') }}: {{ withdrawalExample.net.toFixed(2) }} {{ t('t-points') }}</li>
                                        </ul>
                                    </div> -->
                                </div>

                                <hr class="my-4">

                                <!-- UGC Prizes Section -->
                                <div class="mb-4">
                                    <h5 class="mb-3">جوائز UGC (3 فائزين)</h5>
                                    <BAlert variant="info" show class="mb-3">
                                        <small>قم بتعيين الجوائز للمراكز الثلاثة الأولى في فعاليات UGC</small>
                                    </BAlert>
                                    
                                    <BRow>
                                        <BCol md="4" v-for="(prize, index) in form.ugc_prizes" :key="'ugc-' + index">
                                            <div class="mb-3">
                                                <InputLabel :for="'ugc_prize_' + (index + 1)" :value="`المركز ${index + 1}`" />
                                                <TextInput
                                                    :id="'ugc_prize_' + (index + 1)"
                                                    v-model="form.ugc_prizes[index]"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="mt-1 block w-full"
                                                    :class="{ 'is-invalid': form.errors[`ugc_prizes.${index}`] }"
                                                    placeholder="0"
                                                />
                                                <InputError :message="form.errors[`ugc_prizes.${index}`]" class="mt-2" />
                                            </div>
                                        </BCol>
                                    </BRow>
                                </div>

                                <hr class="my-4">

                                <!-- Nomination Prizes Section -->
                                <div class="mb-4">
                                    <h5 class="mb-3">جوائز Nomination (10 فائزين)</h5>
                                    <BAlert variant="info" show class="mb-3">
                                        <small>قم بتعيين الجوائز للمراكز الخمسة الأولى لكل فئة (attendance_fan و online_fan)</small>
                                    </BAlert>
                                    
                                    <!-- Attendance Fan Prizes -->
                                    <div class="mb-4">
                                        <h6 class="mb-3">جوائز Attendance Fan (5 فائزين)</h6>
                                        <BRow>
                                            <BCol md="2" v-for="(prize, index) in form.nomination_prizes.attendance_fan" :key="'attendance-' + index">
                                                <div class="mb-3">
                                                    <InputLabel :for="'attendance_prize_' + (index + 1)" :value="`المركز ${index + 1}`" />
                                                    <TextInput
                                                        :id="'attendance_prize_' + (index + 1)"
                                                        v-model="form.nomination_prizes.attendance_fan[index]"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        class="mt-1 block w-full"
                                                        :class="{ 'is-invalid': form.errors[`nomination_prizes.attendance_fan.${index}`] }"
                                                        placeholder="0"
                                                    />
                                                    <InputError :message="form.errors[`nomination_prizes.attendance_fan.${index}`]" class="mt-2" />
                                                </div>
                                            </BCol>
                                        </BRow>
                                    </div>

                                    <!-- Online Fan Prizes -->
                                    <div class="mb-4">
                                        <h6 class="mb-3">جوائز Online Fan (5 فائزين)</h6>
                                        <BRow>
                                            <BCol md="2" v-for="(prize, index) in form.nomination_prizes.online_fan" :key="'online-' + index">
                                                <div class="mb-3">
                                                    <InputLabel :for="'online_prize_' + (index + 1)" :value="`المركز ${index + 1}`" />
                                                    <TextInput
                                                        :id="'online_prize_' + (index + 1)"
                                                        v-model="form.nomination_prizes.online_fan[index]"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        class="mt-1 block w-full"
                                                        :class="{ 'is-invalid': form.errors[`nomination_prizes.online_fan.${index}`] }"
                                                        placeholder="0"
                                                    />
                                                    <InputError :message="form.errors[`nomination_prizes.online_fan.${index}`]" class="mt-2" />
                                                </div>
                                            </BCol>
                                        </BRow>
                                    </div>
                                </div>
                            </template>

                            <template #actions>
                                <BButton
                                    variant="primary"
                                    type="submit"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? t('t-saving') : t('t-save-settings') }}
                                </BButton>
                                <p v-if="form.recentlySuccessful" class="alert alert-success mt-3">
                                    {{ t('t-settings-updated') }}
                                </p>
                            </template>
                        </FormSection>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

