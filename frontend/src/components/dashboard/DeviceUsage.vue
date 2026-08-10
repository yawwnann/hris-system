<script setup lang="ts">
import { MoreVertical } from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const late = computed(() => props.stats?.late_today || 0);
const onLeave = computed(() => props.stats?.on_leave_today || 0);
const absent = computed(() => props.stats?.absent_today || 0);
const totalIssues = computed(() => late.value + onLeave.value + absent.value);

const latePct = computed(() => totalIssues.value ? (late.value / totalIssues.value) * 100 : 0);
const leavePct = computed(() => totalIssues.value ? (onLeave.value / totalIssues.value) * 100 : 0);
const absentPct = computed(() => totalIssues.value ? (absent.value / totalIssues.value) * 100 : 0);

const donutStyle = computed(() => {
  if (totalIssues.value === 0) {
    return { background: 'conic-gradient(#f3f4f6 100%)' }; // gray-100
  }
  const lateEnd = latePct.value;
  const leaveEnd = lateEnd + leavePct.value;
  return {
    background: `conic-gradient(#a855f7 0% ${lateEnd}%, #facc15 ${lateEnd}% ${leaveEnd}%, #f87171 ${leaveEnd}% 100%)`
  };
});
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-2 flex flex-row items-center justify-between">
      <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
        >Attendance Issues</CardTitle
      >
      <MoreVertical class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
    </CardHeader>
    <CardContent class="pt-4 flex items-center justify-between">
      <div
        class="relative w-24 h-24 rounded-full flex items-center justify-center shadow-inner"
        :style="donutStyle"
      >
        <div class="w-16 h-16 bg-white dark:bg-zinc-950 rounded-full flex items-center justify-center absolute shadow-sm">
          <div class="text-center">
            <div class="text-xl font-bold text-gray-900 dark:text-zinc-100 leading-tight">{{ totalIssues }}</div>
            <div class="text-[9px] text-gray-400 dark:text-zinc-500 uppercase tracking-wider">Total</div>
          </div>
        </div>
      </div>
      <div class="space-y-3 flex-1 ml-6">
        <div class="flex items-center justify-between space-x-4">
          <div class="flex items-center">
            <span class="w-1 h-4 bg-purple-500 rounded mr-2"></span
            ><span class="font-bold text-sm">{{ late }}</span>
          </div>
          <span class="text-xs text-gray-500 dark:text-zinc-400">Late</span>
        </div>
        <div class="flex items-center justify-between space-x-4">
          <div class="flex items-center">
            <span class="w-1 h-4 bg-yellow-400 rounded mr-2"></span
            ><span class="font-bold text-sm">{{ onLeave }}</span>
          </div>
          <span class="text-xs text-gray-500 dark:text-zinc-400">Leave</span>
        </div>
        <div class="flex items-center justify-between space-x-4">
          <div class="flex items-center">
            <span class="w-1 h-4 bg-red-400 rounded mr-2"></span
            ><span class="font-bold text-sm">{{ absent }}</span>
          </div>
          <span class="text-xs text-gray-500 dark:text-zinc-400">Absent</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
