<!-- components/reusable/ConfirmationModal.vue -->
<template>
    <Dialog :open="show" @update:open="$emit('cancel')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <component :is="iconComponent" class="h-5 w-5" :class="iconClass" />
                    <span>{{ title }}</span>
                </DialogTitle>
                <DialogDescription>
                    {{ message }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button type="button" variant="outline" @click="$emit('cancel')"> {{ cancelText }} </Button>
                <Button type="button" :variant="confirmVariant" @click="$emit('confirm')" class="flex items-center space-x-2">
                    <component :is="confirmIconComponent" class="h-4 w-4" v-if="confirmIconComponent" />
                    <span>{{ confirmText }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertTriangle, Ban, CheckCircle, History, Save, Trash2, XCircle } from 'lucide-vue-next'; // Import all potentially useful icons
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    confirmText: {
        type: String,
        default: 'Hapus',
    },
    confirmVariant: {
        type: String as () => 'destructive' | 'default' | 'outline' | 'secondary' | 'ghost' | 'link' | null | undefined,
        default: 'destructive',
    },
    icon: {
        type: String,
        default: 'AlertTriangle',
    },
    iconClass: {
        type: String,
        default: 'text-red-600',
    },
    confirmIcon: {
        type: String,
        default: 'Trash2',
    },
});

defineEmits(['confirm', 'cancel']);

const iconComponent = computed(() => {
    const iconMap: { [key: string]: any } = {
        AlertTriangle,
        Trash2,
        XCircle,
        CheckCircle,
        Save,
        Ban,
        History,
    };
    return iconMap[props.icon] || AlertTriangle;
});

const confirmIconComponent = computed(() => {
    const iconMap: { [key: string]: any } = {
        AlertTriangle,
        Trash2,
        XCircle,
        CheckCircle,
        Save,
        Ban,
        History,
    };
    return iconMap[props.confirmIcon];
});
</script>
