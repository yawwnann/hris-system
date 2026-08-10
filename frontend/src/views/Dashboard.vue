<script setup lang="ts">
import { CalendarIcon, Download } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";

import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import SummaryCards from "@/components/dashboard/SummaryCards.vue";
import EmployeeOverview from "@/components/dashboard/EmployeeOverview.vue";
import AttendanceOverviewBar from "@/components/dashboard/AttendanceOverviewBar.vue";
import EmployeeTable from "@/components/dashboard/EmployeeTable.vue";
import DeptPerformance from "@/components/dashboard/DeptPerformance.vue";
import DeviceUsage from "@/components/dashboard/DeviceUsage.vue";
import WorkCalendar from "@/components/dashboard/WorkCalendar.vue";
import NextAgenda from "@/components/dashboard/NextAgenda.vue";
import AttendanceReport from "@/components/dashboard/AttendanceReport.vue";
import QuickApprovals from "@/components/dashboard/QuickApprovals.vue";
import EmployeeStatus from "@/components/dashboard/EmployeeStatus.vue";

import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import api from "@/lib/axios";

const authStore = useAuthStore();
const stats = ref<any>(null);
const loading = ref(true);

const fetchDashboardStats = async () => {
  try {
    const endpoint =
      authStore.user?.role === "admin"
        ? "/dashboard/admin"
        : "/dashboard/employee";
    const { data } = await api.get(endpoint);
    stats.value = data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const currentDateRange = computed(() => {
  const date = new Date();
  const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
  const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
  
  const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'short', year: 'numeric' };
  return `${firstDay.toLocaleDateString('en-GB', options)} - ${lastDay.toLocaleDateString('en-GB', options)}`;
});

onMounted(() => {
  fetchDashboardStats();
});
</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <!-- SIDEBAR -->
    <AppSidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0">
      <!-- HEADER -->
      <AppHeader />

      <ScrollArea class="flex-1 p-8">
        <div class="flex items-center justify-between mb-8">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">HR Dashboard</h1>
          <div class="flex items-center space-x-2">
            <Button
              variant="outline"
              class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-600 dark:text-gray-300 h-9"
            >
              <CalendarIcon class="w-4 h-4 mr-2" /> {{ currentDateRange }}
            </Button>
            <Button
              size="icon"
              class="h-9 w-9 bg-black dark:bg-white dark:bg-zinc-950 text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gray-200"
            >
              <Download class="w-4 h-4" />
            </Button>
          </div>
        </div>

        <div
          v-if="loading"
          class="flex justify-center items-center py-20 text-gray-500 dark:text-zinc-400"
        >
          Loading dashboard...
        </div>

        <div v-else-if="stats" class="grid grid-cols-1 xl:grid-cols-4 gap-6">
          <!-- LEFT CONTENT (Col Span 3) -->
          <div class="xl:col-span-3 space-y-6">
            <SummaryCards :stats="stats" />
            <QuickApprovals :stats="stats" v-if="authStore.user?.role === 'admin'" />
            <EmployeeOverview :stats="stats" />
            <AttendanceOverviewBar :stats="stats" />
            <EmployeeTable />
            <DeptPerformance :stats="stats" />
          </div>

          <!-- RIGHT CONTENT (Col Span 1) -->
          <div class="space-y-6">
            <EmployeeStatus :stats="stats" />
            <DeviceUsage :stats="stats" />
            <WorkCalendar :stats="stats" />
            <NextAgenda :stats="stats" />
            <AttendanceReport :stats="stats" />
          </div>
        </div>
      </ScrollArea>
    </main>
  </div>
</template>
