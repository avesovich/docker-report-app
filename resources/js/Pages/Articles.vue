<script setup>
import Layout from '@/Layouts/Layout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="Articles" />

  <Layout>
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
      >
        Articles
      </h2>
    </template>
  </Layout>
</template>
