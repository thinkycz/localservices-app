<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, Info } from '@lucide/vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import UiButton from '@/Components/UiButton.vue';
import OnboardingStepLayout from './OnboardingStepLayout.vue';
import { readOnboardingDraft, saveOnboardingDraft, useOnboardingCopy } from './onboardingCopy';

const props = defineProps({
    user: { type: Object, required: true },
});

const copy = useOnboardingCopy();
const saved = readOnboardingDraft().step1 ?? {};
const form = useForm('ProviderOnboardingStep1', {
    business_name: saved.business_name ?? props.user.name ?? '',
    business_phone: saved.business_phone ?? props.user.phone ?? '',
    business_email: saved.business_email ?? props.user.email ?? '',
});

function submit() {
    saveOnboardingDraft('step1', {
        business_name: form.business_name,
        business_phone: form.business_phone,
        business_email: form.business_email,
    });

    form.post(route('vendor.onboarding.step1.store'), { preserveScroll: true });
}
</script>

<template>
    <Head :title="copy('step1Title')" />

    <OnboardingStepLayout :current-step="1" :title="copy('step1Title')" :intro="copy('step1Intro')">
        <form novalidate @submit.prevent="submit">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <InputLabel for="business_name">{{ copy('businessName') }}</InputLabel>
                    <TextInput
                        id="business_name"
                        v-model="form.business_name"
                        type="text"
                        autocomplete="organization"
                        required
                        autofocus
                        class="mt-2"
                        :placeholder="copy('businessNamePlaceholder')"
                        :aria-invalid="Boolean(form.errors.business_name)"
                        :aria-describedby="form.errors.business_name ? 'business-name-error' : undefined"
                    />
                    <InputError id="business-name-error" class="mt-2" :message="form.errors.business_name" />
                </div>

                <div>
                    <InputLabel for="business_phone">{{ copy('businessPhone') }}</InputLabel>
                    <TextInput
                        id="business_phone"
                        v-model="form.business_phone"
                        type="tel"
                        inputmode="tel"
                        autocomplete="tel"
                        required
                        class="mt-2"
                        :placeholder="copy('businessPhonePlaceholder')"
                        :aria-invalid="Boolean(form.errors.business_phone)"
                        :aria-describedby="form.errors.business_phone ? 'business-phone-error' : 'contact-details-help'"
                    />
                    <InputError id="business-phone-error" class="mt-2" :message="form.errors.business_phone" />
                </div>

                <div>
                    <InputLabel for="business_email">{{ copy('businessEmail') }}</InputLabel>
                    <TextInput
                        id="business_email"
                        v-model="form.business_email"
                        type="email"
                        inputmode="email"
                        autocomplete="email"
                        required
                        class="mt-2"
                        :placeholder="copy('businessEmailPlaceholder')"
                        :aria-invalid="Boolean(form.errors.business_email)"
                        :aria-describedby="form.errors.business_email ? 'business-email-error' : 'contact-details-help'"
                    />
                    <InputError id="business-email-error" class="mt-2" :message="form.errors.business_email" />
                </div>
            </div>

            <p id="contact-details-help" class="mt-5 flex items-start gap-2 rounded-xl bg-brand-50 px-4 py-3 text-sm leading-6 text-brand-900">
                <Info class="mt-0.5 shrink-0" :size="17" aria-hidden="true" />
                <span>{{ copy('customerVisible') }}</span>
            </p>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                <UiButton :href="route('vendor.onboarding.index')" variant="ghost">
                    {{ copy('cancel') }}
                </UiButton>
                <UiButton type="submit" :loading="form.processing" :disabled="form.processing">
                    {{ form.processing ? copy('saving') : copy('continue') }}
                    <ArrowRight v-if="!form.processing" :size="18" aria-hidden="true" />
                </UiButton>
            </div>
        </form>
    </OnboardingStepLayout>
</template>
