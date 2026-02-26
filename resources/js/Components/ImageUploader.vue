<template>
  <div class="image-uploader">
    <!-- Drag and Drop Zone -->
    <div
      class="drop-zone"
      :class="{ 'drag-over': isDragging, 'has-error': error }"
      @dragenter.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @dragover.prevent
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/jpeg,image/png,image/jpg,image/webp"
        class="hidden"
        @change="handleFileSelect"
      />
      
      <div v-if="!isUploading" class="drop-zone-content">
        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <p class="drop-text">
          <span class="font-medium">Click to upload</span> or drag and drop
        </p>
        <p class="file-types">PNG, JPG, WEBP up to 5MB each</p>
      </div>
      
      <div v-else class="uploading-indicator">
        <div class="spinner"></div>
        <p>Uploading {{ uploadProgress }}%...</p>
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="error-message">{{ error }}</p>

    <!-- Image Gallery -->
    <div v-if="images.length > 0" class="image-gallery">
      <draggable
        v-model="localImages"
        item-key="id"
        class="gallery-grid"
        ghost-class="ghost"
        @end="handleReorder"
      >
        <template #item="{ element: image }">
          <div class="gallery-item" :class="{ 'is-primary': image.is_primary }">
            <img :src="image.thumbnail_url || image.url" :alt="image.alt_text" />
            
            <!-- Primary Badge -->
            <div v-if="image.is_primary" class="primary-badge">
              Primary
            </div>
            
            <!-- Actions -->
            <div class="image-actions">
              <button
                v-if="!image.is_primary"
                @click.stop="setPrimary(image.id)"
                class="action-btn"
                title="Set as primary"
              >
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </button>
              <button
                @click.stop="deleteImage(image.id)"
                class="action-btn delete"
                title="Delete image"
              >
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios';

const props = defineProps({
  serviceId: {
    type: Number,
    required: true
  },
  initialImages: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:images', 'upload-success', 'upload-error']);

const fileInput = ref(null);
const isDragging = ref(false);
const isUploading = ref(false);
const uploadProgress = ref(0);
const error = ref('');
const localImages = ref([...props.initialImages]);

// Sync with parent
watch(() => props.initialImages, (newImages) => {
  localImages.value = [...newImages];
}, { deep: true });

watch(localImages, (newImages) => {
  emit('update:images', newImages);
}, { deep: true });

const images = computed(() => localImages.value);

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleDrop = (e) => {
  isDragging.value = false;
  const files = Array.from(e.dataTransfer.files).filter(file => 
    file.type.startsWith('image/')
  );
  uploadFiles(files);
};

const handleFileSelect = (e) => {
  const files = Array.from(e.target.files);
  uploadFiles(files);
  // Reset input
  e.target.value = '';
};

const uploadFiles = async (files) => {
  if (files.length === 0) return;
  
  // Validate files
  const maxSize = 5 * 1024 * 1024; // 5MB
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
  
  for (const file of files) {
    if (file.size > maxSize) {
      error.value = `File ${file.name} is too large. Max size is 5MB.`;
      return;
    }
    if (!validTypes.includes(file.type)) {
      error.value = `File ${file.name} is not a valid image type.`;
      return;
    }
  }

  error.value = '';
  isUploading.value = true;
  uploadProgress.value = 0;

  const formData = new FormData();
  files.forEach((file, index) => {
    formData.append(`images[${index}]`, file);
  });

  try {
    const response = await axios.post(
      `/api/services/${props.serviceId}/images`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
        }
      }
    );

    if (response.data.success) {
      localImages.value.push(...response.data.images);
      emit('upload-success', response.data.images);
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to upload images';
    emit('upload-error', err);
  } finally {
    isUploading.value = false;
    uploadProgress.value = 0;
  }
};

const deleteImage = async (imageId) => {
  if (!confirm('Are you sure you want to delete this image?')) return;

  try {
    await axios.delete(`/api/services/${props.serviceId}/images/${imageId}`);
    localImages.value = localImages.value.filter(img => img.id !== imageId);
  } catch (err) {
    error.value = 'Failed to delete image';
  }
};

const setPrimary = async (imageId) => {
  try {
    await axios.post(`/api/services/${props.serviceId}/images/${imageId}/primary`);
    localImages.value = localImages.value.map(img => ({
      ...img,
      is_primary: img.id === imageId
    }));
  } catch (err) {
    error.value = 'Failed to set primary image';
  }
};

const handleReorder = async () => {
  const orders = localImages.value.map((img, index) => ({
    id: img.id,
    order: index
  }));

  try {
    await axios.post(`/api/services/${props.serviceId}/images/reorder`, { orders });
  } catch (err) {
    error.value = 'Failed to reorder images';
  }
};
</script>

<style scoped>
.image-uploader {
  width: 100%;
}

.drop-zone {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #f9fafb;
}

.drop-zone:hover,
.drop-zone.drag-over {
  border-color: #4f46e5;
  background: #eef2ff;
}

.drop-zone.has-error {
  border-color: #ef4444;
  background: #fef2f2;
}

.hidden {
  display: none;
}

.drop-zone-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.upload-icon {
  width: 48px;
  height: 48px;
  color: #9ca3af;
}

.drop-text {
  color: #374151;
  font-size: 14px;
}

.drop-text .font-medium {
  color: #4f46e5;
  font-weight: 500;
}

.file-types {
  color: #9ca3af;
  font-size: 12px;
}

.uploading-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-message {
  color: #ef4444;
  font-size: 14px;
  margin-top: 8px;
}

.image-gallery {
  margin-top: 24px;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 16px;
}

.gallery-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  cursor: move;
  border: 2px solid transparent;
  transition: all 0.2s;
}

.gallery-item.is-primary {
  border-color: #4f46e5;
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.primary-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background: #4f46e5;
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 4px;
  text-transform: uppercase;
}

.image-actions {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s;
}

.gallery-item:hover .image-actions {
  opacity: 1;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: all 0.2s;
}

.action-btn:hover {
  background: white;
  color: #4f46e5;
}

.action-btn.delete:hover {
  color: #ef4444;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

.ghost {
  opacity: 0.5;
  background: #f3f4f6;
}
</style>
