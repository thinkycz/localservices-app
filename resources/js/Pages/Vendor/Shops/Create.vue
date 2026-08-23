<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import UiButton from '@/Components/UiButton.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import ShopForm from './ShopForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';

defineProps({ categories: { type: Array, required: true } });

const dayNames = ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'];
const hours = dayNames.map((label, day_of_week) => ({
    day_of_week,
    label,
    enabled: day_of_week >= 1 && day_of_week <= 5,
    time_from: '09:00',
    time_to: '17:00',
}));

const form = useForm({
    name: '',
    category_id: '',
    currency: 'CZK',
    description: '',
    address: '',
    city: '',
    state: '',
    is_available: true,
    is_online_only: false,
    image: null,
    remove_image: false,
    business_hours: [],
});

function submit() {
    form.transform((data) => ({
        ...data,
        business_hours: hours
            .filter((hour) => hour.enabled)
            .map(({ day_of_week, time_from, time_to }) => ({ day_of_week, time_from, time_to })),
    })).post(route('vendor.shops.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Nová provozovna" />
    <VendorLayout activePage="shops">
        <div class="mx-auto max-w-4xl space-y-6">
            <UiButton :href="route('vendor.shops.index')" variant="ghost" size="sm"><ArrowLeft :size="17" /> Zpět na provozovny</UiButton>
            <PageHeader title="Přidat provozovnu" description="Vyplňte údaje, nastavte otevírací dobu a provozovnu pak doplňte službami." />
            <form class="space-y-5" @submit.prevent="submit">
                <ShopForm :form="form" :categories="categories" :hours="hours" />
                <div class="sticky bottom-20 z-20 flex flex-col-reverse gap-2 rounded-2xl border border-line bg-white/95 p-4 shadow-lift backdrop-blur sm:static sm:flex-row sm:justify-end sm:shadow-none lg:bottom-4">
                    <UiButton :href="route('vendor.shops.index')" variant="secondary">Zrušit</UiButton>
                    <UiButton type="submit" :loading="form.processing"><Save :size="18" /> Uložit provozovnu</UiButton>
                </div>
            </form>
        </div>
    </VendorLayout>
</template>
