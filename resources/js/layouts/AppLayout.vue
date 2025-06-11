<script setup lang="ts">
import Sidebar from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <Sidebar :breadcrumbs="breadcrumbs">
        <slot />
    </Sidebar>
</template>
