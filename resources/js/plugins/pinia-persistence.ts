// resources/js/plugins/pinia-persistence.ts
import { PiniaPluginContext } from 'pinia';

export interface PersistedStateOptions {
    key?: string;
    storage?: Storage;
    paths?: string[];
}

export function createPersistedState(options: PersistedStateOptions = {}) {
    return (context: PiniaPluginContext) => {
        const { store, options: storeOptions } = context;

        const shouldPersist = (storeOptions as any).persist;
        if (!shouldPersist) return;

        const { key = store.$id, storage = localStorage, paths = [] } = typeof shouldPersist === 'object' ? shouldPersist : options;

        const storedState = storage.getItem(key);
        if (storedState) {
            try {
                const parsedState = JSON.parse(storedState);
                store.$patch(parsedState);
            } catch (error) {
                console.error(`Failed to parse persisted state for store ${store.$id}:`, error);
            }
        }

        store.$subscribe((mutation, state) => {
            try {
                const stateToStore =
                    paths.length > 0
                        ? paths.reduce((acc: any, path: any) => {
                              acc[path] = state[path];
                              return acc;
                          }, {} as any)
                        : state;

                storage.setItem(key, JSON.stringify(stateToStore));
            } catch (error) {
                console.error(`Failed to persist state for store ${store.$id}:`, error);
            }
        });
    };
}
