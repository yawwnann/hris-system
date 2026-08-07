<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const chartData = computed(() => {
  if (!props.stats?.attendance_chart) return [];

  const total = props.stats.total_employees || 1;

  // attendance_chart has dates from 6 days ago to today.
  return [...props.stats.attendance_chart].reverse().map((item) => {
    return {
      ...item,
      absentHeight: ((item.absent / total) * 100).toFixed(1) + "%",
      lateHeight: ((item.late / total) * 100).toFixed(1) + "%",
      presentHeight: ((item.present / total) * 100).toFixed(1) + "%",
    };
  });
});

const attendanceRate = computed(() => {
  if (!props.stats || !props.stats.total_employees) return 0;
  return (
    (props.stats.present_today / props.stats.total_employees) *
    100
  ).toFixed(1);
});
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-6 flex flex-row items-center justify-between">
      <div>
        <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
          >Attendance Overview</CardTitle
        >
        <div class="flex items-center space-x-6 mt-2">
          <div>
            <span class="text-2xl font-bold">{{ attendanceRate }}%</span>
            <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">Attendance Rate</p>
          </div>
          <div>
            <span class="text-2xl font-bold"
              >{{ stats?.present_today || 0
              }}<span class="text-gray-400 dark:text-zinc-500 text-lg"
                >/{{ stats?.total_employees || 0 }}</span
              ></span
            >
            <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">Today's Attendance</p>
          </div>
        </div>
      </div>
      <div class="flex flex-col items-end">
        <Button
          variant="outline"
          size="sm"
          class="h-8 text-xs font-medium bg-white dark:bg-zinc-950"
          >See Detail</Button
        >
        <div
          class="flex items-center space-x-4 mt-4 text-xs font-medium text-gray-500 dark:text-zinc-400"
        >
          <div class="text-center">
            <div class="w-6 h-1 bg-indigo-500 rounded mb-1 mx-auto"></div>
            On-Time
          </div>
          <div class="text-center">
            <div class="w-6 h-1 bg-blue-400 rounded mb-1 mx-auto"></div>
            Late
          </div>
          <div class="text-center">
            <div class="w-6 h-1 bg-red-400 rounded mb-1 mx-auto"></div>
            Absent
          </div>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <div class="h-40 flex items-end justify-between px-4 mt-2">
        <div
          v-for="item in chartData"
          :key="item.date"
          class="flex flex-col items-center flex-1 h-full"
        >
          <div
            class="w-12 flex flex-col justify-end bg-gray-100 dark:bg-zinc-800 rounded-t-sm overflow-hidden h-full"
          >
            <div
              class="bg-red-400 w-full transition-all duration-500"
              :style="{ height: item.absentHeight }"
            ></div>
            <div
              class="bg-blue-400 w-full transition-all duration-500"
              :style="{ height: item.lateHeight }"
            ></div>
            <div
              class="bg-indigo-500 w-full transition-all duration-500"
              :style="{ height: item.presentHeight }"
            ></div>
          </div>
          <span class="text-xs text-gray-500 dark:text-zinc-400 mt-3 font-medium">{{
            item.day
          }}</span>
        </div>

        <div
          v-if="chartData.length === 0"
          class="w-full text-center text-gray-400 dark:text-zinc-500 h-full flex items-center justify-center"
        >
          No chart data available
        </div>
      </div>
    </CardContent>
  </Card>
</template>
