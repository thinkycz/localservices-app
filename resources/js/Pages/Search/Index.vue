<template>
  <AppLayout>
    <Head title="Search Services" />
    
    <div class="bg-gray-50 min-h-screen">
      <!-- Search Header -->
      <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div class="flex flex-col md:flex-row gap-4">
            <!-- Search Input -->
            <div class="flex-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Search for services..."
                @input="debouncedSearch"
                @focus="showSuggestions = true"
              />
              
              <!-- Search Suggestions -->
              <div v-if="showSuggestions && suggestions.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto max-h-60 focus:outline-none sm:text-sm">
                <div
                  v-for="suggestion in suggestions"
                  :key="suggestion.id"
                  class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                  @click="selectSuggestion(suggestion)"
                >
                  <div class="flex items-center">
                    <span class="font-medium block truncate">{{ suggestion.name }}</span>
                    <span class="ml-2 text-gray-500 text-sm">{{ suggestion.category }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sort Dropdown -->
            <select
              v-model="filters.sort"
              class="block w-full md:w-48 pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg"
              @change="applyFilters"
            >
              <option value="recommended">Recommended</option>
              <option value="highest_rated">Highest Rated</option>
              <option value="cheapest">Price: Low to High</option>
              <option value="newest">Newest</option>
            </select>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
          <!-- Filters Sidebar -->
          <div class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
              <!-- Categories -->
              <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Categories</h3>
                <div class="space-y-2">
                  <label
                    v-for="category in categories"
                    :key="category.id"
                    class="flex items-center"
                  >
                    <input
                      type="checkbox"
                      :value="category.slug"
                      v-model="selectedCategories"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                      @change="applyFilters"
                    />
                    <span class="ml-2 text-sm text-gray-600">
                      {{ category.name }}
                      <span class="text-gray-400">({{ category.services_count }})</span>
                    </span>
                  </label>
                </div>
              </div>

              <!-- Price Range -->
              <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Price Range</h3>
                <div class="space-y-2">
                  <label
                    v-for="price in priceRanges"
                    :key="price.value"
                    class="flex items-center"
                  >
                    <input
                      type="checkbox"
                      :value="price.value"
                      v-model="selectedPriceRanges"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                      @change="applyFilters"
                    />
                    <span class="ml-2 text-sm text-gray-600">{{ price.label }}</span>
                  </label>
                </div>
              </div>

              <!-- Rating -->
              <div>
                <h3 class="text-sm font-medium text-gray-900 mb-3">Minimum Rating</h3>
                <select
                  v-model="filters.min_rating"
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  @change="applyFilters"
                >
                  <option value="">Any Rating</option>
                  <option value="4">4+ Stars</option>
                  <option value="4.5">4.5+ Stars</option>
                  <option value="5">5 Stars</option>
                </select>
              </div>

              <!-- Location -->
              <div v-if="cities.length > 0">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Location</h3>
                <select
                  v-model="filters.city"
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  @change="applyFilters"
                >
                  <option value="">All Locations</option>
                  <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                </select>
              </div>

              <!-- Clear Filters -->
              <button
                @click="clearFilters"
                class="w-full text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium"
              >
                Clear All Filters
              </button>
            </div>
          </div>

          <!-- Results -->
          <div class="flex-1">
            <!-- Results Header -->
            <div class="flex items-center justify-between mb-6">
              <p class="text-sm text-gray-600">
                {{ services.total }} results found
                <span v-if="searchQuery" class="font-medium">for "{{ searchQuery }}"</span>
              </p>
            </div>

            <!-- Services Grid -->
            <div v-if="services.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <ServiceCard
                v-for="service in services.data"
                :key="service.id"
                :service="service"
              />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No services found</h3>
              <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
            </div>

            <!-- Pagination -->
            <div v-if="services.data.length > 0" class="mt-8">
              <AppPagination :links="services.links" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServiceCard from '@/Components/ServiceCard.vue';
import AppPagination from '@/Components/AppPagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
  services: Object,
  categories: Array,
  cities: Array,
  filters: Object,
  suggestions: Array,
});

const searchQuery = ref(props.filters.q || '');
const showSuggestions = ref(false);
const suggestions = ref(props.suggestions || []);

const selectedCategories = ref(props.filters.categories || []);
const selectedPriceRanges = ref(props.filters.price_range || []);

const priceRanges = [
  { value: 1, label: '$' },
  { value: 2, label: '$$' },
  { value: 3, label: '$$$' },
  { value: 4, label: '$$$$' },
];

const filters = ref({
  sort: props.filters.sort || 'recommended',
  min_rating: props.filters.min_rating || '',
  city: props.filters.city || '',
});

// Debounced search for suggestions
const debouncedSearch = debounce(async () => {
  if (searchQuery.value.length < 2) {
    suggestions.value = [];
    return;
  }

  try {
    const response = await fetch(`/search/suggestions?q=${encodeURIComponent(searchQuery.value)}`);
    const data = await response.json();
    suggestions.value = data.services || [];
    showSuggestions.value = true;
  } catch (error) {
    console.error('Failed to fetch suggestions:', error);
  }
}, 300);

const selectSuggestion = (suggestion) => {
  searchQuery.value = suggestion.name;
  showSuggestions.value = false;
  applyFilters();
};

const applyFilters = () => {
  router.get('/search', {
    q: searchQuery.value,
    categories: selectedCategories.value,
    price_range: selectedPriceRanges.value,
    min_rating: filters.value.min_rating,
    city: filters.value.city,
    sort: filters.value.sort,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedCategories.value = [];
  selectedPriceRanges.value = [];
  filters.value = {
    sort: 'recommended',
    min_rating: '',
    city: '',
  };
  applyFilters();
};

// Close suggestions when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      showSuggestions.value = false;
    }
  });
});
</script>
