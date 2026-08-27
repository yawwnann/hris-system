<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { Home } from 'lucide-vue-next';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

const route = useRoute();

const breadcrumbs = computed(() => {
const routeNameMap: Record<string, string> = {
  'Dashboard': 'Dashboard',
  'Employees': 'Employee Management',
  'Departments': 'Department Management',
  'Attendance': 'Attendance',
  'Leaves': 'Leave Requests',
  'Overtime': 'Overtime Requests',
};

  const currentName = route.name as string;
  const displayName = routeNameMap[currentName] || currentName;

  return {
    name: currentName,
    displayName: displayName,
    path: route.path
  };
});
</script>

<template>
  <Breadcrumb class="mb-4">
    <BreadcrumbList>
      <!-- Home Link -->
      <BreadcrumbItem>
        <BreadcrumbLink href="/" class="flex items-center text-gray-500 hover:text-indigo-600 transition-colors">
          <Home class="w-4 h-4 mr-1" />
          <span class="sr-only">Beranda</span>
        </BreadcrumbLink>
      </BreadcrumbItem>
      
      <!-- Separator -->
      <BreadcrumbSeparator v-if="breadcrumbs.name !== 'Dashboard'" />
      
      <!-- Current Page -->
      <BreadcrumbItem v-if="breadcrumbs.name !== 'Dashboard'">
        <BreadcrumbPage class="font-medium text-gray-900 dark:text-zinc-100">{{ breadcrumbs.displayName }}</BreadcrumbPage>
      </BreadcrumbItem>
    </BreadcrumbList>
  </Breadcrumb>
</template>
