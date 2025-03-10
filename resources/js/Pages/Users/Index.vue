<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import LinkBg from '@/Components/LinkBg.vue';
import Layout from '@/Layouts/Layout.vue';
import Modal from '@/Components/Modal.vue'; // Import your existing modal component
import { ref, h, computed } from 'vue';
import FloatingSuccess from '@/Components/FloatingSuccess.vue';

const props = defineProps({ headers: Array, rows: Array, users: Object });
const headers = ['Name', 'Email', 'Role', 'Actions'];

const page = usePage();
const successMessage = page.props.successMessage || '';

const showModal = ref(false);
const selectedUser = ref(null);

const confirmDelete = (user) => {
  selectedUser.value = user;
  showModal.value = true;
};

const deleteUser = () => {
  if (selectedUser.value) {
    useForm().delete(route('users.destroy', selectedUser.value.id));
    showModal.value = false;
    selectedUser.value = null;
  }
};

// ✅ Properly formatted rows using `h()` for correct rendering
const rows = computed(() => {
  return props.users.data.map((user) => [
    user.name,
    user.email,
    user.roles.length > 0
      ? user.roles
          .map((role) => role.name.charAt(0).toUpperCase() + role.name.slice(1))
          .join(', ')
      : 'N/A',
    () =>
      h('div', { class: 'flex gap-2' }, [
        h(
          Link,
          {
            href: route('users.edit', user.id),
            class: 'text-blue-500 hover:underline',
          },
          () => 'Edit'
        ),
        h(
          'button',
          {
            class: 'text-red-500 ml-2 hover:underline',
            onClick: () => confirmDelete(user),
          },
          'Delete'
        ),
      ]),
  ]);
});
</script>

<template>
  <Head title="Manage Users" />
  <Layout>
    <FloatingSuccess v-if="successMessage" :message="successMessage" />
    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
      >
        Manage Users
      </h2>
    </template>

    <FloatingSuccess v-if="successMessage" type="success" class="mb-4">
      {{ successMessage }}
    </FloatingSuccess>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <Card class="p-6 shadow-lg bg-white dark:bg-gray-800">
        <div class="mb-6">
          <LinkBg :href="route('users.create')">Create User</LinkBg>
        </div>
        <div class="overflow-x-auto">
          <table
            class="w-full border-collapse border border-gray-300 dark:border-gray-700"
          >
            <thead>
              <tr class="bg-gray-200 dark:bg-gray-700">
                <th
                  v-for="(header, index) in headers"
                  :key="index"
                  class="border border-gray-300 dark:border-gray-600 p-3 text-left text-gray-800 dark:text-gray-200"
                >
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, rowIndex) in rows"
                :key="rowIndex"
                class="border-t border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600"
              >
                <td
                  v-for="(cell, cellIndex) in row"
                  :key="cellIndex"
                  class="border border-gray-300 dark:border-gray-700 p-3 text-gray-800 dark:text-gray-200"
                >
                  <template v-if="typeof cell === 'function'">
                    <component :is="cell"></component>
                  </template>
                  <template v-else>
                    {{ cell }}
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Card>
    </div>

    <!-- Delete Confirmation Modal -->

    <Modal :show="showModal" @close="showModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-200">
          Are you sure you want to delete
          <span class="font-bold">{{ selectedUser?.name }}</span
          >?
        </h2>
        <div class="mt-6 flex justify-end gap-2">
          <button
            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
            @click="showModal = false"
          >
            No
          </button>
          <button
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            @click="deleteUser"
          >
            Yes
          </button>
        </div>
      </div>
    </Modal>
  </Layout>
</template>
