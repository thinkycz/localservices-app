<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, CalendarDays, Star } from '@lucide/vue';

const props = defineProps({ booking: Object });
const page = usePage();
const isCzech = computed(() => page.props.locale === 'cs');
const copy = (cs, en) => isCzech.value ? cs : en;
const form = useForm({ booking_id: props.booking.id, rating: 0, comment: '', tags: [] });
const ratingError = ref('');
const hoverRating = ref(0);

const availableTags = [
    { value: 'Professional', cs: 'Profesionální', en: 'Professional' },
    { value: 'On-time', cs: 'Dochvilné', en: 'On time' },
    { value: 'Quality Shop', cs: 'Kvalitní služba', en: 'Quality service' },
    { value: 'Friendly', cs: 'Příjemné jednání', en: 'Friendly' },
    { value: 'Clean', cs: 'Čisté prostředí', en: 'Clean' },
    { value: 'Good Value', cs: 'Dobrá hodnota', en: 'Good value' },
    { value: 'Expert', cs: 'Odborné', en: 'Expert' },
    { value: 'Recommended', cs: 'Doporučuji', en: 'Recommended' },
];
const ratingLabels = computed(() => isCzech.value
    ? ['', 'Velmi špatné', 'Spíše špatné', 'Dobré', 'Velmi dobré', 'Výborné']
    : ['', 'Very poor', 'Poor', 'Good', 'Very good', 'Excellent']);
const locale = computed(() => isCzech.value ? 'cs-CZ' : 'en-GB');

const setRating = (rating) => { form.rating = rating; ratingError.value = ''; };
const toggleTag = (tag) => {
    form.tags = form.tags.includes(tag) ? form.tags.filter((item) => item !== tag) : [...form.tags, tag];
};
const submit = () => {
    if (!form.rating) {
        ratingError.value = copy('Vyberte počet hvězdiček.', 'Choose a star rating.');
        return;
    }
    form.post(route('reviews.store'));
};
const formatDate = (date) => new Intl.DateTimeFormat(locale.value, { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(`${date}T12:00:00`));
const formatTime = (time) => String(time ?? '').slice(0, 5);
</script>

<template>
    <AppLayout>
        <Head :title="copy('Napsat recenzi', 'Write a review')" />

        <section class="border-b border-line bg-white">
            <div class="ui-container py-8 sm:py-10">
                <Link :href="route('bookings.index')" class="mb-4 inline-flex min-h-11 items-center gap-2 rounded-xl text-sm font-semibold text-muted hover:text-brand-700"><ArrowLeft :size="18" aria-hidden="true" />{{ copy('Zpět na rezervace', 'Back to bookings') }}</Link>
                <h1 class="text-3xl font-extrabold tracking-tight text-ink">{{ copy('Jaká byla vaše zkušenost?', 'How was your experience?') }}</h1>
                <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Hodnocení pomůže ostatním vybrat vhodnou službu.', 'Your review helps others choose the right service.') }}</p>
            </div>
        </section>

        <main id="main-content" class="ui-container py-8 sm:py-10">
            <div class="mx-auto max-w-2xl space-y-5">
                <section class="ui-card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="truncate font-bold text-ink">{{ booking.shop.name }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ booking.service.name }}</p>
                    </div>
                    <p class="flex shrink-0 items-center gap-2 text-sm text-muted"><CalendarDays :size="18" aria-hidden="true" />{{ formatDate(booking.booking_date) }} · {{ formatTime(booking.start_time) }}</p>
                </section>

                <form class="ui-card overflow-hidden" @submit.prevent="submit">
                    <div class="space-y-7 p-5 sm:p-7">
                        <fieldset>
                            <legend class="text-sm font-bold text-ink">{{ copy('Celkové hodnocení', 'Overall rating') }} <span class="text-danger" aria-hidden="true">*</span></legend>
                            <div class="mt-3 flex flex-wrap items-center gap-1" role="radiogroup" :aria-label="copy('Počet hvězdiček', 'Star rating')">
                                <button v-for="star in 5" :key="star" type="button" role="radio" :aria-checked="form.rating === star" :aria-label="`${star} ${copy('z 5 hvězdiček', 'out of 5 stars')}`" class="flex h-11 w-11 items-center justify-center rounded-xl hover:bg-accent/10" @click="setRating(star)" @mouseenter="hoverRating = star" @mouseleave="hoverRating = 0">
                                    <Star :size="31" :stroke-width="1.7" :class="(hoverRating ? star <= hoverRating : star <= form.rating) ? 'fill-accent text-accent' : 'text-line'" aria-hidden="true" />
                                </button>
                                <span v-if="form.rating" class="ml-2 text-sm font-semibold text-ink">{{ ratingLabels[form.rating] }}</span>
                            </div>
                            <p v-if="ratingError" role="alert" class="mt-2 text-sm font-medium text-danger">{{ ratingError }}</p>
                            <InputError class="mt-2" :message="form.errors.rating" />
                        </fieldset>

                        <div>
                            <label for="comment" class="mb-1.5 block text-sm font-bold text-ink">{{ copy('Popište svou zkušenost', 'Describe your experience') }} <span class="text-danger" aria-hidden="true">*</span></label>
                            <p id="comment-help" class="mb-2 text-sm leading-6 text-muted">{{ copy('Zaměřte se na průběh služby, komunikaci a výsledek.', 'Focus on the service, communication, and result.') }}</p>
                            <textarea id="comment" v-model="form.comment" rows="6" required minlength="10" maxlength="1000" aria-describedby="comment-help" class="ui-field resize-y" :class="{ 'border-danger': form.errors.comment }" :placeholder="copy('Co proběhlo dobře a co by mohlo být lepší?', 'What went well, and what could be better?')"></textarea>
                            <div class="mt-1.5 flex items-start justify-between gap-4"><InputError :message="form.errors.comment" /><span class="ml-auto text-xs text-muted">{{ form.comment.length }}/1000</span></div>
                        </div>

                        <fieldset>
                            <legend class="text-sm font-bold text-ink">{{ copy('Co službu vystihuje?', 'What describes the service?') }} <span class="font-normal text-muted">({{ copy('volitelné', 'optional') }})</span></legend>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button v-for="tag in availableTags" :key="tag.value" type="button" :aria-pressed="form.tags.includes(tag.value)" class="min-h-11 rounded-xl border px-3.5 py-2 text-sm font-semibold transition" :class="form.tags.includes(tag.value) ? 'border-brand-600 bg-brand-600 text-white' : 'border-line bg-white text-muted hover:border-brand-300 hover:text-brand-700'" @click="toggleTag(tag.value)">{{ isCzech ? tag.cs : tag.en }}</button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.tags" />
                        </fieldset>
                    </div>

                    <footer class="border-t border-line bg-canvas/60 p-5 sm:flex sm:items-center sm:justify-between sm:gap-5 sm:px-7">
                        <p class="text-xs leading-5 text-muted">{{ copy('Recenze bude veřejná a spojená s touto dokončenou rezervací.', 'Your review will be public and linked to this completed booking.') }}</p>
                        <div class="mt-4 flex flex-col-reverse gap-2 sm:mt-0 sm:flex-row">
                            <Link :href="route('bookings.index')" class="ui-button ui-button-secondary">{{ copy('Zrušit', 'Cancel') }}</Link>
                            <button type="submit" :disabled="form.processing || form.comment.length < 10" class="ui-button ui-button-primary">{{ form.processing ? copy('Odesílání…', 'Submitting…') : copy('Odeslat recenzi', 'Submit review') }}</button>
                        </div>
                    </footer>
                </form>
            </div>
        </main>
    </AppLayout>
</template>
