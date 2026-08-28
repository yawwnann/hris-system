<script setup lang="ts">
import { MoreVertical, TrendingUp, TrendingDown } from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const totalEmp = computed(() => props.stats?.total_employees || 0);
const present = computed(() => props.stats?.present_today || 0);
const late = computed(() => props.stats?.late_today || 0);
const absent = computed(() => props.stats?.absent_today || 0);
const onLeave = computed(() => props.stats?.on_leave_today || 0);

const attendanceRate = computed(() => {
  if (totalEmp.value === 0) return 0;
  return ((present.value / totalEmp.value) * 100).toFixed(1);
});

const onTimePct = computed(() =>
  totalEmp.value ? ((present.value - late.value) / totalEmp.value) * 100 : 0,
);
const latePct = computed(() =>
  totalEmp.value ? (late.value / totalEmp.value) * 100 : 0,
);
const leavePct = computed(() =>
  totalEmp.value ? (onLeave.value / totalEmp.value) * 100 : 0,
);
const absentPct = computed(() =>
  totalEmp.value ? (absent.value / totalEmp.value) * 100 : 0,
);
</script>

<template>
  <div class="grid grid-cols-2 gap-6">
    <!-- Total Employees Split -->
    <Card class="border-gray-200 dark:border-zinc-800 border-2">
      <CardHeader class="pb-2 flex flex-row items-center justify-between">
        <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
          >Ringkasan Total Karyawan</CardTitle
        >
        <MoreVertical class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
      </CardHeader>
      <CardContent class="space-y-4">
        <div
          class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 flex items-center justify-between border border-green-100 dark:border-green-900/30"
        >
          <div class="flex items-center space-x-3">
            <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ totalEmp }}</span>
            <Badge
              variant="outline"
              class="bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400 border-none text-[10px] px-1.5 py-0.5 flex items-center gap-0.5"
              >Aktif</Badge
            >
          </div>
          <span class="text-sm text-green-800 dark:text-green-400 font-medium"
            >Karyawan Terdaftar</span
          >
        </div>

        <div
          class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 flex items-center justify-between border border-orange-100 dark:border-orange-900/30"
        >
          <div class="flex items-center space-x-3">
            <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ absent }}</span>
            <Badge
              variant="outline"
              class="bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-400 border-none text-[10px] px-1.5 py-0.5 flex items-center gap-0.5"
              >Hari Ini</Badge
            >
          </div>
          <span class="text-sm text-orange-800 dark:text-orange-400 font-medium"
            >Karyawan Absen</span
          >
        </div>
      </CardContent>
    </Card>

    <!-- Attendance Overview Progress -->
    <Card class="border-gray-200 dark:border-zinc-800 border-2">
      <CardHeader class="pb-2 flex flex-row items-center justify-between">
        <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
          >Status Kehadiran Hari Ini</CardTitle
        >
        <MoreVertical class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
      </CardHeader>
      <CardContent>
        <div class="flex items-end space-x-3 mb-6">
          <span class="text-4xl font-bold text-gray-900 dark:text-zinc-100"
            >{{ attendanceRate }}%</span
          >
          <span
            class="text-sm text-green-600 dark:text-green-400 font-medium pb-1 flex items-center gap-1"
            >Hadir Hari Ini</span
          >
        </div>

        <div
          class="flex h-3 w-full rounded-full overflow-hidden gap-1 mb-4 bg-gray-100 dark:bg-zinc-800"
        >
          <div class="bg-yellow-400" :style="{ width: `${leavePct}%` }"></div>
          <div class="bg-blue-400" :style="{ width: `${latePct}%` }"></div>
          <div class="bg-orange-400" :style="{ width: `${onTimePct}%` }"></div>
          <div class="bg-red-400" :style="{ width: `${absentPct}%` }"></div>
        </div>

        <div
          class="flex items-center space-x-4 text-xs text-gray-500 dark:text-zinc-400 flex-wrap gap-y-2"
        >
          <div class="flex items-center">
            <span class="w-2 h-2 rounded-full bg-orange-400 mr-2"></span> Tepat
            Waktu ({{ present - late }})
          </div>
          <div class="flex items-center">
            <span class="w-2 h-2 rounded-full bg-blue-400 mr-2"></span> Terlambat ({{
              late
            }})
          </div>
          <div class="flex items-center">
            <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span> Cuti
            ({{ onLeave }})
          </div>
          <div class="flex items-center">
            <span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span> Absen
            ({{ absent }})
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
