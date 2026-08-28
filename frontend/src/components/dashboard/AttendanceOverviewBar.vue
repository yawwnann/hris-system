<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { computed, ref } from "vue";

const props = defineProps<{ stats: any }>();

const hoveredIndex = ref<number | null>(null);
const tooltipX = ref(0);
const tooltipY = ref(0);

const total = computed(() => props.stats?.total_employees || 1);

const chartData = computed(() => {
  if (!props.stats?.attendance_chart) return [];

  return props.stats.attendance_chart.map((item: any) => {
    const presentPct = (item.present / total.value) * 100;
    const latePct = (item.late / total.value) * 100;
    const absentPct = (item.absent / total.value) * 100;
    return {
      ...item,
      presentPct: presentPct.toFixed(1),
      latePct: latePct.toFixed(1),
      absentPct: absentPct.toFixed(1),
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

const maxValue = computed(() => total.value);

const yAxisLabels = computed(() => {
  const max = maxValue.value;
  return [max, Math.round(max * 0.75), Math.round(max * 0.5), Math.round(max * 0.25), 0];
});

const onBarHover = (index: number, event: MouseEvent) => {
  hoveredIndex.value = index;
  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
  tooltipX.value = rect.left + rect.width / 2;
  tooltipY.value = rect.top;
};

const onBarLeave = () => {
  hoveredIndex.value = null;
};
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-4 flex flex-row items-center justify-between">
      <div>
        <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
          >Ringkasan Absensi</CardTitle
        >
        <div class="flex items-center space-x-6 mt-2">
          <div>
            <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ attendanceRate }}%</span>
            <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">Tingkat Kehadiran</p>
          </div>
          <div>
            <span class="text-2xl font-bold text-gray-900 dark:text-zinc-100"
              >{{ stats?.present_today || 0
              }}<span class="text-gray-400 dark:text-zinc-500 text-lg"
                >/{{ stats?.total_employees || 0 }}</span
              ></span
            >
            <p class="text-xs text-gray-400 dark:text-zinc-500 mt-1">Kehadiran Hari Ini</p>
          </div>
        </div>
      </div>
      <div class="flex flex-col items-end">
        <Button
          variant="outline"
          size="sm"
          class="h-8 text-xs font-medium bg-white dark:bg-zinc-950"
          >Lihat Detail</Button
        >
        <div
          class="flex items-center space-x-4 mt-4 text-xs font-medium text-gray-500 dark:text-zinc-400"
        >
          <div class="flex items-center">
            <span class="w-3 h-3 rounded-sm bg-emerald-500 mr-1.5"></span>
            Tepat Waktu
          </div>
          <div class="flex items-center">
            <span class="w-3 h-3 rounded-sm bg-amber-400 mr-1.5"></span>
            Terlambat
          </div>
          <div class="flex items-center">
            <span class="w-3 h-3 rounded-sm bg-red-400 mr-1.5"></span>
            Absen
          </div>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <div class="relative">
        <!-- Chart Area -->
        <div class="flex" v-if="chartData.length > 0">
          <!-- Y-Axis Labels -->
          <div class="flex flex-col justify-between h-48 pr-3 text-right">
            <span v-for="label in yAxisLabels" :key="label" class="text-[10px] text-gray-400 dark:text-zinc-500 leading-none">
              {{ label }}
            </span>
          </div>

          <!-- Bars Container -->
          <div class="flex-1 relative">
            <!-- Grid Lines -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
              <div v-for="(_, i) in yAxisLabels" :key="'grid-'+i" class="border-b border-dashed border-gray-100 dark:border-zinc-800/60"></div>
            </div>

            <!-- Bars -->
            <div class="flex items-end justify-around h-48 relative z-10">
              <div
                v-for="(item, index) in chartData"
                :key="item.date"
                class="flex flex-col items-center flex-1 h-full justify-end cursor-pointer group"
                @mouseenter="onBarHover(Number(index), $event)"
                @mouseleave="onBarLeave"
              >
                <!-- Stacked Bar -->
                <div class="w-8 lg:w-10 flex flex-col justify-end h-full gap-[1px]">
                  <div
                    class="bg-red-400 w-full rounded-t-md transition-all duration-500 group-hover:opacity-90"
                    :style="{ height: item.absentPct + '%' }"
                  ></div>
                  <div
                    class="bg-amber-400 w-full transition-all duration-500 group-hover:opacity-90"
                    :style="{ height: item.latePct + '%' }"
                  ></div>
                  <div
                    class="bg-emerald-500 w-full rounded-b-md transition-all duration-500 group-hover:opacity-90"
                    :style="{ height: item.presentPct + '%' }"
                  ></div>
                </div>

                <!-- Day Label -->
                <span
                  class="text-[11px] mt-2.5 font-semibold transition-colors"
                  :class="hoveredIndex === index ? 'text-gray-900 dark:text-zinc-100' : 'text-gray-400 dark:text-zinc-500'"
                >
                  {{ item.day }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="h-48 w-full text-center text-gray-400 dark:text-zinc-500 flex items-center justify-center"
        >
          Tidak ada data grafik
        </div>

        <!-- Tooltip -->
        <Teleport to="body">
          <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
          >
            <div
              v-if="hoveredIndex !== null && chartData[hoveredIndex]"
              class="fixed z-[9999] pointer-events-none"
              :style="{
                left: tooltipX + 'px',
                top: (tooltipY - 8) + 'px',
                transform: 'translate(-50%, -100%)'
              }"
            >
              <div class="bg-gray-900 dark:bg-zinc-800 text-white rounded-lg shadow-xl px-4 py-3 text-xs min-w-[160px]">
                <div class="font-semibold text-sm mb-2 text-center border-b border-gray-700 dark:border-zinc-600 pb-2">
                  {{ chartData[hoveredIndex].day }} — {{ chartData[hoveredIndex].date }}
                </div>
                <div class="space-y-1.5">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                      <span class="text-gray-300">Tepat Waktu</span>
                    </div>
                    <span class="font-bold">{{ chartData[hoveredIndex].present }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <span class="w-2 h-2 rounded-full bg-amber-400 mr-2"></span>
                      <span class="text-gray-300">Terlambat</span>
                    </div>
                    <span class="font-bold">{{ chartData[hoveredIndex].late }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <span class="w-2 h-2 rounded-full bg-red-400 mr-2"></span>
                      <span class="text-gray-300">Absen</span>
                    </div>
                    <span class="font-bold">{{ chartData[hoveredIndex].absent }}</span>
                  </div>
                </div>
                <!-- Arrow -->
                <div class="absolute left-1/2 -translate-x-1/2 -bottom-1.5 w-3 h-3 bg-gray-900 dark:bg-zinc-800 rotate-45"></div>
              </div>
            </div>
          </Transition>
        </Teleport>
      </div>
    </CardContent>
  </Card>
</template>
