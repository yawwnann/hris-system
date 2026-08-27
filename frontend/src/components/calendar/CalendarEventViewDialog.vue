<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { MapPin, Clock, Users, Building2 } from "lucide-vue-next";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import moment from "moment";

const props = defineProps<{
  open: boolean;
  selectedEvent: any;
  isAdmin: boolean;
}>();

const emit = defineEmits(['update:open', 'edit', 'delete']);

const getCategoryLabel = (catValue: string) => {
  const list = [
    { value: 'WORKDAY', label: 'Workday', color: '#10b981' },
    { value: 'WEEKEND', label: 'Weekend', color: '#6b7280' },
    { value: 'NATIONAL_HOLIDAY', label: 'National Holiday', color: '#ef4444' },
    { value: 'COMPANY_HOLIDAY', label: 'Company Holiday', color: '#f97316' },
    { value: 'COLLECTIVE_LEAVE', label: 'Collective Leave', color: '#f59e0b' },
    { value: 'SHIFT', label: 'Special Shift', color: '#8b5cf6' },
    { value: 'TRAINING', label: 'Training', color: '#0ea5e9' },
    { value: 'MEETING', label: 'Rapat', color: '#3b82f6' },
    { value: 'COMPANY_EVENT', label: 'Acara Perusahaan', color: '#ec4899' },
    { value: 'MAINTENANCE', label: 'Maintenance', color: '#64748b' },
    { value: 'OTHER', label: 'Other', color: '#94a3b8' },
  ];
  const found = list.find(c => c.value === catValue);
  return found ? found.label : catValue;
};

const getCategoryColor = (catValue: string, customColor: string | null) => {
  if (customColor) return customColor;
  const list = [
    { value: 'WORKDAY', label: 'Workday', color: '#10b981' },
    { value: 'WEEKEND', label: 'Weekend', color: '#6b7280' },
    { value: 'NATIONAL_HOLIDAY', label: 'National Holiday', color: '#ef4444' },
    { value: 'COMPANY_HOLIDAY', label: 'Company Holiday', color: '#f97316' },
    { value: 'COLLECTIVE_LEAVE', label: 'Collective Leave', color: '#f59e0b' },
    { value: 'SHIFT', label: 'Special Shift', color: '#8b5cf6' },
    { value: 'TRAINING', label: 'Training', color: '#0ea5e9' },
    { value: 'MEETING', label: 'Rapat', color: '#3b82f6' },
    { value: 'COMPANY_EVENT', label: 'Acara Perusahaan', color: '#ec4899' },
    { value: 'MAINTENANCE', label: 'Maintenance', color: '#64748b' },
    { value: 'OTHER', label: 'Other', color: '#94a3b8' },
  ];
  const found = list.find(c => c.value === catValue);
  return found ? found.color : '#3b82f6';
};

const getInitials = (name: string) => {
  if (!name) return 'A';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};
</script>

<template>
  <Dialog :open="open" @update:open="val => emit('update:open', val)">
    <DialogContent class="sm:max-w-[425px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
      <DialogHeader>
        <div class="flex items-center space-x-2 mb-2">
          <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: getCategoryColor(selectedEvent?.category, selectedEvent?.color) }"></div>
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ getCategoryLabel(selectedEvent?.category) }}</span>
        </div>
        <DialogTitle class="text-xl text-gray-900 dark:text-zinc-100">{{ selectedEvent?.title }}</DialogTitle>
      </DialogHeader>
      
      <div class="space-y-4 py-4">
        <div class="flex items-start space-x-3 text-sm">
          <Clock class="w-4 h-4 mt-0.5 text-gray-500" />
          <div>
            <div class="font-medium text-gray-900 dark:text-zinc-100">
              {{ moment(selectedEvent?.start_datetime).format('dddd, DD MMMM YYYY') }}
            </div>
            <div class="text-gray-500">
              <span v-if="selectedEvent?.is_all_day">Sepanjang Hari</span>
              <span v-else>{{ moment(selectedEvent?.start_datetime).format('HH:mm') }} - {{ moment(selectedEvent?.end_datetime).format('HH:mm') }}</span>
            </div>
          </div>
        </div>
        
        <div v-if="selectedEvent?.location" class="flex items-start space-x-3 text-sm">
          <MapPin class="w-4 h-4 mt-0.5 text-gray-500" />
          <div class="text-gray-900 dark:text-zinc-100">{{ selectedEvent?.location }}</div>
        </div>
        
        <div v-if="selectedEvent?.description" class="text-sm text-gray-600 dark:text-zinc-300 p-3 bg-gray-50 dark:bg-zinc-900 rounded-lg">
          {{ selectedEvent?.description }}
        </div>

        <!-- Peserta -->
        <div v-if="(selectedEvent?.users?.length || 0) + (selectedEvent?.divisions?.length || 0) > 0" class="pt-2">
          <h4 class="text-xs font-semibold text-gray-500 dark:text-zinc-500 uppercase tracking-wider mb-3">Peserta / Divisi Terlibat</h4>
          
          <div class="space-y-4">
            <!-- Divisions -->
            <div v-if="selectedEvent?.divisions?.length > 0">
              <div class="text-[11px] font-medium text-gray-500 mb-2 flex items-center">
                <Building2 class="w-3.5 h-3.5 mr-1.5" /> 
                Divisi ({{ selectedEvent.divisions.length }})
              </div>
              <div class="flex flex-wrap gap-2">
                <Badge variant="outline" v-for="div in selectedEvent.divisions" :key="div.id" class="bg-green-50 dark:bg-green-950 text-green-700 dark:text-green-400 border-green-200 dark:border-green-900 font-normal">
                  {{ div.name }}
                </Badge>
              </div>
            </div>

            <!-- Employees -->
            <div v-if="selectedEvent?.users?.length > 0">
              <div class="text-[11px] font-medium text-gray-500 mb-2 flex items-center">
                <Users class="w-3.5 h-3.5 mr-1.5" /> 
                Karyawan ({{ selectedEvent.users.length }})
              </div>
              <div class="flex flex-wrap gap-2">
                <Badge variant="outline" v-for="user in selectedEvent.users" :key="user.id" class="bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-900 font-normal">
                  {{ user.name }}
                </Badge>
              </div>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter v-if="isAdmin">
        <Button variant="outline" @click="emit('delete', selectedEvent?.id)" class="text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200">
          Delete
        </Button>
        <Button @click="emit('edit', selectedEvent)" class="bg-orange-600 hover:bg-orange-700 text-white">
          Ubah Acara
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
