import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useToast } from 'vue-toastification';

interface FlashProps {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
}

type PropsWithFlash = {
    flash: FlashProps;
} & ReturnType<typeof usePage>['props'];

export function useFlashToast() {
    const toast = useToast();
    const page = usePage<PropsWithFlash>();
    const flash = page.props.flash;

    const show = () => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
        if (flash?.info) toast.info(flash.info);
        if (flash?.warning) toast.warning(flash.warning);
    };

    watch(
        () => flash,
        () => show(),
        { immediate: true },
    );
}
