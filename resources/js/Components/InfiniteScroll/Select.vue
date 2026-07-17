<template>
    <div class="relative w-full" ref="dropdownContainer">
        <div
            @click="toggleDropdown"
            :class="[
                'w-full px-3 border rounded-lg bg-white cursor-pointer flex items-center justify-between transition-all duration-200',
                optionClassAttr ? optionClassAttr : 'py-2',
                isOpen ? 'border-green-600 ring-1 ring-green-600' : 'border-gray-300 hover:border-gray-400',
                { 'opacity-50 cursor-not-allowed': disabled },
                classes
            ]"
        >
            <span
                :class="[!displayText ? 'text-gray-400' : 'text-gray-900']"
                class="truncate"
            >
                {{ displayText || placeholder }}
            </span>
            <svg 
                class="w-4 h-4 text-gray-500 transition-transform duration-200 flex-shrink-0"
                :class="isOpen ? 'rotate-180' : ''"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div
            v-if="isOpen"
            ref="dropdownPanel"
            class="absolute z-50 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
            :style="{ width: dropdownWidth + 'px' }"
        >
            <div class="flex flex-col" style="max-height: 350px">
                <div class="p-2 border-b border-gray-200 bg-white sticky top-0 z-10">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-600 focus:ring-1 focus:ring-green-600 text-sm"
                            @input="handleSearch"
                            @click.stop
                        />
                    </div>
                </div>

                <div
                    ref="scrollContainer"
                    class="options-list"
                    style="max-height: 300px; overflow-y: auto; min-height: 200px;"
                    @scroll="handleScroll"
                >
                    <div
                        v-if="loading && items.length === 0"
                        class="flex items-center justify-center py-8"
                    >
                        <svg class="animate-spin h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div
                        v-else-if="!shouldLoad"
                        class="flex flex-col items-center justify-center py-8 text-gray-400"
                    >
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm">Select a dependency first</p>
                    </div>

                    <template v-else>
                        <template v-if="selectedItem && !isSelectedItemInList">
                            <div class="bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 border-b border-green-100">
                                SELECTED ITEM
                            </div>
                            <div
                                @click="selectOption(selectedItem)"
                                class="option-item px-3 py-2.5 cursor-pointer hover:bg-green-50 transition-colors border-b border-green-100 bg-green-50/50 text-sm"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        <span class="truncate font-medium">{{ getDisplayLabel(selectedItem) }}</span>
                                        <span v-if="selectedItem.total_points !== undefined" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full flex-shrink-0">
                                            {{ selectedItem.total_points }} pts
                                        </span>
                                    </div>
                                    <svg class="w-4 h-4 text-green-600 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="items.length" class="bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-500 border-b border-gray-200">
                                ALL ITEMS
                            </div>
                        </template>

                        <div
                            v-for="item in items"
                            :key="item[optionValue]"
                            @click="selectOption(item)"
                            class="option-item px-3 py-2.5 cursor-pointer hover:bg-green-50 transition-colors border-b border-gray-100 last:border-b-0 text-sm"
                            :class="{
                                'bg-green-50 text-green-700 font-medium': isSelected(item),
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <span class="truncate">{{ getDisplayLabel(item) }}</span>
                                    <!-- <span v-if="item.total_points !== undefined" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full flex-shrink-0">
                                        {{ item.total_points }} pts
                                    </span> -->
                                </div>
                                <svg
                                    v-if="isSelected(item)"
                                    class="w-4 h-4 text-green-600 ml-2 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <div
                            v-if="showDelayLoader"
                            class="flex items-center justify-center py-4 border-t border-gray-100"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-green-600">Loading</span>
                            </div>
                        </div>

                        <div
                            v-if="loadingMore"
                            class="flex items-center justify-center py-4 border-t border-gray-100"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-600">Loading more...</span>
                            </div>
                        </div>

                        <div
                            v-if="!hasMore && items.length > 0"
                            class="text-center py-2 text-xs text-gray-400 border-t border-gray-100"
                        >
                            <svg class="inline-block w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            End of list ({{ totalItems }} total)
                        </div>

                        <div
                            v-if="items.length === 0 && !loading"
                            class="flex flex-col items-center justify-center py-8 text-gray-400"
                        >
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <p class="text-sm">No {{ optionLabelPlural }} found</p>
                        </div>

                        <div ref="sentinel" class="h-px"></div>
                    </template>
                </div>

                <div
                    class="px-3 py-2 border-t border-gray-200 bg-gray-50 text-xs text-gray-500 flex justify-between items-center flex-shrink-0"
                >
                    <span>{{ items.length }} items loaded</span>
                    <span v-if="totalItems > 0">{{ totalItems }} total</span>
                </div>
            </div>
        </div>

        <div
            v-if="debug"
            class="mt-2 p-2 bg-gray-100 rounded text-xs text-gray-600 space-y-1"
        >
            <div class="font-semibold">Debug Info:</div>
            <div>Items: {{ items.length }}, Page: {{ currentPage }}/{{ lastPage }}</div>
            <div>Loading: {{ loading }}, Loading More: {{ loadingMore }}</div>
            <div>Has More: {{ hasMore }}, Total: {{ totalItems }}</div>
            <div>Selected: {{ internalValue }}</div>
            <div>Display Text: {{ displayText }}</div>
            <div>Selected in List: {{ isSelectedItemInList }}</div>
            <div>Should Load: {{ shouldLoad }}</div>
            <div v-if="error" class="text-red-500">Error: {{ error }}</div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from "vue";
import axios from "axios";

const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: null,
    },
    selectedId: {
        type: [String, Number, null],
        default: null,
    },
    endpoint: {
        type: String,
        required: true,
    },
    optionLabel: {
        type: String,
        default: "name",
    },
    optionClassAttr: {
        type: String,
        default: "py-1.5",
    },
    tempVal: {
        type: String,
        default: "",
    },
    optionValue: {
        type: String,
        default: "id",
    },
    placeholder: {
        type: String,
        default: "Select option",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    dependsOn: {
        type: [String, Number, null],
        default: null,
    },
    dependsOnField: {
        type: String,
        default: null,
    },
    debug: {
        type: Boolean,
        default: false,
    },
    initialLimit: {
        type: Number,
        default: 10,
    },
    lazyLoad: {
        type: Boolean,
        default: true,
    },
    scrollDelay: {
        type: Number,
        default: 500,
    },
    classes: {
        type: [String, Object, Array],
        default: "",
    },
    showPoints: {
        type: Boolean,
        default: true,
    },
    pointsLabel: {
        type: String,
        default: "pts",
    },
    optionLabelPlural: {
        type: String,
        default: "options",
    },
    searchPlaceholder: {
        type: String,
        default: "Search...",
    },
    displayFormat: {
        type: String,
        default: "name",
    },
});

const emit = defineEmits(["update:modelValue", "loaded", "select"]);

const dropdownContainer = ref(null);
const dropdownPanel = ref(null);
const scrollContainer = ref(null);
const sentinel = ref(null);


const isOpen = ref(false);
const searchQuery = ref("");
const items = ref([]);
const loading = ref(false);
const loadingMore = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const totalItems = ref(0);
const hasMore = ref(false);
const error = ref(null);
const searchTimeout = ref(null);
const scrollTimeout = ref(null);
const observer = ref(null);
const showDelayLoader = ref(false);
const dropdownWidth = ref(300);
const internalValue = ref(null);
const selectedItemCache = ref(null);
const dataLoaded = ref(false);

const initializeValue = () => {
    if (props.modelValue !== undefined && props.modelValue !== null) {
        internalValue.value = props.modelValue;
    } else if (props.selectedId !== undefined && props.selectedId !== null) {
        internalValue.value = props.selectedId;
        emit("update:modelValue", props.selectedId);
    } else {
        internalValue.value = null;
    }
};

const displayText = computed(() => {
    if (!internalValue.value) return props.tempVal || "";
    
    if (selectedItemCache.value) {
        return getDisplayLabel(selectedItemCache.value);
    }
    
    return props.tempVal || "";
});

const getDisplayLabel = (item) => {
    if (!item) return "";
    
    const name = item[props.optionLabel] || "";
    
    if (props.showPoints && item.total_points !== undefined) {
        return `${name} (${item.total_points} ${props.pointsLabel})`;
    }
    
    return name;
};

const selectedItem = computed(() => selectedItemCache.value);

const isSelectedItemInList = computed(() => {
    if (!internalValue.value || !items.value.length) return false;
    return items.value.some(
        (i) => String(i[props.optionValue]) === String(internalValue.value)
    );
});

const shouldLoad = computed(() => {
    if (!props.dependsOnField) return true;
    return props.dependsOn !== null && props.dependsOn !== undefined;
});

const toggleDropdown = (event) => {
    if (props.disabled) return;

    const selectElement = event.currentTarget;
    dropdownWidth.value = selectElement.offsetWidth;
    isOpen.value = !isOpen.value;
    
    if (isOpen.value) {
        onShow();
    } else {
        onHide();
    }
};

const onShow = async () => {
    await nextTick();

    if (!dataLoaded.value && shouldLoad.value && !loading.value) {
        await loadItems(true);
        dataLoaded.value = true;
    }

    setupIntersectionObserver();
};

const onHide = () => {
    searchQuery.value = "";
    observer.value?.disconnect();
    clearTimeout(scrollTimeout.value);
    showDelayLoader.value = false;
};

const setupIntersectionObserver = () => {
    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && hasMore.value && !loadingMore.value && !loading.value) {
                    startDelayTimer();
                }
            });
        },
        { root: scrollContainer.value, threshold: 0.1 }
    );

    if (sentinel.value) observer.value.observe(sentinel.value);
};

const startDelayTimer = () => {
    clearTimeout(scrollTimeout.value);
    showDelayLoader.value = true;

    scrollTimeout.value = setTimeout(() => {
        showDelayLoader.value = false;
        loadMore();
    }, props.scrollDelay);
};

const isSelected = (item) => {
    if (!internalValue.value) return false;
    return String(internalValue.value) === String(item[props.optionValue]);
};

const selectOption = (item) => {
    const value = item[props.optionValue];
    internalValue.value = value;
    selectedItemCache.value = item;
    emit("update:modelValue", value);
    emit("select", item);
    isOpen.value = false;
};

const handleSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        resetAndLoad();
    }, 300);
};


const loadMore = () => {
    if (hasMore.value && !loadingMore.value && !loading.value) {
        currentPage.value++;
        loadItems(false);
    }
};

const resetAndLoad = () => {
    items.value = [];
    currentPage.value = 1;
    hasMore.value = false;
    totalItems.value = 0;
    error.value = null;
    dataLoaded.value = false;
    clearTimeout(scrollTimeout.value);
    loadItems(true);
};

const loadItems = async (reset = false) => {
    if (!shouldLoad.value) {
        items.value = [];
        return;
    }

    if (reset) {
        currentPage.value = 1;
        items.value = [];
        loading.value = true;
    } else {
        loadingMore.value = true;
    }

    try {
        const params = {
            page: currentPage.value,
            limit: props.initialLimit,
        };

        if (searchQuery.value) params.search = searchQuery.value;
        if (internalValue.value) params.selected_ids = [internalValue.value];
        if (props.dependsOnField && props.dependsOn) {
            params[props.dependsOnField] = props.dependsOn;
        }

        Object.assign(params, props.filters);

        const response = await axios.get(props.endpoint, { params });
        const responseData = response.data;
        const newItems = responseData.data || [];
        
        currentPage.value = responseData.current_page || 1;
        lastPage.value = responseData.last_page || 1;
        totalItems.value = responseData.total || newItems.length;
        hasMore.value = currentPage.value < lastPage.value;

        if (internalValue.value) {
            const selected = newItems.find(
                (i) => String(i[props.optionValue]) === String(internalValue.value)
            );
            if (selected) {
                selectedItemCache.value = selected;
            } else if (!reset && !searchQuery.value) {
                await fetchSelectedItem();
            }
        }

        if (reset) {
            items.value = newItems;
        } else {
            const existingIds = new Set(items.value.map(i => String(i[props.optionValue])));
            const uniqueNewItems = newItems.filter(i => !existingIds.has(String(i[props.optionValue])));
            items.value = [...items.value, ...uniqueNewItems];
        }

        emit("loaded", items.value);
    } catch (err) {
        console.error("Error loading items:", err);
        error.value = err.response?.data?.message || err.message;
        if (reset) items.value = [];
    } finally {
        loading.value = false;
        loadingMore.value = false;
        showDelayLoader.value = false;
    }
};

const fetchSelectedItem = async () => {
    if (!internalValue.value) return;
    
    try {
        const response = await axios.get(props.endpoint, {
            params: {
                selected_ids: [internalValue.value],
                limit: 1
            }
        });
        const data = response.data.data || [];
        if (data.length > 0) {
            selectedItemCache.value = data[0];
        }
    } catch (err) {
        console.error("Error fetching selected item:", err);
    }
};

const handleClickOutside = (event) => {
    if (dropdownContainer.value && !dropdownContainer.value.contains(event.target)) {
        isOpen.value = false;
    }
};

watch(
    () => props.dependsOn,
    () => {
        items.value = [];
        internalValue.value = null;
        selectedItemCache.value = null;
        dataLoaded.value = false;
        emit("update:modelValue", null);
    },
    { immediate: true }
);

watch(
    () => props.filters,
    () => {
        resetAndLoad();
    },
    { deep: true }
);

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            internalValue.value = newVal;
            if (!selectedItemCache.value || String(selectedItemCache.value[props.optionValue]) !== String(newVal)) {
                fetchSelectedItem();
            }
        } else {
            internalValue.value = null;
            selectedItemCache.value = null;
        }
    },
    { immediate: true }
);

watch(
    () => props.selectedId,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            internalValue.value = newVal;
            emit("update:modelValue", newVal);
            if (!selectedItemCache.value || String(selectedItemCache.value[props.optionValue]) !== String(newVal)) {
                fetchSelectedItem();
            }
        }
    },
    { immediate: true }
);

onMounted(() => {
    initializeValue();

    if (!props.lazyLoad && shouldLoad.value) {
        loadItems(true);
        dataLoaded.value = true;
    }
    
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    observer.value?.disconnect();
    clearTimeout(scrollTimeout.value);
    clearTimeout(searchTimeout.value);
    document.removeEventListener("click", handleClickOutside);
});

defineExpose({
    refresh: resetAndLoad,
    items,
    loading,
    hasMore,
    loadMore,
});
</script>

<style scoped>
.options-list {
    overflow-y: auto;
    background: white;
}

.option-item {
    transition: all 0.2s;
}

.option-item:hover {
    background-color: #f0fdf4;
}

.option-item.selected {
    background-color: #f0fdf4;
    color: #00786F;
}

/* Custom scrollbar */
.options-list::-webkit-scrollbar {
    width: 6px;
}

.options-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.options-list::-webkit-scrollbar-thumb {
    background: #00786F;
    border-radius: 3px;
}

.options-list::-webkit-scrollbar-thumb:hover {
    background: #005a54;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

:deep(.p-invalid) {
    border-color: #ef4444 !important;
}

.bg-green-50 {
    background-color: #f0fdf4;
}

.bg-green-100 {
    background-color: #dcfce7;
}

.text-green-600 {
    color: #00786F;
}

.text-green-700 {
    color: #005a54;
}

.border-green-100 {
    border-color: #dcfce7;
}

.border-green-600 {
    border-color: #00786F;
}

.ring-green-600 {
    --tw-ring-color: #00786F;
}

.hover\:bg-green-50:hover {
    background-color: #f0fdf4;
}

.focus\:border-green-600:focus {
    border-color: #00786F;
}

.focus\:ring-green-600:focus {
    --tw-ring-color: #00786F;
}

.text-green-600 {
    color: #00786F;
}
</style>