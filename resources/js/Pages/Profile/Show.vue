<script setup>
import { Head } from "@inertiajs/vue3"
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import CardHeader from "@/common/card-header.vue";
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
});
</script>

<template>
    <Layout>
        <Head title="Profile" />
        <PageHeader title="Profile" pageTitle="User Profile" />

        <BRow>
            <BCol lg="12">
                <BCard no-body>
                    <CardHeader title="Profile Information" />
                    <BCardBody>
                        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                            <UpdateProfileInformationForm :user="$page.props.auth.user" />
                        </div>

                        <div v-if="$page.props.jetstream.canUpdatePassword" class="mt-4">
                            <UpdatePasswordForm />
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>
