<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Check, X, Clock, CalendarHeart, ChevronDown } from "lucide-vue-next";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

const props = defineProps<{
  stats?: any
}>();

const approvals = computed(() => {
  return props.stats?.quick_approvals || [];
});
</script>

<template>
  <Card class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800 shadow-sm">
    <CardHeader class="pb-3 border-b border-gray-100 dark:border-zinc-800 flex flex-row items-center justify-between">
      <div>
        <CardTitle class="text-base font-semibold text-gray-900 dark:text-zinc-100">Quick Approvals</CardTitle>
        <CardDescription class="text-xs">Pending requests requiring your attention</CardDescription>
      </div>
      <Badge variant="secondary" class="bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-100">{{ approvals.length }} Pending</Badge>
    </CardHeader>
    <CardContent class="p-0">
      <div v-if="approvals.length === 0" class="p-8 text-center text-gray-500 text-sm">
        No pending approvals at this time.
      </div>
      <div v-else class="divide-y divide-gray-100 dark:divide-zinc-800">
        <div v-for="item in approvals" :key="item.id" class="p-4 hover:bg-gray-50 dark:hover:bg-zinc-900/50 transition-colors">
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
              <Avatar class="w-10 h-10 border border-gray-200 dark:border-zinc-800">
                <AvatarFallback class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 font-medium">
                  {{ item.user.split(' ').map((n: any[]) => n[0]).join('') }}
                </AvatarFallback>
              </Avatar>
              <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ item.user }}</h4>
                <p class="text-[11px] text-gray-500 mb-1.5">{{ item.role }}</p>
                <div class="flex items-center text-[11px] text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-zinc-800 rounded px-2 py-1 w-fit border border-gray-200 dark:border-zinc-700">
                  <CalendarHeart v-if="item.type === 'Leave'" class="w-3 h-3 mr-1.5 text-blue-500" />
                  <Clock v-else class="w-3 h-3 mr-1.5 text-amber-500" />
                  <span class="font-medium mr-1">{{ item.type }}:</span> 
                  {{ item.date }}
                  <span v-if="item.duration" class="ml-1 text-gray-400">({{ item.duration }})</span>
                </div>
              </div>
            </div>
            <div class="flex flex-col space-y-2">
              <Button size="icon" variant="outline" class="h-7 w-7 text-green-600 border-green-200 hover:bg-green-50 hover:border-green-300 hover:text-green-700 rounded-full transition-colors">
                <Check class="w-3.5 h-3.5" />
              </Button>
              <Button size="icon" variant="outline" class="h-7 w-7 text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 hover:text-red-700 rounded-full transition-colors">
                <X class="w-3.5 h-3.5" />
              </Button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="approvals.length > 0" class="p-3 border-t border-gray-100 dark:border-zinc-800 flex justify-center">
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" size="sm" class="text-xs text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 w-full transition-colors flex items-center justify-center">
              View All Approvals <ChevronDown class="w-3 h-3 ml-1" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="center" class="w-56 bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
            <DropdownMenuItem @click="router.push('/leaves')" class="cursor-pointer text-sm hover:bg-gray-100 dark:hover:bg-zinc-900">
              <CalendarHeart class="w-4 h-4 mr-2 text-blue-500" />
              Leave Approvals
            </DropdownMenuItem>
            <DropdownMenuItem @click="router.push('/overtime')" class="cursor-pointer text-sm hover:bg-gray-100 dark:hover:bg-zinc-900">
              <Clock class="w-4 h-4 mr-2 text-amber-500" />
              Overtime Approvals
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </CardContent>
  </Card>
</template>
