<!-- resources/js/components/reusable/Title.vue -->
<template>
    <h2 :class="['text-lg font-semibold text-gray-900', className]">
        <slot />
    </h2>
</template>

<script setup lang="ts">
interface Props {
    className?: string;
}

withDefaults(defineProps<Props>(), {
    className: '',
});
</script>
