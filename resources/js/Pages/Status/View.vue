<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import Card from '@/Components/Card.vue';
import LinkBg from '@/Components/LinkBg.vue';
import Pagination from '@/Components/Pagination.vue';
import { computed } from 'vue';
import dayjs from 'dayjs';

const {
  userRoles = [],
  article,
  comments = [],
  imagePaths = [],
} = defineProps(['userRoles', 'article', 'comments', 'imagePaths']);

const isAdmin = computed(() => userRoles.includes('administrator'));
const isExecutive = computed(() => userRoles.includes('executive'));

const restrictedStatusesForAdmin = ['Approved', 'Evaluated', 'Revision'];
const restrictedStatusesForExecutive = [
  'Review',
  'Updated',
  'Revision',
  'Approved',
];

const hideAdminButtons = computed(() =>
  restrictedStatusesForAdmin.includes(article.approval_status)
);
const hideExecutiveButtons = computed(() =>
  restrictedStatusesForExecutive.includes(article.approval_status)
);

const form = useForm({
  article_id: article.id,
  comment: '',
});

const approvalForm = useForm({
  status: '',
});

const postComment = () => {
  form.post(`/articles/${article.id}/comments`, {
    preserveScroll: true,
    onSuccess: () => form.reset('comment'),
  });
};

const setApprovalStatus = (status) => {
  approvalForm.status = status;
  approvalForm.post(`/articles/${article.id}/approve`, {
    preserveScroll: true,
  });
};

const formatTime = (timestamp) => {
  return timestamp
    ? dayjs(timestamp).format('MMMM D, YYYY h:mm A')
    : 'Unknown Time';
};

const loadComments = (page) => {
  if (page) {
    router.get(
      route('status.view', {
        status: article.approval_status,
        id: article.id,
      }),
      { page },
      { preserveScroll: true, replace: true }
    );
  }
};

const generalFields = computed(() => {
  const fields = [
    { label: 'Title', value: article.title },
    { label: 'Type of Report', value: article.type_of_report },
    { label: 'Publication Date', value: article.publication_date },
    { label: 'URL', value: article.url, isLink: true },
    { label: 'Posted Date', value: article.posted_date },
    { label: 'Time Posted', value: article.time_posted },
    { label: 'Approval Status', value: article.approval_status },
  ];

  if (!userRoles.includes('editor')) {
    fields.push({ label: 'Editor Name', value: article.editor_name });
  }

  return fields;
});

const contentFields = computed(() => [
  { label: 'Detailed Summary', value: article.detailed_summary },
  { label: 'Analysis', value: article.analysis },
  { label: 'Recommendation', value: article.recommendation },
]);

const getImageUrl = (filename) => {
  if (!filename) return '';
  const cleanFilename = filename.split('/').pop();

  return `/image/article/${encodeURIComponent(cleanFilename)}`;
};
</script>

<template>
  <Head title="View Details" />

  <Layout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card class="p-6 bg-white shadow-xl rounded-2xl dark:bg-gray-800">
          <h2
            class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 border-b border-gray-300 pb-2"
          >
            General Details
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div
              v-for="(field, index) in generalFields"
              :key="index"
              class="p-4 border rounded-lg shadow-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-700"
            >
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ field.label }}
              </p>
              <p
                v-if="!field.isLink"
                class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100"
              >
                {{ field.value }}
              </p>
              <a
                v-else
                :href="field.value"
                target="_blank"
                class="mt-1 text-lg font-semibold text-blue-500 hover:text-blue-700 underline"
              >
                {{ field.value }}
              </a>
            </div>
          </div>

          <h2
            class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 border-b border-gray-300 pb-2"
          >
            Content Details
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-1 gap-6 mb-8">
            <div
              v-for="(field, index) in contentFields"
              :key="index"
              class="p-4 border rounded-lg shadow-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-700"
            >
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ field.label }}
              </p>
              <p
                class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100"
              >
                {{ field.value }}
              </p>
            </div>
          </div>

          <h2
            class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 border-b border-gray-300 pb-2"
          >
            Uploaded Images
          </h2>

          <div
            v-if="imagePaths.length > 0"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8"
          >
            <div
              v-for="(image, index) in imagePaths"
              :key="index"
              class="relative"
            >
              <img
                :src="getImageUrl(image)"
                class="w-full h-full object-cover rounded-lg shadow-md"
                alt="Article Image"
              />
            </div>
          </div>

          <!-- ✅ If no images, show a message -->
          <p v-else class="text-gray-500 dark:text-gray-400 text-center">
            No images uploaded for this article report.
          </p>

          <h2
            class="text-2xl font-bold mt-8 mb-6 text-gray-800 dark:text-gray-200 border-b border-gray-300 pb-2"
          >
            Comments
          </h2>

          <div
            class="comments-section mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-md"
          >
            <!-- Display Comments -->
            <div v-if="comments.data.length > 0">
              <div
                v-for="comment in comments.data"
                :key="comment.id"
                class="p-4 mb-3 border rounded-lg shadow-md bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-700"
              >
                <div class="flex items-center justify-between">
                  <p
                    class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                  >
                    {{ comment.user?.name ?? 'Unknown User' }}
                  </p>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    ({{ formatTime(comment.created_at) }})
                  </span>
                </div>
                <p class="mt-2 text-gray-800 dark:text-gray-100">
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
            <p v-else class="text-gray-500 dark:text-gray-400 text-center">
              No comments yet.
            </p>

            <!-- Admin-Only Comment Input -->
            <div v-if="isAdmin || isExecutive" class="mt-6">
              <textarea
                v-model="form.comment"
                placeholder="Write a comment..."
                class="w-full p-3 border rounded-lg bg-white dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
              <button
                @click="postComment"
                class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200"
                :disabled="form.processing"
              >
                Post Comment
              </button>
            </div>
          </div>

          <!-- Buttons Section -->
          <div class="mt-8 ml-3 flex justify-between items-center">
            <!-- Approve/Disapprove Buttons (Left) -->
            <div
              class="space-x-4"
              v-if="
                (isAdmin && !hideAdminButtons) ||
                (isExecutive && !hideExecutiveButtons)
              "
            >
              <button
                @click="setApprovalStatus('approved')"
                class="px-6 py-2 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700 transition duration-200"
                :disabled="approvalForm.processing"
              >
                ✅ Approve
              </button>
              <button
                @click="setApprovalStatus('disapproved')"
                class="px-6 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 transition duration-200"
                :disabled="approvalForm.processing"
              >
                ❌ Disapprove
              </button>
            </div>

            <!-- Back Button (Right) -->
            <div class="ml-auto">
              <LinkBg class="px-5 py-2"> Go Back </LinkBg>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.comments-section {
  margin-top: 20px;
}
</style>
