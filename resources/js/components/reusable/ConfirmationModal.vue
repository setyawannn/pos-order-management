<!-- components/reusable/ConfirmationModal.vue -->
<template>
    <Dialog :open="show" @update:open="$emit('cancel')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center space-x-2">
                    <AlertTriangle class="h-5 w-5 text-red-600" />
                    <span>{{ title }}</span>
                </DialogTitle>
                <DialogDescription>
                    {{ message }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button type="button" variant="outline" @click="$emit('cancel')"> Batal </Button>
                <Button type="button" variant="destructive" @click="$emit('confirm')" class="flex items-center space-x-2">
                    <Trash2 class="h-4 w-4" />
                    <span>Hapus</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';

defineProps({
    show: Boolean,
    title: String,
    message: String,
});

defineEmits(['confirm', 'cancel']);
</script>
