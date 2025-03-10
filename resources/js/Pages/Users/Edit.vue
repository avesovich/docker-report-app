<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import Card from '@/Components/Card.vue';
import FloatingSuccess from '@/Components/FloatingSuccess.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed } from 'vue';

const props = defineProps({
  user: Object,
  roles: Array, // Available roles from backend
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  role: props.user.roles?.length ? props.user.roles[0].name : '',
});

// Submit update request
const updateUser = () => {
  form.put(route('users.update', props.user.id));
};

// Check if the form is dirty
const isFormDirty = computed(() => form.isDirty);

// Capitalize roles for display
const capitalizedRoles = computed(() => {
  return props.roles.map((role) => ({
    value: role, // Keep original lowercase value
    label: role.charAt(0).toUpperCase() + role.slice(1), // Capitalized display
  }));
});
</script>

<template>
  <Layout>
    <Head title="Edit User" />

    <template #header>
      <h2
        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
      >
        Edit User
      </h2>
    </template>

    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
      <Card class="p-6 shadow-lg bg-white dark:bg-gray-800">
        <FloatingSuccess
          v-if="form.recentlySuccessful"
          message="User updated successfully!"
        />

        <form @submit.prevent="updateUser" class="space-y-6">
          <!-- Name -->
          <div>
            <InputLabel for="name" value="Name" class="text-gray-300" />
            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="w-full"
            />
            <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <InputLabel for="email" value="Email" class="text-gray-300" />
            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="w-full"
            />
            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password (Optional) -->
          <div>
            <InputLabel
              for="password"
              value="New Password (Leave blank to keep current)"
              class="text-gray-300"
            />
            <TextInput
              id="password"
              v-model="form.password"
              type="password"
              class="w-full"
            />
            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Role Selection -->
          <div>
            <InputLabel for="role" value="Role" class="text-gray-300" />
            <select
              id="role"
              v-model="form.role"
              class="w-full p-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
            >
              <option
                v-for="role in capitalizedRoles"
                :key="role.value"
                :value="role.value"
              >
                {{ role.label }}
              </option>
            </select>
            <p v-if="form.errors.role" class="text-sm text-red-600 mt-1">
              {{ form.errors.role }}
            </p>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex justify-between">
            <Link
              :href="route('users.index')"
              class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-black dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600"
            >
              Go Back
            </Link>
            <PrimaryButton
              type="submit"
              :disabled="form.processing || !isFormDirty"
            >
              Save Changes
            </PrimaryButton>
          </div>
        </form>
      </Card>
    </div>
  </Layout>
</template>
