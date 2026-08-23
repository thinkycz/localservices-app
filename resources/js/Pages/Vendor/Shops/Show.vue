<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TextInput from '@/Components/TextInput.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Clock, MapPin, Pencil, Plus, Store, Trash2, Wrench } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
    shop: { type: Object, required: true },
    categories: { type: Array, required: true },
    stats: { type: Object, required: true },
});

const showServiceModal = ref(false);
const editingService = ref(null);
const serviceForm = useForm({ name: '', description: '', duration_minutes: 60, price: '', is_popular: false, category_tag: '', staff_level: '' });

function money(value) {
    return new Intl.NumberFormat('cs-CZ', { style: 'currency', currency: props.shop.currency || 'CZK', maximumFractionDigits: 2 }).format(Number(value || 0));
}

function toggleAvailability() {
    router.post(route('vendor.shops.toggle', props.shop.id), {}, { preserveScroll: true });
}

function openService(service = null) {
    editingService.value = service;
    serviceForm.clearErrors();
    serviceForm.defaults({
        name: service?.name || '',
        description: service?.description || '',
        duration_minutes: service?.duration_minutes || 60,
        price: service?.price || '',
        is_popular: Boolean(service?.is_popular),
        category_tag: service?.category_tag || '',
        staff_level: service?.staff_level || '',
    });
    serviceForm.reset();
    showServiceModal.value = true;
}

function closeService() {
    showServiceModal.value = false;
    editingService.value = null;
    serviceForm.clearErrors();
}

function saveService() {
    const options = { preserveScroll: true, onSuccess: closeService };
    if (editingService.value) {
        serviceForm.put(route('vendor.shops.services.update', { shopId: props.shop.id, serviceId: editingService.value.id }), options);
    } else {
        serviceForm.post(route('vendor.shops.services.store', props.shop.id), options);
    }
}

function removeService(service) {
    if (window.confirm(`Opravdu chcete smazat službu „${service.name}“?`)) {
        router.delete(route('vendor.shops.services.destroy', { shopId: props.shop.id, serviceId: service.id }), { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="shop.name" />
    <VendorLayout activePage="shops">
        <div class="space-y-6">
            <UiButton :href="route('vendor.shops.index')" variant="ghost" size="sm"><ArrowLeft :size="17" /> Zpět na provozovny</UiButton>

            <UiCard padding="none" class="overflow-hidden">
                <div class="grid md:grid-cols-[18rem_minmax(0,1fr)]">
                    <img v-if="shop.cover_image_url" :src="shop.cover_image_url" :alt="`Úvodní fotografie ${shop.name}`" class="aspect-video h-full w-full object-cover md:aspect-auto" />
                    <div v-else class="flex min-h-44 items-center justify-center bg-brand-50 text-brand-700"><Store :size="44" stroke-width="1.5" /></div>
                    <div class="min-w-0 p-5 sm:p-6">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2"><h1 class="break-words text-2xl font-extrabold tracking-tight text-ink">{{ shop.name }}</h1><StatusBadge :tone="shop.is_available ? 'success' : 'neutral'">{{ shop.is_available ? 'Aktivní' : 'Neaktivní' }}</StatusBadge></div>
                                <p class="mt-2 text-sm font-semibold text-muted">{{ shop.category?.name || 'Bez kategorie' }}</p>
                                <p class="mt-3 flex items-start gap-2 text-sm text-muted"><MapPin :size="17" class="mt-0.5 flex-none" /><span>{{ shop.is_online_only ? 'Pouze online' : ([shop.address, shop.city].filter(Boolean).join(', ') || 'Adresa není doplněna') }}</span></p>
                            </div>
                            <div class="flex flex-wrap gap-2 sm:flex-none"><UiButton :href="route('vendor.shops.edit', shop.id)" variant="secondary"><Pencil :size="17" /> Upravit</UiButton><UiButton variant="ghost" @click="toggleAvailability">{{ shop.is_available ? 'Skrýt' : 'Aktivovat' }}</UiButton></div>
                        </div>
                        <p v-if="shop.description" class="mt-5 max-w-3xl text-sm leading-6 text-muted">{{ shop.description }}</p>
                    </div>
                </div>
            </UiCard>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <UiCard v-for="item in [
                    { label: 'Všechny rezervace', value: stats.total_bookings ?? 0, icon: CalendarDays },
                    { label: 'Dokončené', value: stats.completed_bookings ?? 0, icon: Wrench },
                    { label: 'Zrušené', value: stats.cancelled_bookings ?? 0, icon: Clock },
                    { label: 'Hodnota nezrušených rezervací', value: money(stats.total_revenue), icon: Store },
                ]" :key="item.label" padding="sm" class="min-w-0"><div class="flex gap-3"><span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><component :is="item.icon" :size="19" /></span><div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-muted">{{ item.label }}</p><p class="mt-1 break-words text-xl font-extrabold text-ink">{{ item.value }}</p></div></div></UiCard>
            </div>

            <section aria-labelledby="services-title" class="space-y-4">
                <PageHeader title="Služby" :description="`${shop.services?.length || 0} služeb v měně ${shop.currency || 'CZK'}`">
                    <template #actions><UiButton @click="openService()"><Plus :size="18" /> Přidat službu</UiButton></template>
                </PageHeader>

                <EmptyState v-if="!shop.services?.length" title="Zatím nenabízíte žádnou službu" description="Přidejte název, délku a cenu první služby. Bez služby nelze vytvořit rezervaci.">
                    <template #icon><Wrench :size="23" /></template><template #actions><UiButton @click="openService()"><Plus :size="18" /> Přidat službu</UiButton></template>
                </EmptyState>

                <template v-else>
                    <div class="grid gap-3 md:hidden">
                        <UiCard v-for="service in shop.services" :key="service.id" padding="sm">
                            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="break-words font-extrabold text-ink">{{ service.name }}</h3><StatusBadge v-if="service.is_popular" tone="warning">Oblíbená</StatusBadge></div><p v-if="service.description" class="mt-1 text-sm leading-5 text-muted">{{ service.description }}</p></div><strong class="flex-none text-sm text-ink">{{ money(service.price) }}</strong></div>
                            <div class="mt-4 flex items-center justify-between border-t border-line pt-4"><span class="flex items-center gap-2 text-sm text-muted"><Clock :size="16" /> {{ service.duration_minutes }} min</span><div class="flex gap-1"><button class="ui-icon-button" aria-label="Upravit službu" @click="openService(service)"><Pencil :size="17" /></button><button class="ui-icon-button text-danger" aria-label="Smazat službu" @click="removeService(service)"><Trash2 :size="17" /></button></div></div>
                        </UiCard>
                    </div>
                    <div class="hidden overflow-hidden rounded-2xl border border-line bg-white md:block"><div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left"><thead class="border-b border-line bg-gray-50 text-xs font-bold uppercase tracking-wide text-muted"><tr><th class="px-5 py-3">Služba</th><th class="px-5 py-3">Délka</th><th class="px-5 py-3">Cena</th><th class="px-5 py-3">Štítek</th><th class="px-5 py-3 text-right">Akce</th></tr></thead><tbody class="divide-y divide-line"><tr v-for="service in shop.services" :key="service.id"><td class="px-5 py-4"><p class="font-extrabold text-ink">{{ service.name }}</p><p v-if="service.description" class="mt-1 max-w-md truncate text-xs text-muted">{{ service.description }}</p></td><td class="px-5 py-4 text-sm text-muted">{{ service.duration_minutes }} min</td><td class="px-5 py-4 text-sm font-bold text-ink">{{ money(service.price) }}</td><td class="px-5 py-4"><StatusBadge v-if="service.is_popular" tone="warning">Oblíbená</StatusBadge><span v-else class="text-sm text-muted">—</span></td><td class="px-5 py-4"><div class="flex justify-end gap-1"><button class="ui-icon-button" aria-label="Upravit službu" @click="openService(service)"><Pencil :size="17" /></button><button class="ui-icon-button text-danger" aria-label="Smazat službu" @click="removeService(service)"><Trash2 :size="17" /></button></div></td></tr></tbody></table></div></div>
                </template>
            </section>
        </div>

        <Modal :show="showServiceModal" max-width="lg" @close="closeService">
            <form @submit.prevent="saveService">
                <div class="border-b border-line px-5 py-5 sm:px-6"><h2 class="text-lg font-extrabold text-ink">{{ editingService ? 'Upravit službu' : 'Přidat službu' }}</h2><p class="mt-1 text-sm text-muted">Zákazník uvidí název, délku a přesnou cenu.</p></div>
                <div class="grid gap-5 px-5 py-6 sm:grid-cols-2 sm:px-6">
                    <div class="sm:col-span-2"><InputLabel for="service-name" value="Název" /><TextInput id="service-name" v-model="serviceForm.name" class="mt-2 block w-full" required /><InputError class="mt-2" :message="serviceForm.errors.name" /></div>
                    <div><InputLabel for="service-duration" value="Délka (minuty)" /><TextInput id="service-duration" v-model="serviceForm.duration_minutes" type="number" min="1" class="mt-2 block w-full" required /><InputError class="mt-2" :message="serviceForm.errors.duration_minutes" /></div>
                    <div><InputLabel for="service-price" :value="`Cena (${shop.currency || 'CZK'})`" /><TextInput id="service-price" v-model="serviceForm.price" type="number" min="0" step="0.01" class="mt-2 block w-full" required /><InputError class="mt-2" :message="serviceForm.errors.price" /></div>
                    <div class="sm:col-span-2"><InputLabel for="service-description" value="Popis" /><textarea id="service-description" v-model="serviceForm.description" rows="4" class="ui-field mt-2 resize-y" /><InputError class="mt-2" :message="serviceForm.errors.description" /></div>
                    <label class="sm:col-span-2 flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-line px-4 py-3 text-sm font-bold text-ink"><input v-model="serviceForm.is_popular" type="checkbox" class="rounded border-line text-brand-600 focus:ring-brand-600" /> Označit jako oblíbenou</label>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-line px-5 py-4 sm:flex-row sm:justify-end sm:px-6"><UiButton variant="secondary" @click="closeService">Zrušit</UiButton><UiButton type="submit" :loading="serviceForm.processing">{{ editingService ? 'Uložit změny' : 'Přidat službu' }}</UiButton></div>
            </form>
        </Modal>
    </VendorLayout>
</template>
