<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { computed } from "vue";

const props = defineProps<{ stats: any }>();
const nextAcara = computed(() => {
  return props.stats?.upcoming_events && props.stats.upcoming_events.length > 0
    ? props.stats.upcoming_events[0]
    : null;
});

const totalEmp = computed(() => nextAcara.value?.total_participants || 0);

const getInitials = (name: string) => {
  if (!name) return 'A';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-2">
      <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
        >Agenda Anda Berikutnya</CardTitle
      >
    </CardHeader>
    <CardContent>
      <div v-if="nextAcara">
        <div class="text-sm font-bold text-gray-900 dark:text-zinc-100">
          {{ nextAcara.description || nextAcara.type }}
        </div>
        <div class="text-xs text-gray-500 dark:text-zinc-400 mt-1 mb-4">
          Tanggal: {{ nextAcara.date }}
        </div>

        <div v-if="totalEmp > 0" class="flex items-center space-x-3 mb-6">
          <div class="flex -space-x-2">
            <Avatar v-for="(div, idx) in (nextAcara.divisions || []).slice(0, 3)" :key="'d'+idx" class="w-7 h-7 border-2 border-white dark:border-zinc-950 bg-green-100 dark:bg-green-900/40 text-green-700">
              <AvatarFallback class="text-[10px] font-bold">{{ getInitials(div.name) }}</AvatarFallback>
            </Avatar>
            <Avatar v-for="(user, idx) in (nextAcara.users || []).slice(0, Math.max(0, 3 - (nextAcara.divisions?.length || 0)))" :key="'u'+idx" class="w-7 h-7 border-2 border-white dark:border-zinc-950 bg-blue-100 dark:bg-blue-900/40 text-blue-700">
              <AvatarFallback class="text-[10px] font-bold">{{ getInitials(user.name) }}</AvatarFallback>
            </Avatar>
            <div
              v-if="totalEmp > 3"
              class="w-7 h-7 rounded-full bg-gray-100 dark:bg-zinc-800 border-2 border-white dark:border-zinc-950 flex items-center justify-center text-[10px] font-medium text-gray-600 dark:text-zinc-400 z-10"
            >
              +{{ totalEmp - 3 }}
            </div>
          </div>
          <span class="text-xs text-gray-500 dark:text-zinc-400">{{ totalEmp }} Peserta Terlibat</span>
        </div>

        <Button
          class="w-full bg-black text-white hover:bg-gray-800 text-xs h-10"
          >Lihat Detail</Button
        >
      </div>
      <div
        v-else
        class="text-center py-4 text-xs text-gray-400 dark:text-zinc-500 border border-dashed rounded-lg mt-2"
      >
        Tidak ada agenda mendatang
      </div>
    </CardContent>
  </Card>
</template>
