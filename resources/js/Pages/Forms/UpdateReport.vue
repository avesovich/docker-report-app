<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  article: {
    type: Object,
    required: true,
  },
  comments: {
    type: Object,
    default: () => ({
      data: [],
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      pageSize: 5,
    }),
  },
  typeOfReportOptions: {
    type: Array,
    default: () => [
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
    ],
  },
});

const form = useForm({
  title: props.article?.title || '',
  type_of_report: props.article?.type_of_report || '',
  publication_date: props.article?.publication_date || '',
  url: props.article?.url || '',
  detailed_summary: props.article?.detailed_summary || '',
  analysis: props.article?.analysis || '',
  recommendation: props.article?.recommendation || '',
});

const submitUpdate = () => {
  form.put(route('status.update', { id: props.article.id }), {
    preserveScroll: true,
    onSuccess: () => {
      router.visit(route('status.index', { status: 'Updated' }), {
        // ✅ Use dynamic route
        preserveState: true,
        replace: true,
      });
    },
  });
};

const loadComments = (page) => {
  router.get(route('status.update', { id: props.article.id, page }), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Update Article" />
  <Layout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
          <div
            class="flex flex-col md:flex-row gap-6 border-b pb-6 border-gray-300 dark:border-gray-700"
          >
            <!-- Left Column - Update Article -->
            <div class="w-full md:w-1/2">
              <h2
                class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4"
              >
                Update Article
              </h2>

              <form @submit.prevent="submitUpdate">
                <!-- Title -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    Title
                  </label>
                  <input
                    v-model="form.title"
                    type="text"
                    class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Type of Report -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    Type of Report
                  </label>
                  <select
                    v-model="form.type_of_report"
                    class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  >
                    <option
                      v-for="option in typeOfReportOptions"
                      :key="option"
                      :value="option"
                    >
                      {{ option }}
                    </option>
                  </select>
                </div>

                <!-- Publication Date -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    Publication Date
                  </label>
                  <input
                    v-model="form.publication_date"
                    type="date"
                    class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- URL -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    URL
                  </label>
                  <input
                    v-model="form.url"
                    type="url"
                    class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  />
                </div>

                <!-- Detailed Summary -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    Detailed Summary
                  </label>
                  <textarea
                    v-model="form.detailed_summary"
                    class="w-full min-h-[120px] resize-none px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  ></textarea>
                </div>

                <!-- Analysis -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    Analysis
                  </label>
                  <textarea
                    v-model="form.analysis"
                    class="w-full min-h-[120px] resize-none px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  ></textarea>
                </div>

                <!-- Recommendation -->
                <div class="mb-4">
                  <label
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                  >
                    Recommendation
                  </label>
                  <textarea
                    v-model="form.recommendation"
                    class="w-full min-h-[120px] resize-none px-3 py-2 border rounded-lg bg-white dark:bg-gray-700 dark:text-white"
                  ></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="mt-6 flex justify-between">
                  <button
                    type="button"
                    @click="
                      $inertia.visit(
                        route('status.index', { status: 'Revision' })
                      )
                    "
                    class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-black dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    :disabled="form.processing"
                  >
                    Update
                  </button>
                </div>
              </form>
            </div>

            <!-- Vertical Divider -->
            <div
              class="hidden md:block w-[2px] bg-gray-300 dark:bg-gray-700"
            ></div>

            <!-- Right Column - Comments -->
            <div class="w-full md:w-1/2 pl-6">
              <h2
                class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4"
              >
                Comments
              </h2>

              <div v-if="comments.data.length > 0">
                <div
                  v-for="comment in comments.data"
                  :key="comment.id"
                  class="p-4 mb-2 border rounded-lg bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600"
                >
                  <p
                    class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                  >
                    {{ comment.user?.name ?? 'Unknown User' }}
                    <span class="text-xs text-gray-500 ml-2">
                      ({{ new Date(comment.created_at).toLocaleString() }})
                    </span>
                  </p>
                  <p class="text-gray-800 dark:text-gray-100">
                    {{ comment.comment }}
                  </p>
                </div>

                <!-- Pagination Component -->
                <Pagination
                  :currentPage="comments.currentPage"
                  :totalPages="comments.totalPages"
                  :totalItems="comments.totalItems"
                  :pageSize="comments.pageSize"
                  @change-page="loadComments"
                />
              </div>

              <p v-else class="text-gray-500 dark:text-gray-300">
                No comments yet.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
