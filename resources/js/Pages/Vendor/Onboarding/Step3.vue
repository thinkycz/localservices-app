<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, ChevronLeft, CirclePlus, Info, Trash2 } from '@lucide/vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import UiButton from '@/Components/UiButton.vue';
import OnboardingStepLayout from './OnboardingStepLayout.vue';
import {
    clearOnboardingDraft,
    readOnboardingDraft,
    saveOnboardingDraft,
    useOnboardingCopy,
} from './onboardingCopy';

const copy = useOnboardingCopy();
const draft = readOnboardingDraft();
const savedServices = Array.isArray(draft.step3?.services) && draft.step3.services.length > 0
    ? draft.step3.services
    : [{ name: '', description: '', price: '', duration_minutes: 60 }];

const form = useForm('ProviderOnboardingStep3', {
    services: savedServices.map((service) => ({
        name: service.name ?? '',
        description: service.description ?? '',
        price: service.price ?? '',
        duration_minutes: service.duration_minutes ?? 60,
    })),
});

const currency = computed(() => draft.step2?.currency ?? 'CZK');
const summaryRows = computed(() => [
    { label: copy('businessName'), value: draft.step1?.business_name },
    { label: copy('shopName'), value: draft.step2?.shop_name },
    { label: copy('city'), value: draft.step2?.city },
    { label: copy('currency'), value: draft.step2?.currency },
].filter((item) => item.value));

function addService() {
    form.services.push({ name: '', description: '', price: '', duration_minutes: 60 });
}

function removeService(index) {
    if (form.services.length === 1) return;
    form.services.splice(index, 1);
}

function submit() {
    const services = form.services.map((service) => ({
        name: service.name,
        description: service.description,
        price: service.price,
        duration_minutes: service.duration_minutes,
    }));

    saveOnboardingDraft('step3', { services });
    form.transform(() => ({ services })).post(route('vendor.onboarding.step3.store'), {
        preserveScroll: true,
        onSuccess: () => clearOnboardingDraft(),
    });
}
</script>

<template>
    <Head :title="copy('step3Title')" />

    <OnboardingStepLayout :current-step="3" :title="copy('step3Title')" :intro="copy('step3Intro')">
        <form novalidate @submit.prevent="submit">
            <InputError class="mb-4" :message="form.errors.services" />

            <div class="space-y-5">
                <fieldset
                    v-for="(service, index) in form.services"
                    :key="index"
                    class="relative rounded-2xl border border-line bg-white p-4 sm:p-5"
                >
                    <legend class="px-2 text-sm font-extrabold text-ink">
                        {{ copy('service') }} {{ index + 1 }}
                    </legend>

                    <button
                        v-if="form.services.length > 1"
                        type="button"
                        class="ui-icon-button absolute right-3 top-3 text-danger hover:bg-red-50 hover:text-danger"
                        :aria-label="`${copy('removeService')} ${index + 1}`"
                        @click="removeService(index)"
                    >
                        <Trash2 :size="19" aria-hidden="true" />
                    </button>

                    <div class="mt-2 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2 sm:pr-12">
                            <InputLabel :for="`service-name-${index}`">{{ copy('serviceName') }}</InputLabel>
                            <TextInput
                                :id="`service-name-${index}`"
                                v-model="service.name"
                                type="text"
                                required
                                class="mt-2"
                                :placeholder="copy('serviceNamePlaceholder')"
                                :aria-invalid="Boolean(form.errors[`services.${index}.name`])"
                                :aria-describedby="form.errors[`services.${index}.name`] ? `service-name-error-${index}` : undefined"
                            />
                            <InputError :id="`service-name-error-${index}`" class="mt-2" :message="form.errors[`services.${index}.name`]" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel :for="`service-description-${index}`">{{ copy('serviceDescription') }}</InputLabel>
                            <textarea
                                :id="`service-description-${index}`"
                                v-model="service.description"
                                rows="3"
                                maxlength="500"
                                required
                                class="ui-field mt-2 min-h-24 resize-y"
                                :placeholder="copy('serviceDescriptionPlaceholder')"
                                :aria-invalid="Boolean(form.errors[`services.${index}.description`])"
                                :aria-describedby="form.errors[`services.${index}.description`] ? `service-description-error-${index}` : undefined"
                            />
                            <InputError :id="`service-description-error-${index}`" class="mt-2" :message="form.errors[`services.${index}.description`]" />
                        </div>

                        <div>
                            <InputLabel :for="`service-price-${index}`">{{ copy('price') }} ({{ currency }})</InputLabel>
                            <div class="relative mt-2">
                                <TextInput
                                    :id="`service-price-${index}`"
                                    v-model.number="service.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    inputmode="decimal"
                                    required
                                    class="pr-16"
                                    :placeholder="copy('priceExample')"
                                    :aria-invalid="Boolean(form.errors[`services.${index}.price`])"
                                    :aria-describedby="form.errors[`services.${index}.price`] ? `service-price-error-${index}` : undefined"
                                />
                                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-xs font-bold text-muted">{{ currency }}</span>
                            </div>
                            <InputError :id="`service-price-error-${index}`" class="mt-2" :message="form.errors[`services.${index}.price`]" />
                        </div>

                        <div>
                            <InputLabel :for="`service-duration-${index}`">{{ copy('duration') }}</InputLabel>
                            <select
                                :id="`service-duration-${index}`"
                                v-model.number="service.duration_minutes"
                                required
                                class="ui-field mt-2"
                                :aria-invalid="Boolean(form.errors[`services.${index}.duration_minutes`])"
                                :aria-describedby="form.errors[`services.${index}.duration_minutes`] ? `service-duration-error-${index}` : undefined"
                            >
                                <option disabled value="">{{ copy('chooseDuration') }}</option>
                                <option v-for="minutes in [15, 30, 45, 60, 90, 120, 180, 240, 480]" :key="minutes" :value="minutes">
                                    {{ minutes }} {{ copy('minutes') }}
                                </option>
                            </select>
                            <InputError :id="`service-duration-error-${index}`" class="mt-2" :message="form.errors[`services.${index}.duration_minutes`]" />
                        </div>
                    </div>
                </fieldset>
            </div>

            <button
                type="button"
                class="mt-5 flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-brand-300 bg-brand-50/60 px-4 text-sm font-bold text-brand-800 transition hover:border-brand-500 hover:bg-brand-50"
                @click="addService"
            >
                <CirclePlus :size="20" aria-hidden="true" />
                {{ copy('addService') }}
            </button>

            <section class="mt-8 rounded-2xl border border-line bg-canvas p-4 sm:p-5" :aria-labelledby="'final-review-title'">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-700">
                        <Check :size="19" aria-hidden="true" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h2 id="final-review-title" class="text-base font-extrabold text-ink">{{ copy('reviewTitle') }}</h2>
                        <dl v-if="summaryRows.length" class="mt-3 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                            <div v-for="item in summaryRows" :key="item.label">
                                <dt class="text-xs font-bold uppercase tracking-wide text-muted">{{ item.label }}</dt>
                                <dd class="mt-0.5 truncate text-sm font-semibold text-ink">{{ item.value }}</dd>
                            </div>
                        </dl>
                        <p v-else class="mt-2 flex items-start gap-2 text-sm leading-6 text-muted">
                            <Info class="mt-0.5 shrink-0" :size="16" aria-hidden="true" />
                            {{ copy('reviewFallback') }}
                        </p>
                    </div>
                </div>
            </section>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                <UiButton :href="route('vendor.onboarding.step2')" variant="secondary">
                    <ChevronLeft :size="18" aria-hidden="true" />
                    {{ copy('back') }}
                </UiButton>
                <UiButton type="submit" :loading="form.processing" :disabled="form.processing">
                    {{ form.processing ? copy('finishing') : copy('finish') }}
                    <Check v-if="!form.processing" :size="18" aria-hidden="true" />
                </UiButton>
            </div>
        </form>
    </OnboardingStepLayout>
</template>
