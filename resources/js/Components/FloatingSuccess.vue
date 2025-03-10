<template>
  <transition
    enter-active-class="transition ease-out duration-300 transform"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200 transform"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div
      v-if="visible"
      class="fixed bottom-5 right-5 z-50 flex items-center p-4 text-sm text-green-800 bg-green-200 border border-green-400 rounded-lg shadow-lg max-w-100"
    >
      <svg
        class="shrink-0 w-5 h-5 me-3"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
        />
      </svg>
      <span class="font-medium">{{ message }}</span>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  message: String,
});

const visible = ref(false);

// Show the message when it exists
watch(
  () => props.message,
  (newMessage) => {
    if (newMessage) {
      visible.value = true;
      setTimeout(() => {
        visible.value = false;
      }, 10000); // Auto-hide after 3 seconds
    }
  },
  { immediate: true }
);
</script>
