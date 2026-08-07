<script setup lang="ts">
import {
  CalendarIcon,
  ChevronRight,
  Video,
  MapPin,
  Calendar,
} from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();

const events = computed(() => props.stats?.upcoming_events || []);
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-2 flex flex-row items-center justify-between">
      <div class="flex items-center space-x-2">
        <CalendarIcon class="w-4 h-4 text-gray-600 dark:text-zinc-300" />
        <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
          >Work Calendar</CardTitle
        >
      </div>
    </CardHeader>
    <CardContent class="pt-2">
      <div class="flex justify-between items-center mb-6">
        <div
          v-for="(day, index) in ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']"
          :key="day"
          class="text-center"
        >
          <div
            class="w-8 h-8 rounded-lg flex items-center justify-center mb-1 text-sm font-medium"
            :class="
              index === 2
                ? 'bg-gray-900 text-white '
                : 'bg-gray-50 dark:bg-zinc-900/50 text-gray-600 dark:text-zinc-300'
            "
          >
            {{ index + 1 }}
          </div>
          <span
            class="text-[10px] text-gray-400 dark:text-zinc-500 uppercase font-medium"
            :class="
              index === 2 ? 'text-black border-b-2 border-black pb-1' : ''
            "
            >{{ day }}</span
          >
        </div>
      </div>

      <div class="space-y-3">
        <div
          v-for="evt in events"
          :key="evt.id"
          class="p-3 border border-gray-200 dark:border-zinc-800 border-2 rounded-xl flex items-center justify-between transition- cursor-pointer group"
        >
          <div class="flex items-center space-x-3">
            <div
              class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center"
            >
              <Calendar class="w-5 h-5" />
            </div>
            <div>
              <div class="text-xs font-bold text-gray-900 dark:text-zinc-100">
                {{ evt.description || evt.type }}
              </div>
              <div class="text-[10px] text-gray-500 dark:text-zinc-400 mt-0.5">{{ evt.date }}</div>
            </div>
          </div>
          <ChevronRight
            class="w-4 h-4 text-gray-300 group-hover:text-gray-500 dark:text-zinc-400"
          />
        </div>

        <div
          v-if="events.length === 0"
          class="text-center text-xs text-gray-400 dark:text-zinc-500 py-4 border border-dashed border-gray-200 dark:border-zinc-800 rounded-lg"
        >
          No upcoming events
        </div>
      </div>
    </CardContent>
  </Card>
</template>
