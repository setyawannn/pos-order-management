<!-- resources/js/components/reusable/PriceDisplay.vue -->
<template>
    <span :class="className">
        {{ formatPrice(amount) }}
    </span>
</template>

<script setup lang="ts">
import { formatPrice } from '@/utils/currency';

interface Props {
    amount: number;
    className?: string;
}

withDefaults(defineProps<Props>(), {
    className: '',
});
</script>
