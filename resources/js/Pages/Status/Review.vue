<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce';
import Layout from '@/Layouts/Layout.vue';
import CustomTable from '@/Components/CustomTable.vue';
import Pagination from '@/Components/Pagination.vue';
import LinkBg from '@/Components/LinkBg.vue';
import FloatingSuccess from '@/Components/FloatingSuccess.vue';

const { articles: initialArticles, userRoles } = defineProps([
  'articles',
  'userRoles',
]);
const articles = ref({
  data: initialArticles?.data || [],
  current_page: initialArticles?.current_page || 1,
  last_page: initialArticles?.last_page || 1,
  total: initialArticles?.total || 0,
  per_page: initialArticles?.per_page || 10,
});

const searchQuery = ref('');
const isEditor = computed(() => userRoles.includes('editor'));
const page = usePage();
const successMessage = page.props.successMessage || '';

const headers = computed(() => [
  'Title',
  'Type of Report',
  isEditor.value ? ' ' : 'Editor Name',
  'Date Submitted',
  'Approval Status',
  'Actions',
]);

const rows = computed(() => {
  return articles.value.data.map((article) => {
    return [
      article.title,
      article.type_of_report,
      isEditor.value ? ' ' : article.editor_name,
      article.publication_date,
      article.approval_status,
      {
        type: 'component',
        component: Link,
        props: {
          href: route('status.view', {
            status: article.approval_status,
            id: article.id,
          }),
          class: 'text-blue-500 underline',
        },
        slot: 'View',
      },
    ];
  });
});

function onChangePage(page) {
  fetchArticles(page, searchQuery.value);
}

const fetchArticles = debounce((page = 1, search = '') => {
  const status = route().params.status || 'Review';

  axios
    .get(route('status.index', { status, page, search }))
    .then((response) => {
      articles.value = {
        data: response.data.articles || [],
        current_page: response.data.currentPage || 1,
        last_page: response.data.totalPages || 1,
        total: response.data.totalItems || 0,
        per_page: 10,
      };
    })
    .catch((error) => {
      console.error('Error fetching articles:', error);
    });
}, 500);

onMounted(() => {
  fetchArticles(1, searchQuery.value);
});

watch(searchQuery, () => {
  fetchArticles(1, searchQuery.value);
});

const exportCsv = () => {
  const status = route().params.status || 'Review';
  const exportUrl = route('articles.export', { status });

  const link = document.createElement('a');
  link.href = exportUrl;
  link.setAttribute('download', '');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<template>
  <Head title="Review" />

  <Layout>
    <FloatingSuccess v-if="successMessage" :message="successMessage" />
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
      >
        Review
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div
          class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
        >
          <div class="flex items-center justify-between gap-4 px-6 pt-6 w-full">
            <LinkBg
              v-if="
                $page.props.auth.user.roles.some(
                  (role) => role.name === 'editor'
                )
              "
              :href="route('form.create')"
            >
              📝 Create a New Article Report
            </LinkBg>
            <div class="ml-auto">
              <input
                v-model="searchQuery"
                placeholder="Search articles..."
                class="border px-4 py-3 rounded-md transition duration-300 bg-white text-gray-900 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 focus:ring focus:ring-blue-400 dark:focus:ring-blue-600"
              />
            </div>
            <button
              @click="exportCsv"
              class="text-center inline-flex items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out hover:bg-gray-700 duration-300 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:focus:ring-offset-gray-800 dark:active:bg-gray-300"
            >
              📥 Export CSV
            </button>
          </div>

          <div class="p-6 text-gray-900 dark:text-gray-100">
            <CustomTable
              :headers="headers"
              :rows="rows"
              :page="articles.current_page"
              :page-size="articles.per_page"
            >
              <template #empty v-if="rows.length === 0">
                <tr>
                  <td
                    colspan="5"
                    class="p-4 text-center text-gray-500 dark:text-gray-300"
                  >
                    No data available
                  </td>
                </tr>
              </template>
            </CustomTable>

            <div
              v-if="articles.data.length === 0"
              class="p-4 text-center text-gray-500 dark:text-gray-300"
            >
              No data available
            </div>

            <Pagination
              :current-page="articles.current_page"
              :total-pages="articles.last_page"
              :total-items="articles.total"
              :page-size="articles.per_page"
              @change-page="onChangePage"
            />
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>
