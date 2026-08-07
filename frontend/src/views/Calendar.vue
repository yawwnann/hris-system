<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { 
  Calendar as CalendarIcon, 
  ChevronLeft, 
  ChevronRight, 
  Plus, 
  Users, 
  Clock,
  Check
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "@/components/ui/tabs";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import moment from "moment";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

// Calendar state
const currentDate = ref(moment());
const agendas = ref<any[]>([]);
const rosters = ref<any[]>([]);
const employees = ref<any[]>([]);
const shifts = ref<any[]>([]);
const loading = ref(true);
const isSubmitting = ref(false);

// Dialog state
const isDialogOpen = ref(false);
const selectedDate = ref("");
const activeTab = ref("detail"); // detail, agenda, or shift

// Agenda Form
const agendaForm = ref({
  title: "",
  description: "",
  start_time: "",
  end_time: "",
  user_ids: [] as number[]
});

// Roster Form
const rosterForm = ref({
  shift_id: "",
  user_ids: [] as number[]
});

const fetchCalendarData = async () => {
  loading.value = true;
  const month = currentDate.value.format('MM');
  const year = currentDate.value.format('YYYY');
  
  try {
    const [agendaRes, rosterRes] = await Promise.all([
      api.get(`/agendas?month=${month}&year=${year}`),
      api.get(`/rosters?month=${month}&year=${year}`)
    ]);
    agendas.value = agendaRes.data;
    rosters.value = rosterRes.data;
  } catch (error) {
    toast.error("Gagal memuat data kalender");
  } finally {
    loading.value = false;
  }
};

const fetchOptions = async () => {
  if (authStore.user?.role !== 'admin') return;
  try {
    const [empRes, shiftRes] = await Promise.all([
      api.get("/users"),
      api.get("/shifts")
    ]);
    employees.value = empRes.data;
    shifts.value = shiftRes.data;
  } catch (error) {
    console.error(error);
  }
};

onMounted(() => {
  fetchCalendarData();
  fetchOptions();
});

const nextMonth = () => {
  currentDate.value = moment(currentDate.value).add(1, 'month');
  fetchCalendarData();
};

const prevMonth = () => {
  currentDate.value = moment(currentDate.value).subtract(1, 'month');
  fetchCalendarData();
};

const goToToday = () => {
  currentDate.value = moment();
  fetchCalendarData();
};

// Calendar Grid Logic
const calendarGrid = computed(() => {
  const startOfMonth = moment(currentDate.value).startOf('month');
  const endOfMonth = moment(currentDate.value).endOf('month');
  const startDate = moment(startOfMonth).startOf('week');
  const endDate = moment(endOfMonth).endOf('week');
  
  const grid = [];
  let current = moment(startDate);
  
  while (current.isBefore(endDate)) {
    const dateStr = current.format('YYYY-MM-DD');
    grid.push({
      date: current.clone(),
      dateStr,
      isCurrentMonth: current.month() === currentDate.value.month(),
      isToday: current.isSame(moment(), 'day'),
      agendas: agendas.value.filter(a => a.date === dateStr),
      rosters: rosters.value.filter(r => r.date === dateStr)
    });
    current.add(1, 'days');
  }
  return grid;
});

const openDialog = (dateStr: string) => {
  selectedDate.value = dateStr;
  activeTab.value = 'detail';
  
  // Reset forms
  agendaForm.value = { title: "", description: "", start_time: "", end_time: "", user_ids: [] };
  rosterForm.value = { shift_id: "", user_ids: [] };
  
  isDialogOpen.value = true;
};

const toggleEmployeeSelection = (type: 'agenda' | 'roster', empId: number) => {
  const form = type === 'agenda' ? agendaForm.value : rosterForm.value;
  const index = form.user_ids.indexOf(empId);
  if (index === -1) {
    form.user_ids.push(empId);
  } else {
    form.user_ids.splice(index, 1);
  }
};

const selectAllEmployees = (type: 'agenda' | 'roster') => {
  const form = type === 'agenda' ? agendaForm.value : rosterForm.value;
  if (form.user_ids.length === employees.value.length) {
    form.user_ids = [];
  } else {
    form.user_ids = employees.value.map(e => e.id);
  }
};

const submitAgenda = async () => {
  if (!agendaForm.value.title) {
    toast.error("Judul agenda wajib diisi");
    return;
  }
  
  isSubmitting.value = true;
  try {
    await api.post("/agendas", {
      ...agendaForm.value,
      date: selectedDate.value,
      start_time: agendaForm.value.start_time || null,
      end_time: agendaForm.value.end_time || null
    });
    toast.success("Agenda berhasil dibuat");
    isDialogOpen.value = false;
    fetchCalendarData();
  } catch (error) {
    toast.error("Gagal membuat agenda");
  } finally {
    isSubmitting.value = false;
  }
};

const submitRoster = async () => {
  if (!rosterForm.value.shift_id || rosterForm.value.user_ids.length === 0) {
    toast.error("Shift dan setidaknya satu karyawan harus dipilih");
    return;
  }
  
  isSubmitting.value = true;
  try {
    await api.post("/rosters", {
      date: selectedDate.value,
      shift_id: rosterForm.value.shift_id,
      user_ids: rosterForm.value.user_ids
    });
    toast.success("Shift karyawan berhasil diatur");
    isDialogOpen.value = false;
    fetchCalendarData();
  } catch (error) {
    toast.error("Gagal mengatur shift");
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <AppSidebar />
    
    <main class="flex-1 flex flex-col min-w-0">
      <AppHeader />
      
      <ScrollArea class="flex-1 p-8">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">
              <CalendarIcon class="w-6 h-6 mr-3 text-indigo-600 dark:text-indigo-400" />
              Kalender Kerja & Plotting
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Pantau dan atur agenda, rapat, serta penugasan shift karyawan per tanggal.
            </p>
          </div>
          
          <div class="flex items-center space-x-4 bg-white dark:bg-zinc-900 p-2 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm">
            <Button variant="outline" size="sm" @click="goToToday" class="mr-2 text-xs font-medium">Bulan Ini</Button>
            <Button variant="ghost" size="icon" @click="prevMonth" class="text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg">
              <ChevronLeft class="w-5 h-5" />
            </Button>
            <div class="font-bold text-lg w-40 text-center text-gray-800 dark:text-zinc-200">
              {{ currentDate.format('MMMM YYYY') }}
            </div>
            <Button variant="ghost" size="icon" @click="nextMonth" class="text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg">
              <ChevronRight class="w-5 h-5" />
            </Button>
          </div>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
          <div class="grid grid-cols-7 border-b border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-950/50">
            <div v-for="day in ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']" :key="day" class="py-3 text-center text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">
              {{ day }}
            </div>
          </div>
          
          <div class="grid grid-cols-7 auto-rows-fr">
            <div 
              v-for="(day, idx) in calendarGrid" 
              :key="idx"
              @click="openDialog(day.dateStr)"
              class="min-h-[120px] p-2 border-r border-b border-gray-100 dark:border-zinc-800 transition-colors relative group"
              :class="{
                'bg-gray-50/30 dark:bg-zinc-950/30': !day.isCurrentMonth,
                'hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 cursor-pointer': true
              }"
            >
              <div class="flex justify-between items-start mb-2">
                <span 
                  class="w-7 h-7 flex items-center justify-center rounded-full text-sm font-medium"
                  :class="{
                    'bg-indigo-600 text-white shadow-md': day.isToday,
                    'text-gray-900 dark:text-zinc-100': !day.isToday && day.isCurrentMonth,
                    'text-gray-400 dark:text-zinc-600': !day.isCurrentMonth
                  }"
                >
                  {{ day.date.format('D') }}
                </span>
                
                <Button v-if="authStore.user?.role === 'admin'" variant="ghost" size="icon" class="w-6 h-6 opacity-0 group-hover:opacity-100 text-indigo-500 transition-opacity">
                  <Plus class="w-4 h-4" />
                </Button>
              </div>
              
              <!-- Chips for Agendas -->
              <div class="space-y-1.5 mt-2">
                <div v-for="agenda in day.agendas" :key="'a'+agenda.id" class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30 rounded text-xs truncate font-medium">
                  {{ agenda.title }}
                </div>
                
                <!-- Summary of Rosters -->
                <div v-if="day.rosters.length > 0" class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30 rounded text-xs font-medium flex items-center justify-between">
                  <span>{{ day.rosters.length }} Shift Set</span>
                  <Users class="w-3 h-3" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </ScrollArea>
    </main>

    <!-- Dialog Create Agenda & Roster -->
    <Dialog v-model:open="isDialogOpen">
      <DialogContent class="sm:max-w-[600px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 p-0 overflow-hidden">
        <DialogHeader class="p-6 pb-0">
          <DialogTitle class="text-xl text-gray-900 dark:text-zinc-100">Plotting Tanggal: {{ moment(selectedDate).format('DD MMMM YYYY') }}</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Atur agenda pertemuan atau plotting shift kerja karyawan.
          </DialogDescription>
        </DialogHeader>
        
        <Tabs v-model="activeTab" class="w-full">
          <div class="px-6 pt-4">
            <TabsList class="grid w-full bg-gray-100 dark:bg-zinc-900 p-1 rounded-lg" :class="authStore.user?.role === 'admin' ? 'grid-cols-3' : 'grid-cols-1'">
              <TabsTrigger value="detail" class="rounded-md data-[state=active]:bg-white data-[state=active]:dark:bg-zinc-800 data-[state=active]:shadow-sm">Detail Jadwal</TabsTrigger>
              <TabsTrigger v-if="authStore.user?.role === 'admin'" value="agenda" class="rounded-md data-[state=active]:bg-white data-[state=active]:dark:bg-zinc-800 data-[state=active]:shadow-sm">Buat Agenda</TabsTrigger>
              <TabsTrigger v-if="authStore.user?.role === 'admin'" value="shift" class="rounded-md data-[state=active]:bg-white data-[state=active]:dark:bg-zinc-800 data-[state=active]:shadow-sm">Plotting Shift</TabsTrigger>
            </TabsList>
          </div>
          
          <div class="p-6 pt-4 h-[400px] overflow-y-auto">
            <TabsContent value="detail" class="m-0 space-y-4 outline-none">
              <div v-if="!calendarGrid.find(d => d.dateStr === selectedDate)?.agendas.length && !calendarGrid.find(d => d.dateStr === selectedDate)?.rosters.length" class="text-center text-gray-500 dark:text-zinc-400 py-10">
                Tidak ada agenda atau jadwal shift pada tanggal ini.
              </div>
              
              <div v-else class="space-y-6">
                <!-- Agendas List -->
                <div v-if="calendarGrid.find(d => d.dateStr === selectedDate)?.agendas.length">
                  <h3 class="font-semibold text-gray-900 dark:text-zinc-100 mb-3 flex items-center">
                    <CalendarIcon class="w-4 h-4 mr-2 text-indigo-500" /> Agenda Kegiatan
                  </h3>
                  <div class="space-y-3">
                    <div v-for="agenda in calendarGrid.find(d => d.dateStr === selectedDate)?.agendas" :key="agenda.id" class="p-3 border border-gray-100 dark:border-zinc-800 rounded-lg bg-gray-50 dark:bg-zinc-900/50">
                      <div class="font-medium text-gray-900 dark:text-zinc-100">{{ agenda.title }}</div>
                      <div class="text-xs text-gray-500 dark:text-zinc-400 mt-1 flex items-center gap-3">
                        <span v-if="agenda.start_time" class="flex items-center"><Clock class="w-3 h-3 mr-1" /> {{ agenda.start_time }} - {{ agenda.end_time || 'Selesai' }}</span>
                        <span v-if="agenda.description" class="truncate">{{ agenda.description }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Rosters List -->
                <div v-if="calendarGrid.find(d => d.dateStr === selectedDate)?.rosters.length">
                  <h3 class="font-semibold text-gray-900 dark:text-zinc-100 mb-3 flex items-center">
                    <Users class="w-4 h-4 mr-2 text-emerald-500" /> Shift Karyawan
                  </h3>
                  <div class="space-y-3">
                    <div v-for="roster in calendarGrid.find(d => d.dateStr === selectedDate)?.rosters" :key="roster.id" class="p-3 border border-gray-100 dark:border-zinc-800 rounded-lg bg-gray-50 dark:bg-zinc-900/50 flex justify-between items-center">
                      <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                          {{ roster.user?.name?.charAt(0) || 'U' }}
                        </div>
                        <div>
                          <div class="font-medium text-gray-900 dark:text-zinc-100 text-sm">{{ roster.user?.name || 'Karyawan' }}</div>
                          <div class="text-xs text-gray-500 dark:text-zinc-400">{{ roster.shift?.name || 'Shift' }} ({{ roster.shift?.start_time }} - {{ roster.shift?.end_time }})</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </TabsContent>

            <TabsContent value="agenda" class="m-0 space-y-4 outline-none">
              <div class="space-y-2">
                <Label class="text-gray-700 dark:text-gray-300">Judul Agenda <span class="text-red-500">*</span></Label>
                <Input v-model="agendaForm.title" placeholder="Contoh: Rapat Evaluasi Bulanan" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-gray-700 dark:text-gray-300">Waktu Mulai</Label>
                  <Input type="time" v-model="agendaForm.start_time" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                </div>
                <div class="space-y-2">
                  <Label class="text-gray-700 dark:text-gray-300">Waktu Selesai</Label>
                  <Input type="time" v-model="agendaForm.end_time" class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
                </div>
              </div>
              
              <div class="space-y-2">
                <Label class="text-gray-700 dark:text-gray-300">Deskripsi/Catatan</Label>
                <Input v-model="agendaForm.description" placeholder="Lokasi atau link meeting..." class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" />
              </div>
              
              <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
                <div class="flex justify-between items-center">
                  <Label class="text-gray-700 dark:text-gray-300">Peserta Agenda</Label>
                  <div class="flex items-center space-x-2">
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">{{ agendaForm.user_ids.length }} Terpilih</span>
                    <Button variant="ghost" size="sm" class="h-6 text-xs px-2" @click="selectAllEmployees('agenda')">
                      Pilih Semua
                    </Button>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2 max-h-[150px] overflow-y-auto p-1">
                  <div 
                    v-for="emp in employees" 
                    :key="emp.id"
                    @click="toggleEmployeeSelection('agenda', emp.id)"
                    class="flex items-center space-x-2 p-2 rounded border cursor-pointer transition-colors"
                    :class="agendaForm.user_ids.includes(emp.id) ? 'bg-indigo-50 border-indigo-200 dark:bg-indigo-900/20 dark:border-indigo-800/30' : 'bg-white border-gray-200 dark:bg-zinc-900 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800'"
                  >
                    <div class="w-4 h-4 rounded border flex items-center justify-center shrink-0" :class="agendaForm.user_ids.includes(emp.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 dark:border-zinc-600'">
                      <Check v-if="agendaForm.user_ids.includes(emp.id)" class="w-3 h-3 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-zinc-300 truncate">{{ emp.name }}</span>
                  </div>
                </div>
              </div>
              
              <Button @click="submitAgenda" :disabled="isSubmitting" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white mt-4">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Agenda' }}
              </Button>
            </TabsContent>
            
            <TabsContent value="shift" class="m-0 space-y-4 outline-none">
              <div class="space-y-2">
                <Label class="text-gray-700 dark:text-gray-300">Pilih Shift Kerja <span class="text-red-500">*</span></Label>
                <Select v-model="rosterForm.shift_id">
                  <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-zinc-300">
                    <SelectValue placeholder="Pilih shift..." />
                  </SelectTrigger>
                  <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                    <SelectItem v-for="shift in shifts" :key="shift.id" :value="String(shift.id)" class="text-gray-700 dark:text-zinc-300">
                      {{ shift.name }} ({{ shift.start_time }} - {{ shift.end_time }})
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
                <div class="flex justify-between items-center">
                  <Label class="text-gray-700 dark:text-gray-300">Pilih Karyawan</Label>
                  <div class="flex items-center space-x-2">
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">{{ rosterForm.user_ids.length }} Terpilih</span>
                    <Button variant="ghost" size="sm" class="h-6 text-xs px-2" @click="selectAllEmployees('roster')">
                      Pilih Semua
                    </Button>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-2 max-h-[220px] overflow-y-auto p-1">
                  <div 
                    v-for="emp in employees" 
                    :key="emp.id"
                    @click="toggleEmployeeSelection('roster', emp.id)"
                    class="flex items-center space-x-2 p-2 rounded border cursor-pointer transition-colors"
                    :class="rosterForm.user_ids.includes(emp.id) ? 'bg-indigo-50 border-indigo-200 dark:bg-indigo-900/20 dark:border-indigo-800/30' : 'bg-white border-gray-200 dark:bg-zinc-900 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800'"
                  >
                    <div class="w-4 h-4 rounded border flex items-center justify-center shrink-0" :class="rosterForm.user_ids.includes(emp.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 dark:border-zinc-600'">
                      <Check v-if="rosterForm.user_ids.includes(emp.id)" class="w-3 h-3 text-white" />
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-zinc-300 truncate">{{ emp.name }}</span>
                  </div>
                </div>
              </div>
              
              <Button @click="submitRoster" :disabled="isSubmitting" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white mt-4">
                {{ isSubmitting ? 'Menyimpan...' : 'Simpan Shift Karyawan' }}
              </Button>
            </TabsContent>
          </div>
        </Tabs>
      </DialogContent>
    </Dialog>
  </div>
</template>
