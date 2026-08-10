<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Plane, Stethoscope, Cake, PartyPopper } from "lucide-vue-next";

import { computed } from "vue";

const props = defineProps<{
  stats?: any
}>();

const awayToday = computed(() => {
  return props.stats?.away_today?.map((a: any) => ({
    id: a.id,
    user: a.user,
    type: a.type,
    icon: a.is_sick ? Stethoscope : Plane,
    color: a.is_sick ? 'text-red-500' : 'text-blue-500',
    bg: a.is_sick ? 'bg-red-100 dark:bg-red-900/30' : 'bg-blue-100 dark:bg-blue-900/30'
  })) || [];
});

const celebrations = computed(() => {
  return props.stats?.celebrations?.map((c: any) => ({
    id: c.id,
    user: c.user,
    type: c.type,
    icon: c.type.includes('Birthday') ? Cake : PartyPopper,
    color: c.type.includes('Birthday') ? 'text-pink-500' : 'text-amber-500',
    bg: c.type.includes('Birthday') ? 'bg-pink-100 dark:bg-pink-900/30' : 'bg-amber-100 dark:bg-amber-900/30'
  })) || [];
});
</script>

<template>
  <Card class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 shadow-sm overflow-hidden">
    <CardHeader class="pb-3 border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50">
      <CardTitle class="text-base font-semibold text-gray-900 dark:text-zinc-100 flex items-center">
        <Plane class="w-4 h-4 mr-2 text-indigo-500" /> Employee Status
      </CardTitle>
    </CardHeader>
    <CardContent class="p-4 space-y-5">
      
      <!-- Away Today -->
      <div>
        <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Away Today ({{ awayToday.length }})</h4>
        <div v-if="awayToday.length === 0" class="text-xs text-gray-400 italic">No one is away today.</div>
        <div class="space-y-3 max-h-[140px] overflow-y-auto pr-2 custom-scrollbar">
          <div v-for="person in awayToday" :key="person.id" class="flex items-center space-x-3 group">
            <div :class="`w-8 h-8 rounded-full flex items-center justify-center transition-transform group-hover:scale-110 ${person.bg} ${person.color}`">
              <component :is="person.icon" class="w-4 h-4" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 transition-colors">{{ person.user }}</p>
              <p class="text-[11px] text-gray-500 truncate">{{ person.type }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Celebrations -->
      <div class="pt-4 border-t border-gray-100 dark:border-zinc-800">
        <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider mb-3">Upcoming Celebrations ({{ celebrations.length }})</h4>
        <div v-if="celebrations.length === 0" class="text-xs text-gray-400 italic">No upcoming celebrations in the next 7 days.</div>
        <div class="space-y-3 max-h-[180px] overflow-y-auto pr-2 custom-scrollbar">
          <div v-for="person in celebrations" :key="person.id" class="flex items-center space-x-3 group">
            <div :class="`w-8 h-8 rounded-full flex items-center justify-center transition-transform group-hover:scale-110 ${person.bg} ${person.color}`">
              <component :is="person.icon" class="w-4 h-4" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate group-hover:text-indigo-600 transition-colors">{{ person.user }}</p>
              <p class="text-[11px] text-gray-500 truncate">{{ person.type }}</p>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 4px;
}
:root.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3f3f46;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: #d1d5db;
}
:root.dark .custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: #52525b;
}
</style>
