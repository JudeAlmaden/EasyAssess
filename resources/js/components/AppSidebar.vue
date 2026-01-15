<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {LayoutGrid, FileText, FileQuestion, ListChecks  } from 'lucide-vue-next';

import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
  {
    title: 'Homepage',
    href: '/Homepage',
    icon: LayoutGrid,
  },
  {
    title: 'Respondents',
    href: '/dictionary',
    icon: FileText,
  },
  {
    title: 'Answer Sheets',
    href: '/omr-sheets',
    icon: FileQuestion,
  },
  {
    title: 'My Assessments',
    href: '/assessments',
    icon: ListChecks,
  },
//  {
//    title: 'Payment',
//   href: '/payment',
//  icon: FileText,
// },
];


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
