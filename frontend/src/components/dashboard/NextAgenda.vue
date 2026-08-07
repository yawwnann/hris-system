<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();
const nextEvent = computed(() => {
  return props.stats?.upcoming_events && props.stats.upcoming_events.length > 0
    ? props.stats.upcoming_events[0]
    : null;
});

const totalEmp = computed(() => props.stats?.total_employees || 0);
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-2">
      <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
        >Your Next Agenda</CardTitle
      >
    </CardHeader>
    <CardContent>
      <div v-if="nextEvent">
        <div class="text-sm font-bold text-gray-900 dark:text-zinc-100">
          {{ nextEvent.description || nextEvent.type }}
        </div>
        <div class="text-xs text-gray-500 dark:text-zinc-400 mt-1 mb-4">
          Date: {{ nextEvent.date }}
        </div>

        <div class="flex items-center space-x-3 mb-6">
          <div class="flex -space-x-2">
            <Avatar class="w-7 h-7 border-2 border-white bg-blue-100 dark:bg-blue-900/40"
              ><AvatarFallback class="text-[10px]">A</AvatarFallback></Avatar
            >
            <Avatar class="w-7 h-7 border-2 border-white bg-green-100 dark:bg-green-900/40"
              ><AvatarFallback class="text-[10px]">B</AvatarFallback></Avatar
            >
            <Avatar class="w-7 h-7 border-2 border-white bg-orange-100 dark:bg-orange-900/40"
              ><AvatarFallback class="text-[10px]">C</AvatarFallback></Avatar
            >
            <div
              v-if="totalEmp > 3"
              class="w-7 h-7 rounded-full bg-gray-100 dark:bg-zinc-800 border-2 border-white flex items-center justify-center text-[10px] font-medium"
            >
              +{{ totalEmp - 3 }}
            </div>
          </div>
          <span class="text-xs text-gray-500 dark:text-zinc-400">{{ totalEmp }} Participants</span>
        </div>

        <Button
          class="w-full bg-black text-white hover:bg-gray-800 text-xs h-10"
          >See Details</Button
        >
      </div>
      <div
        v-else
        class="text-center py-4 text-xs text-gray-400 dark:text-zinc-500 border border-dashed rounded-lg mt-2"
      >
        No upcoming agendas
      </div>
    </CardContent>
  </Card>
</template>
