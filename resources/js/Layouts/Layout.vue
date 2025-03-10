<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isDarkMode = ref(false);

onMounted(() => {
  isDarkMode.value = localStorage.getItem('darkMode') === 'enabled';
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark');
  }
});

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('darkMode', 'enabled');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('darkMode', 'disabled');
  }
};
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
      <nav
        class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800 sticky top-0 z-50"
      >
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex items-center space-x-8">
              <!-- Logo -->
              <div class="flex shrink-0 items-center">
                <Link :href="route('dashboard')">
                  <ApplicationLogo
                    class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                  />
                </Link>
              </div>

              <!-- Navigation Links -->
              <div class="hidden sm:flex items-center space-x-8 sm:ms-10">
                <NavLink
                  :href="route('dashboard')"
                  :active="route().current('dashboard')"
                  class="py-2"
                >
                  Dashboard
                </NavLink>
              </div>

              <!-- Dropdown for Article Status -->
              <div class="hidden sm:flex items-center sm:ms-10">
                <Dropdown>
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                      >
                        Article Status
                        <svg
                          class="ms-2 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div
                      v-if="
                        $page.props.auth.user.roles.every(
                          (role) => role.name !== 'executive'
                        )
                      "
                    >
                      <DropdownLink
                        :href="route('status.index', { status: 'Review' })"
                        preserve-scroll
                        >Review</DropdownLink
                      >
                      <DropdownLink
                        :href="route('status.index', { status: 'Updated' })"
                        preserve-scroll
                        >Updated</DropdownLink
                      >
                      <DropdownLink
                        :href="route('status.index', { status: 'Revision' })"
                        preserve-scroll
                        >Revision</DropdownLink
                      >
                    </div>
                    <DropdownLink
                      :href="route('status.index', { status: 'Evaluated' })"
                      preserve-scroll
                      >Evaluated</DropdownLink
                    >
                    <DropdownLink
                      :href="route('status.index', { status: 'Approved' })"
                      preserve-scroll
                      >Approved</DropdownLink
                    >
                  </template>
                </Dropdown>
              </div>
              <div class="hidden sm:flex items-center space-x-8 sm:ms-10">
                <NavLink
                  :href="route('news.index')"
                  :active="route().current('news.index')"
                  class="py-2"
                >
                  News
                </NavLink>
              </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
              <span class="ms-3">
                <button
                  @click="toggleDarkMode"
                  class="px-3 py-2 text-sm font-medium rounded-md hover:bg-gray-200 hover:dark:bg-gray-700 duration-300 text-gray-800 dark:text-gray-200 transition"
                >
                  {{ isDarkMode ? '🌙' : '☀️' }}
                </button>
              </span>
              <!-- Settings Dropdown -->
              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                      >
                        {{ $page.props.auth.user.name }}

                        <svg
                          class="-me-0.5 ms-2 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>
                  <template #content>
                    <DropdownLink :href="route('profile.edit')" preserve-scroll>
                      Profile
                    </DropdownLink>
                    <template
                      v-if="
                        $page.props.auth.user.roles.every(
                          (role) => role.name === 'administrator'
                        )
                      "
                    >
                      <DropdownLink
                        :href="route('users.index')"
                        preserve-scroll
                      >
                        Manage Users
                      </DropdownLink>
                    </template>

                    <DropdownLink
                      :href="route('logout')"
                      method="post"
                      as="button"
                    >
                      Log Out
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden"
        >
          <div class="space-y-1 pb-3 pt-2">
            <ResponsiveNavLink
              :href="route('dashboard')"
              :active="route().current('dashboard')"
            >
              Dashboard
            </ResponsiveNavLink>
            <div class="px-4">
              <div
                class="text-base font-medium text-gray-800 dark:text-gray-200"
              >
                Article Status
              </div>
            </div>
            <div class="px-5">
              <div
                v-if="
                  $page.props.auth.user.roles.every(
                    (role) => role.name !== 'executive'
                  )
                "
              >
                <ResponsiveNavLink
                  :href="route('status.index', { status: 'Review' })"
                  preserve-scroll
                  >Review</ResponsiveNavLink
                >
                <ResponsiveNavLink
                  :href="route('status.index', { status: 'Updated' })"
                  preserve-scroll
                  >Updated</ResponsiveNavLink
                >
                <ResponsiveNavLink
                  :href="route('status.index', { status: 'Revision' })"
                  preserve-scroll
                  >Revision</ResponsiveNavLink
                >
              </div>
              <ResponsiveNavLink
                :href="route('status.index', { status: 'Evaluated' })"
                preserve-scroll
                >Evaluated</ResponsiveNavLink
              >
              <ResponsiveNavLink
                :href="route('status.index', { status: 'Approved' })"
                preserve-scroll
                >Approved</ResponsiveNavLink
              >
            </div>
            <ResponsiveNavLink
              :href="route('news.index')"
              :active="route().current('news.index')"
            >
              News
            </ResponsiveNavLink>
          </div>

          <!-- Responsive Settings Options -->
          <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
            <div class="px-4">
              <div
                class="text-base font-medium text-gray-800 dark:text-gray-200"
              >
                {{ $page.props.auth.user.name }}
              </div>
              <div class="text-sm font-medium text-gray-500">
                {{ $page.props.auth.user.email }}
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                Profile
              </ResponsiveNavLink>
              <template
                v-if="
                  $page.props.auth.user.roles.every(
                    (role) => role.name === 'administrator'
                  )
                "
              >
                <ResponsiveNavLink :href="route('users.index')">
                  Manage Users
                </ResponsiveNavLink>
              </template>
              <ResponsiveNavLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow dark:bg-gray-800" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>
