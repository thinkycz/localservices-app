<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import VendorLayout from '@/Layouts/VendorLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Check, CircleAlert, Clock, Mail, MapPin, NotebookPen, Phone, UserRound, X } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps({ booking: { type: Object, required: true }, customerHistory: { type: Array, default: () => [] } });

const showCancel = ref(false);
const showNotes = ref(false);
const actionError = ref('');
const processingAction = ref(null);
const cancellationForm = useForm({ cancellation_reason: '' });
const notesForm = useForm({ notes: '' });

const statusMap = {
    pending: { label: 'Čeká na potvrzení', tone: 'warning' },
    confirmed: { label: 'Potvrzená', tone: 'brand' },
    completed: { label: 'Dokončená', tone: 'success' },
    cancelled: { label: 'Zrušená', tone: 'danger' },
};

const appointmentStart = computed(() => new Date(`${String(props.booking.booking_date).slice(0, 10)}T${String(props.booking.start_time).slice(0, 8)}`));
const canComplete = computed(() => props.booking.status === 'confirmed' && appointmentStart.value.getTime() <= Date.now());
const canCancel = computed(() => ['pending', 'confirmed'].includes(props.booking.status));

function formatDate(value, long = true) {
    if (!value) return '—';
    return new Intl.DateTimeFormat('cs-CZ', long ? { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' } : { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(`${String(value).slice(0, 10)}T12:00:00`));
}

function formatTime(value) { return String(value || '').slice(0, 5) || '—'; }
function customerName() { return props.booking.customer_display_name || props.booking.customer_name || props.booking.customer?.name || 'Zákazník bez jména'; }
function customerEmail() { return props.booking.customer_contact_email || props.booking.customer_email || props.booking.customer?.email || '—'; }
function customerPhone() { return props.booking.customer_phone || props.booking.customer?.phone || 'Neuveden'; }
function money(booking = props.booking) { return new Intl.NumberFormat('cs-CZ', { style: 'currency', currency: booking.currency || booking.shop?.currency || 'CZK' }).format(Number(booking.price_amount ?? booking.total_price ?? booking.service?.price ?? 0)); }

function postAction(action, routeName) {
    actionError.value = '';
    processingAction.value = action;
    router.post(route(routeName, props.booking.id), {}, {
        preserveScroll: true,
        onError: (errors) => { actionError.value = Object.values(errors || {})[0] || 'Akci se nepodařilo dokončit. Obnovte stránku a zkuste to znovu.'; },
        onFinish: () => { processingAction.value = null; },
    });
}

function cancelBooking() {
    actionError.value = '';
    cancellationForm.post(route('vendor.bookings.cancel', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => { showCancel.value = false; cancellationForm.reset(); },
        onError: (errors) => { actionError.value = Object.values(errors || {})[0] || 'Rezervaci se nepodařilo zrušit.'; },
    });
}

function saveNotes() {
    notesForm.post(route('vendor.bookings.notes', props.booking.id), { preserveScroll: true, onSuccess: () => { showNotes.value = false; notesForm.reset(); } });
}
</script>

<template>
    <Head :title="`Rezervace #${booking.id}`" />
    <VendorLayout activePage="bookings">
        <div class="mx-auto max-w-6xl space-y-6">
            <UiButton :href="route('vendor.bookings.index')" variant="ghost" size="sm"><ArrowLeft :size="17" /> Zpět na rezervace</UiButton>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0"><div class="flex flex-wrap items-center gap-3"><h1 class="text-2xl font-extrabold tracking-tight text-ink">Rezervace #{{ booking.id }}</h1><StatusBadge :tone="statusMap[booking.status]?.tone || 'neutral'">{{ statusMap[booking.status]?.label || booking.status }}</StatusBadge></div><p class="mt-2 text-sm text-muted">{{ booking.service?.name || 'Služba' }} · {{ booking.shop?.name || 'Provozovna' }}</p></div>
                <div class="flex flex-wrap gap-2">
                    <UiButton v-if="booking.status === 'pending'" :loading="processingAction === 'confirm'" @click="postAction('confirm', 'vendor.bookings.confirm')"><Check :size="18" /> Potvrdit</UiButton>
                    <UiButton v-if="booking.status === 'confirmed'" :disabled="!canComplete" :loading="processingAction === 'complete'" @click="postAction('complete', 'vendor.bookings.complete')"><Check :size="18" /> Označit jako dokončenou</UiButton>
                    <UiButton v-if="canCancel" variant="danger" @click="showCancel = true"><X :size="18" /> Zrušit</UiButton>
                </div>
            </div>

            <div v-if="booking.status === 'confirmed' && !canComplete" class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"><CircleAlert :size="20" class="mt-0.5 flex-none" /><p>Rezervaci lze dokončit až po začátku termínu {{ formatDate(booking.booking_date) }} v {{ formatTime(booking.start_time) }}.</p></div>
            <div v-if="actionError" role="alert" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-danger"><CircleAlert :size="20" class="mt-0.5 flex-none" /><p>{{ actionError }}</p></div>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,1fr)]">
                <div class="space-y-5">
                    <UiCard>
                        <h2 class="text-base font-extrabold text-ink">Termín a služba</h2>
                        <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                            <div><dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted"><CalendarDays :size="16" /> Datum</dt><dd class="mt-2 font-bold capitalize text-ink">{{ formatDate(booking.booking_date) }}</dd></div>
                            <div><dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted"><Clock :size="16" /> Čas</dt><dd class="mt-2 font-bold text-ink">{{ formatTime(booking.start_time) }}–{{ formatTime(booking.end_time) }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wide text-muted">Služba</dt><dd class="mt-2 font-bold text-ink">{{ booking.service?.name || 'Služba již není dostupná' }}</dd></div>
                            <div><dt class="text-xs font-bold uppercase tracking-wide text-muted">Cena</dt><dd class="mt-2 font-bold text-ink">{{ money() }}</dd></div>
                            <div class="sm:col-span-2"><dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-muted"><MapPin :size="16" /> Provozovna</dt><dd class="mt-2 font-bold text-ink">{{ booking.shop?.name || '—' }}</dd><dd class="mt-1 text-sm text-muted">{{ [booking.shop?.address, booking.shop?.city].filter(Boolean).join(', ') }}</dd></div>
                        </dl>
                    </UiCard>

                    <UiCard>
                        <div class="flex items-center justify-between gap-3"><div><h2 class="text-base font-extrabold text-ink">Poznámky</h2><p class="mt-1 text-sm text-muted">Informace od zákazníka a vaše interní poznámka.</p></div><UiButton variant="secondary" size="sm" @click="showNotes = true"><NotebookPen :size="17" /> Přidat</UiButton></div>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2"><div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-bold uppercase text-muted">Od zákazníka</p><p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-ink">{{ booking.customer_notes || 'Bez poznámky' }}</p></div><div class="rounded-xl bg-gray-50 p-4"><p class="text-xs font-bold uppercase text-muted">Interní</p><p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-ink">{{ booking.notes || 'Bez interní poznámky' }}</p></div></div>
                    </UiCard>

                    <UiCard v-if="customerHistory.length">
                        <h2 class="text-base font-extrabold text-ink">Předchozí rezervace zákazníka</h2>
                        <div class="mt-4 divide-y divide-line"><Link v-for="item in customerHistory" :key="item.id" :href="route('vendor.bookings.show', item.id)" class="flex min-h-14 items-center justify-between gap-4 py-3"><div class="min-w-0"><p class="truncate text-sm font-bold text-ink">{{ item.service?.name || 'Služba' }}</p><p class="mt-1 text-xs text-muted">{{ formatDate(item.booking_date, false) }} · {{ formatTime(item.start_time) }}</p></div><StatusBadge :tone="statusMap[item.status]?.tone || 'neutral'">{{ statusMap[item.status]?.label || item.status }}</StatusBadge></Link></div>
                    </UiCard>
                </div>

                <UiCard class="h-fit">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 font-extrabold text-brand-800">{{ customerName().split(/\s+/).map((part) => part[0]).join('').slice(0, 2).toUpperCase() }}</span>
                    <h2 class="mt-4 break-words text-lg font-extrabold text-ink">{{ customerName() }}</h2>
                    <dl class="mt-5 space-y-4"><div><dt class="flex items-center gap-3 text-xs font-bold uppercase text-muted"><Mail :size="18" class="flex-none text-muted" aria-hidden="true" />E-mail</dt><dd class="ml-[30px] mt-1 break-all text-sm text-ink">{{ customerEmail() }}</dd></div><div><dt class="flex items-center gap-3 text-xs font-bold uppercase text-muted"><Phone :size="18" class="flex-none text-muted" aria-hidden="true" />Telefon</dt><dd class="ml-[30px] mt-1 text-sm text-ink">{{ customerPhone() }}</dd></div><div><dt class="flex items-center gap-3 text-xs font-bold uppercase text-muted"><UserRound :size="18" class="flex-none text-muted" aria-hidden="true" />Typ</dt><dd class="ml-[30px] mt-1 text-sm text-ink">{{ booking.user_id ? 'Registrovaný zákazník' : 'Host bez účtu' }}</dd></div></dl>
                </UiCard>
            </div>
        </div>

        <Modal :show="showCancel" max-width="md" @close="showCancel = false"><form @submit.prevent="cancelBooking"><div class="p-5 sm:p-6"><h2 class="text-lg font-extrabold text-ink">Zrušit rezervaci?</h2><p class="mt-2 text-sm leading-6 text-muted">Zákazník dostane oznámení. Zrušenou rezervaci už nelze obnovit.</p><div class="mt-5"><InputLabel for="cancel-reason" value="Důvod pro zákazníka" /><textarea id="cancel-reason" v-model="cancellationForm.cancellation_reason" rows="4" maxlength="500" required class="ui-field mt-2 resize-y" placeholder="Například nemoc nebo provozní důvody" /><InputError class="mt-2" :message="cancellationForm.errors.cancellation_reason" /></div></div><div class="flex flex-col-reverse gap-2 border-t border-line p-4 sm:flex-row sm:justify-end"><UiButton variant="secondary" @click="showCancel = false">Ponechat rezervaci</UiButton><UiButton type="submit" variant="danger" :loading="cancellationForm.processing">Zrušit rezervaci</UiButton></div></form></Modal>
        <Modal :show="showNotes" max-width="md" @close="showNotes = false"><form @submit.prevent="saveNotes"><div class="p-5 sm:p-6"><h2 class="text-lg font-extrabold text-ink">Přidat interní poznámku</h2><p class="mt-2 text-sm text-muted">Poznámka je viditelná pouze v účtu poskytovatele.</p><textarea v-model="notesForm.notes" rows="5" maxlength="1000" class="ui-field mt-5 resize-y" required /><InputError class="mt-2" :message="notesForm.errors.notes" /></div><div class="flex flex-col-reverse gap-2 border-t border-line p-4 sm:flex-row sm:justify-end"><UiButton variant="secondary" @click="showNotes = false">Zrušit</UiButton><UiButton type="submit" :loading="notesForm.processing">Uložit poznámku</UiButton></div></form></Modal>
    </VendorLayout>
</template>
