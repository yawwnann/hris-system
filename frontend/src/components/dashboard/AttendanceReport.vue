<script setup lang="ts">
import { MoreVertical } from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const chartData = computed(() => {
  if (!props.stats?.attendance_chart) return [];
  return [...props.stats.attendance_chart].reverse();
});

const onTimeArrivalRate = computed(() => {
  if (!chartData.value.length) return 0;
  let present = 0,
    total = 0;
  chartData.value.forEach((d: any) => {
    present += d.present;
    total += d.present + d.late + d.absent + d.on_leave;
  });
  if (total === 0) return 0;
  return ((present / total) * 100).toFixed(1);
});
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2 bg-gray-50 dark:bg-zinc-900/50 border-none">
    <CardHeader class="pb-2 flex flex-row items-center justify-between">
      <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
        >Attendance Report</CardTitle
      >
      <MoreVertical class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
    </CardHeader>
    <CardContent>
      <div class="text-3xl font-bold mb-1">{{ onTimeArrivalRate }}%</div>
      <div class="text-xs text-gray-500 dark:text-zinc-400 mb-6 flex items-center">
        On-time Arrival Rate
        <span class="text-green-600 dark:text-green-400 font-medium ml-1">Overall</span>
      </div>

      <div class="flex">
        <div class="w-8 space-y-[13px] pt-1">
          <div class="text-[10px] text-gray-400 dark:text-zinc-500">100%</div>
          <div class="text-[10px] text-gray-400 dark:text-zinc-500">75%</div>
          <div class="text-[10px] text-gray-400 dark:text-zinc-500">50%</div>
          <div class="text-[10px] text-gray-400 dark:text-zinc-500">25%</div>
          <div class="text-[10px] text-gray-400 dark:text-zinc-500">0%</div>
        </div>
        <div class="flex-1 grid grid-cols-7 gap-1">
          <div v-for="item in chartData" :key="item.date" class="space-y-1">
            <div
              class="h-6 rounded-sm transition-colors"
              :class="item.present > 0 ? 'bg-green-600' : 'bg-gray-200'"
            ></div>
            <div
              class="h-6 rounded-sm transition-colors"
              :class="item.present > 5 ? 'bg-green-50 dark:bg-green-900/200' : 'bg-gray-200'"
            ></div>
            <div
              class="h-6 rounded-sm transition-colors"
              :class="item.present > 10 ? 'bg-green-400' : 'bg-gray-200'"
            ></div>
            <div
              class="h-6 rounded-sm transition-colors"
              :class="item.present > 15 ? 'bg-green-300' : 'bg-gray-200'"
            ></div>
            <div
              class="h-6 rounded-sm transition-colors"
              :class="item.present > 20 ? 'bg-green-100 dark:bg-green-900/40' : 'bg-gray-200'"
            ></div>
            <div class="text-[10px] text-gray-400 dark:text-zinc-500 text-center mt-2">
              {{ item.day }}
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
