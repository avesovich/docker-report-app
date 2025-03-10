<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

import Layout from '@/Layouts/Layout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';

const isModalOpen = ref(false);
const imageFiles = ref([]);
const imagePreviews = ref([]);
const uploadError = ref(null);
const isUploading = ref(false);

const reportTypes = [
  'Breach',
  'Data Leak',
  'Malware Information',
  'Threat Actors Updates',
  'Cyber Awareness',
  'Vulnerability Exploitation',
  'Phishing',
  'Ransomware',
  'Social Engineering',
  'Illegal Access',
];

const form = useForm({
  title: '',
  publication_date: '',
  type_of_report: '',
  url: '',
  detailed_summary: '',
  analysis: '',
  recommendation: '',
  image_path: [],
});

const openModal = () => {
  isModalOpen.value = true;
};

const sanitizeInput = (input) => {
  return input.replace(/<\/?p>/g, '').trim();
};

const handleFileUpload = (event) => {
  const files = event.target.files;
  if (files.length > 10) {
    uploadError.value = 'You can only upload a maximum of 10 images.';
    return;
  }

  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  let validFiles = [];
  let previews = [];

  for (let file of files) {
    if (!allowedTypes.includes(file.type)) {
      uploadError.value =
        'Invalid file type. Only JPEG, PNG, GIF, WEBP allowed.';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      uploadError.value = 'File size exceeds 5MB limit.';
      return;
    }

    validFiles.push(file);
    previews.push(URL.createObjectURL(file));
  }

  uploadError.value = null;
  imageFiles.value = validFiles;
  imagePreviews.value = previews;
};

const submitForm = async () => {
  if (imageFiles.value.length > 0) {
    isUploading.value = true;

    const formData = new FormData();
    imageFiles.value.forEach((file) => formData.append('images[]', file));

    try {
      const response = await axios.post('/api/upload-images', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      form.image_path = response.data.paths;
      uploadError.value = null;
    } catch (error) {
      uploadError.value =
        'Upload failed: ' + (error.response?.data?.message || 'Unknown error');
      isUploading.value = false;
      return;
    }
  }

  const sanitizedData = {
    title: form.title,
    publication_date: form.publication_date,
    type_of_report: form.type_of_report,
    url: form.url,
    detailed_summary: sanitizeInput(form.detailed_summary),
    analysis: sanitizeInput(form.analysis),
    recommendation: sanitizeInput(form.recommendation),
    image_path: form.image_path,
  };

  form.post(route('create.report'), {
    data: sanitizedData,
    onSuccess: () => {
      router.visit(route('status.index', { status: 'Review' }), {
        preserveState: true,
        replace: true,
      });
    },
    onError: (errors) => {
      console.error('Validation failed:', errors);
    },
    onFinish: () => {
      isUploading.value = false;
    },
  });
};
</script>

<template>
  <Head title="Create Report" />

  <Layout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
      >
        Create Article Report
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <form @submit.prevent="openModal" class="space-y-6">
          <!-- General Information Card -->
          <Card title="General Information">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Title -->
              <div>
                <InputLabel for="title" value="Title" />
                <TextInput
                  id="title"
                  v-model="form.title"
                  type="text"
                  placeholder="Enter article title"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <InputError :message="form.errors.title" />
              </div>

              <!-- Publication Date -->
              <div>
                <InputLabel for="publication_date" value="Publication Date" />
                <TextInput
                  id="publication_date"
                  v-model="form.publication_date"
                  type="date"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <InputError :message="form.errors.publication_date" />
              </div>

              <!-- Type of Report -->
              <div>
                <InputLabel for="type_of_report" value="Type of Report" />
                <select
                  v-model="form.type_of_report"
                  id="type_of_report"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="" disabled>Select Report Type</option>
                  <option v-for="type in reportTypes" :key="type" :value="type">
                    {{ type }}
                  </option>
                </select>
                <InputError :message="form.errors.type_of_report" />
              </div>

              <!-- URL -->
              <div>
                <InputLabel for="url" value="Article URL" />
                <TextInput
                  id="url"
                  v-model="form.url"
                  type="url"
                  placeholder="https://example.com/article"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <InputError :message="form.errors.url" />
              </div>
            </div>
          </Card>

          <!-- Report Details Card -->
          <Card title="Report Details">
            <div class="grid grid-cols-1 gap-4">
              <!-- Detailed Summary -->
              <div>
                <InputLabel for="detailed_summary" value="Detailed Summary" />
                <textarea
                  id="detailed_summary"
                  v-model.trim="form.detailed_summary"
                  rows="4"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Provide a detailed summary"
                ></textarea>
                <InputError :message="form.errors.detailed_summary" />
              </div>

              <!-- Analysis -->
              <div>
                <InputLabel for="analysis" value="Analysis" />
                <textarea
                  id="analysis"
                  v-model.trim="form.analysis"
                  rows="4"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Provide an in-depth analysis"
                ></textarea>
                <InputError :message="form.errors.analysis" />
              </div>

              <!-- Recommendation -->
              <div>
                <InputLabel for="recommendation" value="Recommendation" />
                <textarea
                  id="recommendation"
                  v-model.trim="form.recommendation"
                  rows="4"
                  class="w-full p-3 bg-white border border-gray-300 rounded-md text-gray-900 transition-all duration-300 ease-in-out dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Provide your recommendations"
                ></textarea>
                <InputError :message="form.errors.recommendation" />
              </div>
            </div>
          </Card>

          <Card title="Upload Images (Max 10)">
            <div>
              <InputLabel for="images" value="Upload Article Images" />
              <input
                type="file"
                id="images"
                multiple
                accept="image/*"
                @change="handleFileUpload"
                class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-200 border border-gray-300 rounded-md cursor-pointer focus:outline-none dark:bg-gray-800 dark:border-gray-600"
              />
              <p v-if="uploadError" class="text-red-500 text-sm mt-1">
                {{ uploadError }}
              </p>

              <!-- Preview Multiple Images -->
              <div
                v-if="imagePreviews.length > 0"
                class="mt-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2"
              >
                <div v-for="(preview, index) in imagePreviews" :key="index">
                  <img
                    :src="preview"
                    class="w-24 h-24 object-cover rounded-md shadow-md"
                  />
                </div>
              </div>
            </div>
          </Card>

          <div class="mt-6 flex justify-between">
            <button
              type="button"
              @click="$inertia.visit(route('status.review'))"
              class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-black dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600"
            >
              Go Back
            </button>
            <PrimaryButton type="submit">Submit Report</PrimaryButton>
          </div>
        </form>
      </div>
    </div>

    <Modal :show="isModalOpen" max-width="2xl" @close="isModalOpen = false">
      <div class="p-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
          Review Your Submission
        </h2>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Please review your inputs before submitting the report.
        </p>

        <div class="mt-4 space-y-2 dark:text-gray-200">
          <p><strong>Title:</strong> {{ form.title }}</p>
          <p><strong>Publication Date:</strong> {{ form.publication_date }}</p>
          <p><strong>Type of Report:</strong> {{ form.type_of_report }}</p>
          <p>
            <strong>URL:</strong>
            <a
              :href="form.url"
              target="_blank"
              class="text-blue-500 underline"
              >{{ form.url }}</a
            >
          </p>
          <p><strong>Detailed Summary:</strong> {{ form.detailed_summary }}</p>
          <p><strong>Analysis:</strong> {{ form.analysis }}</p>
          <p><strong>Recommendation:</strong> {{ form.recommendation }}</p>
          <div class="mt-4">
            <p class="font-semibold text-gray-700 dark:text-gray-300">
              Selected Images:
            </p>
            <div
              v-if="imagePreviews.length > 0"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 mt-2"
            >
              <div
                v-for="(preview, index) in imagePreviews"
                :key="index"
                class="relative"
              >
                <img
                  :src="preview"
                  class="w-24 h-24 object-cover rounded-md shadow-md"
                />
              </div>
            </div>
            <p v-else class="text-gray-500 dark:text-gray-400 mt-2">
              No images selected.
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-2">
          <button
            @click="isModalOpen = false"
            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600"
          >
            Edit
          </button>
          <button
            @click="submitForm"
            :disabled="isUploading"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center"
          >
            <span v-if="isUploading" class="animate-spin mr-2">🔄</span>
            Confirm & Submit
          </button>
        </div>
      </div>
    </Modal>
  </Layout>
</template>

<style scoped>
input[type='date']::-webkit-calendar-picker-indicator {
  transition: filter 0.3s ease;
}

.dark input[type='date']::-webkit-calendar-picker-indicator {
  filter: invert(1);
}
</style>
