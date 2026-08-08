<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Calendar as CalendarIcon, 
  ChevronLeft, 
  ChevronRight, 
  Plus, 
  MapPin, 
  Clock,
  ListFilter,
  Check
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
} from "@/components/ui/alert-dialog";
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import CalendarEventFormDialog from "@/components/calendar/CalendarEventFormDialog.vue";
import CalendarEventViewDialog from "@/components/calendar/CalendarEventViewDialog.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import moment from "moment";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

// State
const currentDate = ref(moment());
const events = ref<any[]>([]);
const usersList = ref<any[]>([]);
const divisionsList = ref<any[]>([]);
const loading = ref(true);
const isSubmitting = ref(false);

// Filters
const activeCategories = ref<string[]>([
  'WORKDAY', 'WEEKEND', 'NATIONAL_HOLIDAY', 'COMPANY_HOLIDAY', 
  'COLLECTIVE_LEAVE', 'SHIFT', 'TRAINING', 'MEETING', 'COMPANY_EVENT', 'MAINTENANCE', 'OTHER'
]);

const categoriesList = [
  { value: 'WORKDAY', label: 'Workday', color: '#10b981' },
  { value: 'WEEKEND', label: 'Weekend', color: '#6b7280' },
  { value: 'NATIONAL_HOLIDAY', label: 'National Holiday', color: '#ef4444' },
  { value: 'COMPANY_HOLIDAY', label: 'Company Holiday', color: '#f97316' },
  { value: 'COLLECTIVE_LEAVE', label: 'Collective Leave', color: '#f59e0b' },
  { value: 'SHIFT', label: 'Special Shift', color: '#8b5cf6' },
  { value: 'TRAINING', label: 'Training', color: '#0ea5e9' },
  { value: 'MEETING', label: 'Meeting', color: '#3b82f6' },
  { value: 'COMPANY_EVENT', label: 'Company Event', color: '#ec4899' },
  { value: 'MAINTENANCE', label: 'Maintenance', color: '#64748b' },
  { value: 'OTHER', label: 'Other', color: '#94a3b8' },
];

// Form
const isDialogOpen = ref(false);
const editId = ref<number | null>(null);
const eventForm = ref({
  title: "",
  description: "",
  category: "WORKDAY",
  start_datetime: moment().format("YYYY-MM-DDTHH:mm"),
  end_datetime: moment().add(1, 'hours').format("YYYY-MM-DDTHH:mm"),
  location: "",
  color: "#3b82f6",
  is_working_day: true,
  is_all_day: false,
  is_recurring: false,
  recurrence_rule: "",
  user_ids: [] as number[],
  division_ids: [] as number[],
});

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);
const selectedEvent = ref<any>(null);
const isViewDialogOpen = ref(false);

// Methods
const fetchEvents = async () => {
  loading.value = true;
  try {
    const start = currentDate.value.clone().startOf('month').startOf('isoWeek').format('YYYY-MM-DD');
    const end = currentDate.value.clone().endOf('month').endOf('isoWeek').format('YYYY-MM-DD');
    
    const [eventsRes, usersRes, divsRes] = await Promise.all([
      api.get('/calendar-events', {
        params: { start, end, categories: activeCategories.value.join(',') }
      }),
      usersList.value.length === 0 ? api.get('/users') : Promise.resolve(null),
      divisionsList.value.length === 0 ? api.get('/divisions') : Promise.resolve(null)
    ]);
    
    events.value = eventsRes.data.data;
    if (usersRes) usersList.value = usersRes.data.data;
    if (divsRes) divisionsList.value = divsRes.data.data;
  } catch (error) {
    console.error(error);
    toast.error("Failed to fetch calendar data");
  } finally {
    loading.value = false;
  }
};

watch(currentDate, () => fetchEvents(), { deep: true });
watch(activeCategories, () => fetchEvents(), { deep: true });

onMounted(() => {
  fetchEvents();
});

const toggleCategory = (category: string) => {
  const index = activeCategories.value.indexOf(category);
  if (index > -1) {
    activeCategories.value.splice(index, 1);
  } else {
    activeCategories.value.push(category);
  }
};

const getCategoryColor = (catValue: string, customColor: string | null) => {
  if (customColor) return customColor;
  const found = categoriesList.find(c => c.value === catValue);
  return found ? found.color : '#3b82f6';
};

const getCategoryLabel = (catValue: string) => {
  const found = categoriesList.find(c => c.value === catValue);
  return found ? found.label : catValue;
};

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

// Calendar Grid Generation
const calendarDays = computed(() => {
  const startDay = currentDate.value.clone().startOf("month").startOf("isoWeek");
  const endDay = currentDate.value.clone().endOf("month").endOf("isoWeek");
  const days = [];
  
  let day = startDay.clone();
  while (day.isSameOrBefore(endDay)) {
    const dateStr = day.format("YYYY-MM-DD");
    // Get events for this day
    const dayEvents = events.value.filter(e => {
      const eStart = moment(e.start_datetime).startOf('day');
      const eEnd = moment(e.end_datetime).startOf('day');
      return day.isBetween(eStart, eEnd, 'day', '[]');
    });

    // Auto add weekend event for Sundays
    if (day.day() === 0 && activeCategories.value.includes('WEEKEND')) {
      dayEvents.push({
        id: `sunday-${dateStr}`,
        title: 'Weekend Holiday',
        category: 'WEEKEND',
        is_all_day: true,
        start_datetime: dateStr,
        end_datetime: dateStr,
        color: '#6b7280',
        _is_virtual: true
      });
    }

    days.push({
      date: day.clone(),
      dateStr,
      isCurrentMonth: day.isSame(currentDate.value, "month"),
      isToday: day.isSame(moment(), "day"),
      isPast: day.isBefore(moment(), "day"),
      events: dayEvents,
    });
    day.add(1, "day");
  }
  return days;
});

const nextMonth = () => {
  currentDate.value = currentDate.value.clone().add(1, "month");
};

const prevMonth = () => {
  currentDate.value = currentDate.value.clone().subtract(1, "month");
};

const openAddForm = (date?: string) => {
  editId.value = null;
  const start = date ? `${date}T09:00` : moment().format("YYYY-MM-DDTHH:mm");
  const end = date ? `${date}T17:00` : moment().add(1, 'hours').format("YYYY-MM-DDTHH:mm");
  
  eventForm.value = {
    title: "",
    description: "",
    category: "COMPANY_EVENT",
    start_datetime: start,
    end_datetime: end,
    location: "",
    color: "#3b82f6",
    is_working_day: true,
    is_all_day: false,
    is_recurring: false,
    recurrence_rule: "",
    user_ids: [],
    division_ids: [],
  };
  isDialogOpen.value = true;
};

const openEditForm = (event: any) => {
  editId.value = event.id;
  eventForm.value = {
    title: event.title,
    description: event.description || "",
    category: event.category,
    start_datetime: moment(event.start_datetime).format("YYYY-MM-DDTHH:mm"),
    end_datetime: moment(event.end_datetime).format("YYYY-MM-DDTHH:mm"),
    location: event.location || "",
    color: event.color || "#3b82f6",
    is_working_day: event.is_working_day,
    is_all_day: event.is_all_day,
    is_recurring: event.is_recurring,
    recurrence_rule: event.recurrence_rule || "",
    user_ids: event.users?.map((u: any) => u.id) || [],
    division_ids: event.divisions?.map((d: any) => d.id) || [],
  };
  isViewDialogOpen.value = false;
  isDialogOpen.value = true;
};

const toggleUser = (id: number) => {
  const index = eventForm.value.user_ids.indexOf(id);
  if (index > -1) eventForm.value.user_ids.splice(index, 1);
  else eventForm.value.user_ids.push(id);
};

const toggleDivision = (id: number) => {
  const index = eventForm.value.division_ids.indexOf(id);
  if (index > -1) eventForm.value.division_ids.splice(index, 1);
  else eventForm.value.division_ids.push(id);
};

const viewEvent = (event: any) => {
  if (event._is_virtual) return; // Don't open virtual weekend events
  selectedEvent.value = event;
  isViewDialogOpen.value = true;
};

const saveEvent = async () => {
  isSubmitting.value = true;
  try {
    if (editId.value) {
      await api.put(`/calendar-events/${editId.value}`, eventForm.value);
      toast.success("Event successfully updated");
    } else {
      await api.post("/calendar-events", eventForm.value);
      toast.success("Event successfully added");
    }
    isDialogOpen.value = false;
    fetchEvents();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Failed to save event");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDelete = (id: number) => {
  itemToDelete.value = id;
  isDeleteDialogOpen.value = true;
};

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  try {
    await api.delete(`/calendar-events/${itemToDelete.value}`);
    toast.success("Event successfully deleted");
    isViewDialogOpen.value = false;
    fetchEvents();
  } catch (error) {
    toast.error("Failed to delete event");
  } finally {
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
  }
};

</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <AppSidebar />
    
    <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
      <AppHeader />
      
      <div class="flex-1 flex flex-col p-6 overflow-hidden">
        <AppBreadcrumb />
        
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">
              Calendar Management
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Manage work schedules, holidays, company agendas, and other events.
            </p>
          </div>
          <Button v-if="authStore.user?.role === 'admin'" @click="openAddForm()" class="bg-indigo-600 hover:bg-indigo-700 text-white">
            <Plus class="w-4 h-4 mr-2" /> Add Event
          </Button>
        </div>

        <div class="flex-1 flex flex-col lg:flex-row gap-6 overflow-hidden">
          
          <!-- Sidebar Filter -->
          <div class="w-full lg:w-64 flex-shrink-0 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/30">
              <h3 class="font-semibold text-gray-800 dark:text-zinc-200 flex items-center">
                <ListFilter class="w-4 h-4 mr-2" /> Category Filter
              </h3>
            </div>
            <ScrollArea class="flex-1 p-4">
              <div class="space-y-3">
                <div v-for="cat in categoriesList" :key="cat.value" class="flex items-center space-x-2">
                  <Checkbox 
                    :id="`filter-${cat.value}`" 
                    :checked="activeCategories.includes(cat.value)"
                    @update:checked="toggleCategory(cat.value)"
                  />
                  <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: cat.color }"></div>
                  <label :for="`filter-${cat.value}`" class="text-sm cursor-pointer text-gray-700 dark:text-gray-300 select-none flex-1 truncate">
                    {{ cat.label }}
                  </label>
                </div>
              </div>
            </ScrollArea>
          </div>

          <!-- Calendar Area -->
          <div class="flex-1 bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800 flex flex-col overflow-hidden shadow-sm">
            <!-- Calendar Header -->
            <div class="p-4 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <Button variant="outline" size="icon" @click="prevMonth" class="h-8 w-8">
                  <ChevronLeft class="w-4 h-4" />
                </Button>
                <h2 class="text-lg font-bold text-gray-800 dark:text-zinc-100 min-w-[150px] text-center">
                  {{ currentDate.format("MMMM YYYY") }}
                </h2>
                <Button variant="outline" size="icon" @click="nextMonth" class="h-8 w-8">
                  <ChevronRight class="w-4 h-4" />
                </Button>
                <Button variant="ghost" size="sm" @click="currentDate = moment()" class="ml-2 text-indigo-600 dark:text-indigo-400">
                  Today
                </Button>
              </div>
              
              <div v-if="loading" class="text-sm text-gray-500 animate-pulse">Loading...</div>
            </div>

            <!-- Calendar Grid -->
            <div class="flex-1 flex flex-col overflow-hidden">
              <div class="grid grid-cols-7 border-b border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-950">
                <div v-for="day in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']" :key="day" class="py-2 text-center text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
                  {{ day }}
                </div>
              </div>
              <ScrollArea class="flex-1">
                <div class="grid grid-cols-7 auto-rows-fr h-full min-h-[600px]">
                  <div 
                    v-for="(day, index) in calendarDays" 
                    :key="index"
                    class="border-b border-r border-gray-100 dark:border-zinc-800 p-1 flex flex-col min-h-[120px] transition-colors relative group"
                    :class="[
                      day.isCurrentMonth ? 'bg-white dark:bg-zinc-900' : 'bg-gray-50 dark:bg-zinc-950/50',
                      { 'ring-2 ring-indigo-500 ring-inset z-10': day.isToday },
                      { 'past-day bg-gray-100/50 dark:bg-zinc-900/40 opacity-50': day.isPast }
                    ]"
                  >
                    <!-- Date Number -->
                    <div class="flex justify-between items-start p-1">
                      <span 
                        class="text-xs font-medium w-6 h-6 flex items-center justify-center rounded-full"
                        :class="[
                          day.isToday ? 'bg-indigo-600 text-white' : (day.isCurrentMonth ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-zinc-600'),
                          day.date.day() === 0 ? 'text-red-500 dark:text-red-400' : ''
                        ]"
                      >
                        {{ day.date.format("D") }}
                      </span>
                      <Button v-if="authStore.user?.role === 'admin' && !day.isPast && day.date.day() !== 0" variant="ghost" size="icon" class="h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity" @click="openAddForm(day.dateStr)">
                        <Plus class="w-3 h-3" />
                      </Button>
                    </div>

                    <!-- Events list -->
                    <div class="flex-1 overflow-y-auto mt-1 space-y-1 px-1 custom-scrollbar">
                      <div 
                        v-for="event in day.events" 
                        :key="event.id"
                        @click="viewEvent(event)"
                        class="text-[11px] px-1.5 py-1 rounded cursor-pointer truncate transition-all hover:opacity-80"
                        :style="{ 
                          backgroundColor: event.is_all_day ? getCategoryColor(event.category, event.color) : 'transparent',
                          color: event.is_all_day ? '#fff' : getCategoryColor(event.category, event.color),
                          borderLeft: event.is_all_day ? 'none' : `3px solid ${getCategoryColor(event.category, event.color)}`
                        }"
                      >
                        <span v-if="!event.is_all_day" class="font-medium mr-1">{{ moment(event.start_datetime).format('HH:mm') }}</span>
                        {{ event.title }}
                      </div>
                    </div>
                  </div>
                </div>
              </ScrollArea>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Dialog View Event -->
    <CalendarEventViewDialog
      v-model:open="isViewDialogOpen"
      :selectedEvent="selectedEvent"
      :isAdmin="authStore.user?.role === 'admin'"
      @edit="openEditForm"
      @delete="confirmDelete"
    />

    <!-- Dialog Add/Edit Event -->
    <CalendarEventFormDialog
      v-model:open="isDialogOpen"
      :editId="editId"
      :eventForm="eventForm"
      :categoriesList="categoriesList"
      :divisionsList="divisionsList"
      :usersList="usersList"
      :isSubmitting="isSubmitting"
      @save="saveEvent"
    />

    <!-- Alert Dialog Konfirmasi Hapus -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Delete Event?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-500 dark:text-zinc-400">
            Are you sure you want to delete this event from the calendar?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white hover:bg-red-700">Yes, Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(82, 82, 91, 0.5);
}
.past-day {
  background-image: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(0, 0, 0, 0.02) 10px,
    rgba(0, 0, 0, 0.02) 20px
  );
}
.dark .past-day {
  background-image: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(255, 255, 255, 0.02) 10px,
    rgba(255, 255, 255, 0.02) 20px
  );
}
</style>
