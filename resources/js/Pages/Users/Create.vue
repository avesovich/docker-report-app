<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Layout from '@/Layouts/Layout.vue';
import Card from '@/Components/Card.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'editor',
});

// Submit function
const submitForm = () => {
  form.post(route('users.store'), {
    onSuccess: () => {
      router.visit(route('users.index'), {
        preserveState: true,
        replace: true,
      });
    },
    onError: (errors) => {
      console.error('Validation failed:', errors);
    },
  });
};
</script>

<template>
  <Layout>
    <Head title="Create User" />
    <div class="py-12 mx-auto max-w-3xl sm:px-6 lg:px-8">
      <Card class="p-6 dark:bg-gray-800">
        <h2 class="text-2xl font-bold text-black dark:text-gray-100 mb-4">
          Create New User
        </h2>
        <p class="text-black-400 dark:text-gray-400 mb-6">
          Fill in the details below to create a new user account.
        </p>

        <form @submit.prevent="submitForm" class="space-y-6">
          <div>
            <InputLabel for="name" value="Name" class="text-gray-300" />
            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="w-full"
            />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
              {{ form.errors.name }}
            </p>
          </div>

          <div>
            <InputLabel for="email" value="Email" class="text-gray-300" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="w-full"
            />
            <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <InputLabel for="password" value="Password" class="text-gray-300" />
            <TextInput
              id="password"
              v-model="form.password"
              type="password"
              class="w-full"
            />
            <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
              {{ form.errors.password }}
            </p>
          </div>

          <div>
            <InputLabel
              for="password_confirmation"
              value="Confirm Password"
              class="text-gray-300"
            />
            <TextInput
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="w-full"
            />
          </div>

          <div>
            <InputLabel for="role" value="Role" class="text-gray-300" />
            <select
              id="role"
              v-model="form.role"
              class="w-full p-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
            >
              <option value="administrator">Administrator</option>
              <option value="editor">Editor</option>
              <option value="executive">Executive</option>
            </select>
            <p v-if="form.errors.role" class="text-red-500 text-sm mt-1">
              {{ form.errors.role }}
            </p>
          </div>

          <div class="mt-6 flex justify-between">
            <button
              type="button"
              @click="$inertia.visit(route('users.index'))"
              class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-black dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600"
            >
              Go Back
            </button>
            <PrimaryButton type="submit" :disabled="form.processing"
              >Create User</PrimaryButton
            >
          </div>
        </form>
      </Card>
    </div>
  </Layout>
</template>
