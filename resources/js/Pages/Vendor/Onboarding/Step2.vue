<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, ChevronLeft, Clock3, Info } from '@lucide/vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import UiButton from '@/Components/UiButton.vue';
import OnboardingStepLayout from './OnboardingStepLayout.vue';
import { readOnboardingDraft, saveOnboardingDraft, useOnboardingCopy } from './onboardingCopy';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    saved: { type: Object, default: null },
});

const copy = useOnboardingCopy();
const dayDefinitions = [
    { day_of_week: 1, labelKey: 'monday', closed: false },
    { day_of_week: 2, labelKey: 'tuesday', closed: false },
    { day_of_week: 3, labelKey: 'wednesday', closed: false },
    { day_of_week: 4, labelKey: 'thursday', closed: false },
    { day_of_week: 5, labelKey: 'friday', closed: false },
    { day_of_week: 6, labelKey: 'saturday', closed: true },
    { day_of_week: 0, labelKey: 'sunday', closed: true },
];

const rememberedStep = readOnboardingDraft().step2 ?? {};
const initial = Object.keys(rememberedStep).length > 0 ? rememberedStep : (props.saved ?? {});
const savedHours = Array.isArray(initial.business_hours) ? initial.business_hours : [];

const businessHours = dayDefinitions.map((day) => {
    const savedDay = savedHours.find((item) => Number(item.day_of_week) === day.day_of_week);
    const isClosed = savedDay
        ? [true, 1, '1'].includes(savedDay.is_closed)
        : day.closed;

    return {
        day_of_week: day.day_of_week,
        labelKey: day.labelKey,
        is_closed: isClosed,
        time_from: savedDay?.time_from ?? '09:00',
        time_to: savedDay?.time_to ?? '17:00',
    };
});

const form = useForm('ProviderOnboardingStep2', {
    category_id: initial.category_id ?? '',
    shop_name: initial.shop_name ?? '',
    description: initial.description ?? '',
    city: initial.city ?? '',
    address: initial.address ?? '',
    currency: initial.currency ?? 'CZK',
    business_hours: businessHours,
});

function submit() {
    const businessHoursPayload = form.business_hours.map((day) => ({
        day_of_week: day.day_of_week,
        is_closed: day.is_closed,
        time_from: day.is_closed ? null : day.time_from,
        time_to: day.is_closed ? null : day.time_to,
    }));

    saveOnboardingDraft('step2', {
        category_id: form.category_id,
        shop_name: form.shop_name,
        description: form.description,
        city: form.city,
        address: form.address,
        currency: form.currency,
        business_hours: businessHoursPayload,
    });

    form.transform((data) => ({
        ...data,
        business_hours: businessHoursPayload,
    })).post(route('vendor.onboarding.step2.store'), { preserveScroll: true });
}
</script>

<template>
    <Head :title="copy('step2Title')" />

    <OnboardingStepLayout :current-step="2" :title="copy('step2Title')" :intro="copy('step2Intro')">
        <form novalidate @submit.prevent="submit">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="category_id">{{ copy('category') }}</InputLabel>
                    <select
                        id="category_id"
                        v-model="form.category_id"
                        required
                        class="ui-field mt-2"
                        :aria-invalid="Boolean(form.errors.category_id)"
                        :aria-describedby="form.errors.category_id ? 'category-error' : undefined"
                    >
                        <option value="" disabled>{{ copy('chooseCategory') }}</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <InputError id="category-error" class="mt-2" :message="form.errors.category_id" />
                </div>

                <div>
                    <InputLabel for="shop_name">{{ copy('shopName') }}</InputLabel>
                    <TextInput
                        id="shop_name"
                        v-model="form.shop_name"
                        type="text"
                        autocomplete="organization"
                        required
                        class="mt-2"
                        :placeholder="copy('shopNamePlaceholder')"
                        :aria-invalid="Boolean(form.errors.shop_name)"
                        :aria-describedby="form.errors.shop_name ? 'shop-name-error' : undefined"
                    />
                    <InputError id="shop-name-error" class="mt-2" :message="form.errors.shop_name" />
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-end justify-between gap-3">
                        <InputLabel for="description">{{ copy('description') }}</InputLabel>
                        <span class="text-xs tabular-nums text-muted">{{ form.description.length }}/1000</span>
                    </div>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="5"
                        minlength="50"
                        maxlength="1000"
                        required
                        class="ui-field mt-2 min-h-32 resize-y"
                        :placeholder="copy('descriptionPlaceholder')"
                        :aria-invalid="Boolean(form.errors.description)"
                        :aria-describedby="form.errors.description ? 'description-error' : 'description-help'"
                    />
                    <InputError id="description-error" class="mt-2" :message="form.errors.description" />
                    <p id="description-help" class="mt-2 text-xs leading-5 text-muted">{{ copy('descriptionHint') }}</p>
                </div>

                <div>
                    <InputLabel for="city">{{ copy('city') }}</InputLabel>
                    <TextInput
                        id="city"
                        v-model="form.city"
                        type="text"
                        autocomplete="address-level2"
                        required
                        class="mt-2"
                        :placeholder="copy('cityPlaceholder')"
                        :aria-invalid="Boolean(form.errors.city)"
                        :aria-describedby="form.errors.city ? 'city-error' : undefined"
                    />
                    <InputError id="city-error" class="mt-2" :message="form.errors.city" />
                </div>

                <div>
                    <InputLabel for="address">{{ copy('address') }}</InputLabel>
                    <TextInput
                        id="address"
                        v-model="form.address"
                        type="text"
                        autocomplete="street-address"
                        required
                        class="mt-2"
                        :placeholder="copy('addressPlaceholder')"
                        :aria-invalid="Boolean(form.errors.address)"
                        :aria-describedby="form.errors.address ? 'address-error' : undefined"
                    />
                    <InputError id="address-error" class="mt-2" :message="form.errors.address" />
                </div>

                <div class="sm:col-span-2 sm:max-w-xs">
                    <InputLabel for="currency">{{ copy('currency') }}</InputLabel>
                    <select
                        id="currency"
                        v-model="form.currency"
                        required
                        class="ui-field mt-2"
                        :aria-describedby="form.errors.currency ? 'currency-error' : 'currency-help'"
                        :aria-invalid="Boolean(form.errors.currency)"
                    >
                        <option value="CZK">CZK — Kč</option>
                        <option value="EUR">EUR — €</option>
                    </select>
                    <InputError id="currency-error" class="mt-2" :message="form.errors.currency" />
                    <p id="currency-help" class="mt-2 text-xs leading-5 text-muted">{{ copy('currencyHint') }}</p>
                </div>
            </div>

            <fieldset class="mt-8 border-t border-line pt-7">
                <legend class="text-lg font-extrabold text-ink">{{ copy('businessHours') }}</legend>
                <p class="mt-1 flex items-start gap-2 text-sm leading-6 text-muted">
                    <Clock3 class="mt-0.5 shrink-0 text-brand-700" :size="17" aria-hidden="true" />
                    <span>{{ copy('businessHoursHint') }}</span>
                </p>
                <InputError class="mt-2" :message="form.errors.business_hours" />

                <div class="mt-5 overflow-hidden rounded-2xl border border-line bg-white">
                    <div
                        v-for="(day, index) in form.business_hours"
                        :key="day.day_of_week"
                        class="grid gap-3 border-b border-line p-4 last:border-b-0 sm:grid-cols-[minmax(8rem,1fr)_auto_minmax(15rem,1.4fr)] sm:items-center"
                    >
                        <span class="text-sm font-bold text-ink">{{ copy(day.labelKey) }}</span>

                        <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-xl px-1 text-sm font-semibold text-muted">
                            <input
                                v-model="day.is_closed"
                                type="checkbox"
                                class="h-5 w-5 rounded border-line text-brand-600 focus:ring-brand-600"
                            />
                            {{ copy('closed') }}
                        </label>

                        <div v-if="!day.is_closed" class="grid grid-cols-2 gap-3">
                            <label class="text-xs font-bold text-muted">
                                <span>{{ copy('opens') }}</span>
                                <input
                                    v-model="day.time_from"
                                    type="time"
                                    required
                                    class="ui-field mt-1"
                                    :aria-invalid="Boolean(form.errors[`business_hours.${index}.time_from`])"
                                />
                            </label>
                            <label class="text-xs font-bold text-muted">
                                <span>{{ copy('closes') }}</span>
                                <input
                                    v-model="day.time_to"
                                    type="time"
                                    required
                                    class="ui-field mt-1"
                                    :aria-invalid="Boolean(form.errors[`business_hours.${index}.time_to`])"
                                />
                            </label>
                        </div>
                        <p v-else class="flex min-h-11 items-center text-sm text-muted sm:justify-end">{{ copy('closed') }}</p>

                        <div v-if="form.errors[`business_hours.${index}.time_from`] || form.errors[`business_hours.${index}.time_to`]" class="sm:col-start-3">
                            <InputError :message="form.errors[`business_hours.${index}.time_from`] || form.errors[`business_hours.${index}.time_to`]" />
                        </div>
                    </div>
                </div>

                <p class="mt-4 flex items-start gap-2 rounded-xl bg-brand-50 px-4 py-3 text-sm leading-6 text-brand-900">
                    <Info class="mt-0.5 shrink-0" :size="17" aria-hidden="true" />
                    <span>{{ copy('businessHoursHint') }}</span>
                </p>
            </fieldset>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                <UiButton :href="route('vendor.onboarding.step1')" variant="secondary">
                    <ChevronLeft :size="18" aria-hidden="true" />
                    {{ copy('back') }}
                </UiButton>
                <UiButton type="submit" :loading="form.processing" :disabled="form.processing">
                    {{ form.processing ? copy('saving') : copy('continue') }}
                    <ArrowRight v-if="!form.processing" :size="18" aria-hidden="true" />
                </UiButton>
            </div>
        </form>
    </OnboardingStepLayout>
</template>
