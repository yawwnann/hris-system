<script setup lang="ts">
import { MoreVertical } from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const colors = [
  "bg-blue-400",
  "bg-yellow-400",
  "bg-purple-300",
  "bg-green-400",
  "bg-red-300",
  "bg-orange-300",
  "bg-pink-300",
];

const divisions = computed(() => {
  if (!props.stats?.division_stats) return [];
  const total = props.stats.total_employees || 1;
  return props.stats.division_stats.map((div: any, i: number) => ({
    name: div.division?.name || "Unknown",
    count: div.count,
    percent: ((div.count / total) * 100).toFixed(1),
    color: colors[i % colors.length],
  }));
});
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-2 flex flex-row items-center justify-between">
      <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
        >Karyawan per Departemen</CardTitle
      >
      <MoreVertical class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
    </CardHeader>
    <CardContent>
      <div
        class="flex w-full h-12 rounded-lg overflow-hidden gap-1 mb-8 bg-gray-50 dark:bg-zinc-900/50"
      >
        <div
          v-for="div in divisions"
          :key="div.name"
          :class="div.color"
          :style="{ width: `${div.percent}%` }"
        ></div>
        <div
          v-if="divisions.length === 0"
          class="w-full text-center text-xs text-gray-400 dark:text-zinc-500 leading-[3rem]"
        >
          Tidak ada data
        </div>
      </div>

      <div class="grid grid-cols-3 gap-6">
        <div v-for="div in divisions" :key="div.name">
          <div class="flex items-center text-xs text-gray-500 dark:text-zinc-400 mb-1">
            <span class="w-2 h-2 rounded-full mr-2" :class="div.color"></span>
            {{ div.name }}
          </div>
          <div class="text-xl font-bold">
            {{ div.count }}
            <span class="text-xs text-gray-400 dark:text-zinc-500 font-normal"
              >({{ div.percent }}%)</span
            >
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
