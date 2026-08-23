<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import UiButton from '@/Components/UiButton.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import ShopForm from './ShopForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';

const props = defineProps({
    shop: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const dayNames = ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'];
const hours = dayNames.map((label, day_of_week) => {
    const existing = props.shop.business_hours?.find((item) => Number(item.day_of_week) === day_of_week);
    return {
        day_of_week,
        label,
        enabled: Boolean(existing),
        time_from: existing?.time_from?.slice(0, 5) || '09:00',
        time_to: existing?.time_to?.slice(0, 5) || '17:00',
    };
});

const form = useForm({
    _method: 'put',
    name: props.shop.name || '',
    category_id: props.shop.category_id || '',
    currency: props.shop.currency || 'CZK',
    description: props.shop.description || '',
    address: props.shop.address || '',
    city: props.shop.city || '',
    state: props.shop.state || '',
    is_available: Boolean(props.shop.is_available),
    is_online_only: Boolean(props.shop.is_online_only),
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
    })).post(route('vendor.shops.update', props.shop.id), { forceFormData: true });
}
</script>

<template>
    <Head :title="`Upravit ${shop.name}`" />
    <VendorLayout activePage="shops">
        <div class="mx-auto max-w-4xl space-y-6">
            <UiButton :href="route('vendor.shops.show', shop.id)" variant="ghost" size="sm"><ArrowLeft :size="17" /> Zpět na detail</UiButton>
            <PageHeader :title="`Upravit ${shop.name}`" description="Změny v dostupnosti, otevírací době a fotografii se projeví zákazníkům po uložení." />
            <form class="space-y-5" @submit.prevent="submit">
                <ShopForm :form="form" :categories="categories" :hours="hours" :existing-image="shop.cover_image_url" />
                <div class="sticky bottom-20 z-20 flex flex-col-reverse gap-2 rounded-2xl border border-line bg-white/95 p-4 shadow-lift backdrop-blur sm:static sm:flex-row sm:justify-end sm:shadow-none lg:bottom-4">
                    <UiButton :href="route('vendor.shops.show', shop.id)" variant="secondary">Zrušit</UiButton>
                    <UiButton type="submit" :loading="form.processing"><Save :size="18" /> Uložit změny</UiButton>
                </div>
            </form>
        </div>
    </VendorLayout>
</template>
