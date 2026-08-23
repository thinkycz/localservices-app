<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import UiCard from '@/Components/UiCard.vue';
import { ImagePlus, MapPin, Store, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    categories: { type: Array, required: true },
    hours: { type: Array, required: true },
    existingImage: { type: String, default: null },
});

const localPreview = ref(null);
const preview = computed(() => localPreview.value || (props.form.remove_image ? null : props.existingImage));

function chooseImage(event) {
    const file = event.target.files?.[0] || null;
    if (localPreview.value) URL.revokeObjectURL(localPreview.value);
    localPreview.value = file ? URL.createObjectURL(file) : null;
    props.form.image = file;
    if (file) props.form.remove_image = false;
}

function removeImage() {
    if (localPreview.value) URL.revokeObjectURL(localPreview.value);
    localPreview.value = null;
    props.form.image = null;
    props.form.remove_image = true;
}

onBeforeUnmount(() => {
    if (localPreview.value) URL.revokeObjectURL(localPreview.value);
});
</script>

<template>
    <div class="space-y-5">
        <UiCard>
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><Store :size="21" aria-hidden="true" /></span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-extrabold text-ink">Základní údaje</h2>
                    <p class="mt-1 text-sm text-muted">Informace, podle kterých zákazníci provozovnu poznají.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <InputLabel for="shop-name" value="Název provozovny" />
                    <TextInput id="shop-name" v-model="form.name" class="mt-2 block w-full" required autocomplete="organization" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div>
                    <InputLabel for="shop-category" value="Kategorie" />
                    <select id="shop-category" v-model="form.category_id" class="ui-field mt-2" required>
                        <option value="" disabled>Vyberte kategorii</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.category_id" />
                </div>
                <div>
                    <InputLabel for="shop-currency" value="Měna" />
                    <select id="shop-currency" v-model="form.currency" class="ui-field mt-2" required>
                        <option value="CZK">CZK — česká koruna</option>
                        <option value="EUR">EUR — euro</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.currency" />
                </div>
                <div class="sm:col-span-2">
                    <InputLabel for="shop-description" value="Popis" />
                    <textarea id="shop-description" v-model="form.description" rows="5" class="ui-field mt-2 resize-y" placeholder="Co nabízíte a čím je vaše provozovna výjimečná?" />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>
            </div>
        </UiCard>

        <UiCard>
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><MapPin :size="21" aria-hidden="true" /></span>
                <div>
                    <h2 class="text-base font-extrabold text-ink">Místo poskytování</h2>
                    <p class="mt-1 text-sm text-muted">Uveďte českou adresu, nebo označte službu jako online.</p>
                </div>
            </div>

            <label class="mt-6 flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-line px-4 py-3 text-sm font-semibold text-ink">
                <input v-model="form.is_online_only" type="checkbox" class="rounded border-line text-brand-600 focus:ring-brand-600" />
                Služby poskytuji pouze online
            </label>

            <div class="mt-5 grid gap-5 sm:grid-cols-2" :class="form.is_online_only ? 'opacity-50' : ''">
                <div class="sm:col-span-2">
                    <InputLabel for="shop-address" value="Ulice a číslo" />
                    <TextInput id="shop-address" v-model="form.address" class="mt-2 block w-full" :disabled="form.is_online_only" autocomplete="street-address" placeholder="Vinohradská 12" />
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
                <div>
                    <InputLabel for="shop-city" value="Město" />
                    <TextInput id="shop-city" v-model="form.city" class="mt-2 block w-full" :disabled="form.is_online_only" autocomplete="address-level2" placeholder="Praha" />
                    <InputError class="mt-2" :message="form.errors.city" />
                </div>
                <div>
                    <InputLabel for="shop-state" value="Kraj" />
                    <TextInput id="shop-state" v-model="form.state" class="mt-2 block w-full" :disabled="form.is_online_only" autocomplete="address-level1" placeholder="Hlavní město Praha" />
                    <InputError class="mt-2" :message="form.errors.state" />
                </div>
            </div>
        </UiCard>

        <UiCard>
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-700"><ImagePlus :size="21" aria-hidden="true" /></span>
                <div>
                    <h2 class="text-base font-extrabold text-ink">Úvodní fotografie</h2>
                    <p class="mt-1 text-sm text-muted">JPG, PNG nebo WebP do 5 MB. Obrázek upravíme na poměr 16 : 9.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <InputLabel for="shop-image" value="Vybrat soubor" />
                    <input id="shop-image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-line bg-white p-2.5 text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:font-bold file:text-brand-800" @change="chooseImage" />
                    <InputError class="mt-2" :message="form.errors.image" />
                </div>
                <button v-if="preview" type="button" class="ui-button ui-button-secondary" @click="removeImage"><X :size="17" /> Odebrat fotografii</button>
            </div>
            <img v-if="preview" :src="preview" alt="Náhled úvodní fotografie" class="mt-5 aspect-video w-full max-w-xl rounded-2xl border border-line object-cover" />
        </UiCard>

        <UiCard>
            <h2 class="text-base font-extrabold text-ink">Otevírací doba</h2>
            <p class="mt-1 text-sm text-muted">Zavřené dny vypněte. Časy se používají při výpočtu volných termínů.</p>

            <div class="mt-6 divide-y divide-line rounded-2xl border border-line">
                <div v-for="hour in hours" :key="hour.day_of_week" class="grid gap-3 p-4 sm:grid-cols-[8rem_1fr] sm:items-center">
                    <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm font-bold text-ink">
                        <input v-model="hour.enabled" type="checkbox" class="rounded border-line text-brand-600 focus:ring-brand-600" />
                        {{ hour.label }}
                    </label>
                    <div v-if="hour.enabled" class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
                        <input v-model="hour.time_from" type="time" class="ui-field min-w-0" :aria-label="`Otevírá v ${hour.label}`" />
                        <span class="text-sm text-muted">–</span>
                        <input v-model="hour.time_to" type="time" class="ui-field min-w-0" :aria-label="`Zavírá v ${hour.label}`" />
                    </div>
                    <p v-else class="text-sm font-semibold text-muted">Zavřeno</p>
                    <InputError class="sm:col-start-2" :message="form.errors[`business_hours.${hour.day_of_week}.time_from`] || form.errors[`business_hours.${hour.day_of_week}.time_to`]" />
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h2 class="text-base font-extrabold text-ink">Viditelnost</h2>
            <label class="mt-4 flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border border-line px-4 py-3">
                <input v-model="form.is_available" type="checkbox" class="mt-0.5 rounded border-line text-brand-600 focus:ring-brand-600" />
                <span>
                    <span class="block text-sm font-bold text-ink">Provozovna je aktivní</span>
                    <span class="mt-1 block text-sm text-muted">Zákazníci ji uvidí ve vyhledávání a mohou si rezervovat termín.</span>
                </span>
            </label>
        </UiCard>
    </div>
</template>
