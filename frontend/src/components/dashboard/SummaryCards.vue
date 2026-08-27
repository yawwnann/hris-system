<script setup lang="ts">
import {
  Users,
  CheckCircle,
  FileText,
  Target,
  TrendingUp,
  TrendingDown,
} from "lucide-vue-next";
import { Card, CardContent } from "@/components/ui/card";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const attendanceRate = computed(() => {
  if (!props.stats || !props.stats.total_employees) return 0;
  return (
    (props.stats.present_today / props.stats.total_employees) *
    100
  ).toFixed(1);
});
</script>

<template>
  <div class="grid grid-cols-4 gap-4">
    <Card class="border-gray-200 dark:border-zinc-800 border-2">
      <CardContent class="p-5">
        <div class="flex items-center space-x-2 mb-4 text-gray-500 dark:text-zinc-400">
          <Users class="w-4 h-4" />
          <span class="font-medium text-sm">Total Karyawan</span>
        </div>
        <div class="text-3xl font-bold mb-2">
          {{ stats?.total_employees || 0 }}
        </div>
        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mt-2">
          Updated today
        </div>
      </CardContent>
    </Card>
    <Card class="border-gray-200 dark:border-zinc-800 border-2">
      <CardContent class="p-5">
        <div class="flex items-center space-x-2 mb-4 text-gray-500 dark:text-zinc-400">
          <CheckCircle class="w-4 h-4" />
          <span class="font-medium text-sm">Attendance Rate</span>
        </div>
        <div class="text-3xl font-bold mb-2">{{ attendanceRate }}%</div>
        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mt-2">
          Updated today
        </div>
      </CardContent>
    </Card>
    <Card class="border-gray-200 dark:border-zinc-800 border-2">
      <CardContent class="p-5">
        <div class="flex items-center space-x-2 mb-4 text-gray-500 dark:text-zinc-400">
          <FileText class="w-4 h-4" />
          <span class="font-medium text-sm">Pengajuan Cuti</span>
        </div>
        <div class="text-3xl font-bold mb-2">
          {{ stats?.pending_requests || 0 }}
        </div>
        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mt-2">
          Updated today
        </div>
      </CardContent>
    </Card>
    <Card class="border-gray-200 dark:border-zinc-800 border-2">
      <CardContent class="p-5">
        <div class="flex items-center space-x-2 mb-4 text-gray-500 dark:text-zinc-400">
          <Target class="w-4 h-4" />
          <span class="font-medium text-sm">Cuti Hari Ini</span>
        </div>
        <div class="text-3xl font-bold mb-2">
          {{ stats?.on_leave_today || 0
          }}<span class="text-lg text-gray-400 dark:text-zinc-500 font-normal"
            >/{{ stats?.total_employees || 0 }}</span
          >
        </div>
        <div class="text-xs text-gray-400 dark:text-zinc-500 font-medium mt-2">
          Updated today
        </div>
      </CardContent>
    </Card>
  </div>
</template>
