<script setup>
import { computed, onMounted, onUnmounted, ref, defineExpose } from 'vue';

const props = defineProps({
  align: { type: String, default: 'right' },
  width: { type: String, default: '48' },
  contentClasses: { type: String, default: 'py-1 bg-white dark:bg-gray-700' },
});

const open = ref(false);

// **✅ Method to Close Dropdown Programmatically**
const closeDropdown = () => {
  open.value = false;
};

// Close dropdown on Escape key
const closeOnEscape = (e) => {
  if (open.value && e.key === 'Escape') {
    closeDropdown();
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

// **✅ Expose the `closeDropdown` function to parent components**
defineExpose({ closeDropdown });

const widthClass = computed(
  () =>
    ({
      48: 'w-48',
    })[props.width.toString()]
);

const alignmentClasses = computed(() => {
  if (props.align === 'left') {
    return 'ltr:origin-top-left rtl:origin-top-right start-0';
  } else if (props.align === 'right') {
    return 'ltr:origin-top-right rtl:origin-top-left end-0';
  } else {
    return 'origin-top';
  }
});
</script>

<template>
  <div class="relative">
    <div @click="open = !open">
      <slot name="trigger" />
    </div>

    <!-- Full Screen Dropdown Overlay -->
    <div v-show="open" class="fixed inset-0 z-40" @click="closeDropdown"></div>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-show="open"
        class="absolute z-50 mt-2 rounded-md shadow-lg bg-white dark:bg-gray-700"
        :class="[widthClass, alignmentClasses]"
        @click.stop
      >
        <div
          class="rounded-md ring-1 ring-black ring-opacity-5"
          :class="contentClasses"
        >
          <slot name="content" />
        </div>
      </div>
    </Transition>
  </div>
</template>
