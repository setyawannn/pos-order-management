// resources/js/composables/useBaseStore.ts
import { computed, ref } from 'vue';

export interface LoadingState {
    [key: string]: boolean;
}

export interface ErrorState {
    [key: string]: string | null;
}

export function useBaseStore() {
    const loading = ref<LoadingState>({});
    const errors = ref<ErrorState>({});

    const isLoading = computed(() => (key: string) => loading.value[key] || false);
    const getError = computed(() => (key: string) => errors.value[key] || null);
    const hasError = computed(() => (key: string) => !!errors.value[key]);

    const setLoading = (key: string, value: boolean) => {
        loading.value[key] = value;
    };

    const setError = (key: string, error: string | null) => {
        errors.value[key] = error;
    };

    const clearError = (key: string) => {
        errors.value[key] = null;
    };

    const clearAllErrors = () => {
        errors.value = {};
    };

    const handleApiError = (key: string, error: any) => {
        console.error(`API Error for ${key}:`, error);

        if (error.response?.status === 422) {
            // Validation errors
            const validationErrors = error.response.data.errors;
            const firstError = Object.values(validationErrors)[0] as string[];
            setError(key, firstError[0]);
        } else if (error.response?.data?.message) {
            setError(key, error.response.data.message);
        } else {
            setError(key, 'An unexpected error occurred');
        }

        setLoading(key, false);
    };

    return {
        loading: loading.value,
        errors: errors.value,
        isLoading,
        getError,
        hasError,
        setLoading,
        setError,
        clearError,
        clearAllErrors,
        handleApiError,
    };
}
