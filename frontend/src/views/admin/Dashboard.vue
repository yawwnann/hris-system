<script setup lang="ts">
import { 
  CalendarIcon, 
  Download,
  Clock,
  Umbrella,
  Timer,
  MoreHorizontal,
  Users,
  Code
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";

import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import SummaryCards from "@/components/dashboard/SummaryCards.vue";
import EmployeeOverview from "@/components/dashboard/EmployeeOverview.vue";
import AttendanceOverviewBar from "@/components/dashboard/AttendanceOverviewBar.vue";
import EmployeeTable from "@/components/dashboard/EmployeeTable.vue";
import DeptPerformance from "@/components/dashboard/DeptPerformance.vue";
import DeviceUsage from "@/components/dashboard/DeviceUsage.vue";
import WorkCalendar from "@/components/dashboard/WorkCalendar.vue";
import NextAgenda from "@/components/dashboard/NextAgenda.vue";
import AttendanceReport from "@/components/dashboard/AttendanceReport.vue";
import QuickApprovals from "@/components/dashboard/QuickApprovals.vue";
import EmployeeStatus from "@/components/dashboard/EmployeeStatus.vue";

import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import api from "@/lib/axios";
import moment from "moment";
// @ts-ignore
import "moment/locale/id";
moment.locale('id');

const authStore = useAuthStore();
const stats = ref<any>(null);
const loading = ref(true);

const fetchDashboardStats = async () => {
  try {
    const endpoint =
      authStore.user?.role === "admin"
        ? "/dashboard/admin"
        : "/dashboard/employee";
    const { data } = await api.get(endpoint);
    stats.value = data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const currentDateRange = computed(() => {
  const date = new Date();
  const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
  const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
  
  const options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'short', year: 'numeric' };
  return `${firstDay.toLocaleDateString('en-GB', options)} - ${lastDay.toLocaleDateString('en-GB', options)}`;
});

const currentDateDisplay = computed(() => {
  return moment().format('dddd, DD MMMM YYYY');
});

const formatTime = (timeStr: string) => {
  if (!timeStr) return '--:--';
  if (timeStr.includes(' ')) return moment(timeStr).format('HH:mm');
  return moment(timeStr, 'HH:mm:ss').format('HH:mm');
};

const formatDate = (dateStr: string) => {
  return moment(dateStr).format('DD MMM YYYY');
};

const formatDayDate = (dateStr: string) => {
  return moment(dateStr).format('ddd, DD MMM');
};

onMounted(() => {
  fetchDashboardStats();
});
</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <AppSidebar />

    <main class="flex-1 flex flex-col min-w-0">
      <AppHeader />

      <ScrollArea class="flex-1 p-8">
        <div class="flex items-center justify-between mb-8">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dasbor HR</h1>
          <div class="flex items-center space-x-2">
            <Button
              variant="outline"
              class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-600 dark:text-gray-300 h-9"
            >
              <CalendarIcon class="w-4 h-4 mr-2" /> {{ currentDateRange }}
            </Button>
            <Button
              size="icon"
              class="h-9 w-9 bg-black dark:bg-white dark:bg-zinc-950 text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gray-200"
            >
              <Download class="w-4 h-4" />
            </Button>
          </div>
        </div>

        <div
          v-if="loading"
          class="flex justify-center items-center py-20 text-gray-500 dark:text-zinc-400"
        >
          Loading dashboard...
        </div>

        <div v-else-if="stats">
          
          <!-- ADMIN LAYOUT -->
          <div v-if="authStore.user?.role === 'admin'" class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- LEFT CONTENT (Col Span 3) -->
            <div class="xl:col-span-3 space-y-6">
              <SummaryCards :stats="stats" />
              <QuickApprovals :stats="stats" />
              <EmployeeOverview :stats="stats" />
              <AttendanceOverviewBar :stats="stats" />
              <EmployeeTable />
              <DeptPerformance :stats="stats" />
            </div>

            <!-- RIGHT CONTENT (Col Span 1) -->
            <div class="space-y-6">
              <EmployeeStatus :stats="stats" />
              <DeviceUsage :stats="stats" />
              <WorkCalendar :stats="stats" />
              <NextAgenda :stats="stats" />
              <AttendanceReport :stats="stats" />
            </div>
          </div>

          <!-- EMPLOYEE LAYOUT -->
          <div v-else>
            
            <!-- Welcome Card -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Halo, {{ authStore.user?.name }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1.5">{{ currentDateDisplay }} | {{ authStore.user?.position?.name || 'Employee' }}</p>
              </div>
              <div class="flex items-center space-x-3 mt-5 md:mt-0">
                <Button class="bg-black hover:bg-gray-800 text-white dark:bg-white dark:text-black dark:hover:bg-gray-200 h-10 px-6 font-medium">Jam Masuk</Button>
                <Button variant="outline" class="border-gray-300 dark:border-zinc-700 bg-white text-gray-900 hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-100 h-10 px-6 font-medium">Ajukan Cuti</Button>
              </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <!-- Kehadiran Hari Ini -->
              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 flex flex-col justify-between">
                <div>
                   <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs font-semibold tracking-wider mb-5 uppercase">
                      <Clock class="w-4 h-4 mr-2" /> Kehadiran Hari Ini
                   </div>
                   <div class="flex justify-between items-center mb-6">
                      <div>
                        <div class="text-[32px] leading-none font-bold text-gray-900 dark:text-gray-100">{{ stats.today_attendance?.clock_in_time ? formatTime(stats.today_attendance.clock_in_time) : '--:--' }}</div>
                        <div class="text-xs text-gray-500 mt-1.5">Jam Masuk</div>
                      </div>
                      <div class="text-right">
                        <div class="text-[32px] leading-none font-bold text-gray-400 dark:text-gray-500">{{ stats.today_attendance?.clock_out_time ? formatTime(stats.today_attendance.clock_out_time) : '--:--' }}</div>
                        <div class="text-xs text-gray-500 mt-1.5">Jam Keluar</div>
                      </div>
                   </div>
                </div>
                <div class="bg-gray-100 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700/50 px-3 py-1.5 rounded-md inline-block w-fit text-xs font-semibold text-gray-700 dark:text-gray-300">
                  Status: {{ stats.today_attendance ? 'Hadir' : 'Belum Hadir' }}
                </div>
              </div>

              <!-- Sisa Cuti Tahunan -->
              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 flex flex-col justify-between">
                <div>
                   <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs font-semibold tracking-wider mb-5 uppercase">
                      <Umbrella class="w-4 h-4 mr-2" /> Sisa Cuti Tahunan
                   </div>
                   <div class="flex items-end space-x-1.5">
                     <span class="text-[36px] leading-none font-bold text-gray-900 dark:text-gray-100">{{ stats.leave_quota }}</span>
                     <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Hari</span>
                   </div>
                </div>
                <div class="mt-6">
                   <div class="w-full bg-gray-200 dark:bg-zinc-800 rounded-full h-1.5 mb-2.5">
                     <div class="bg-gray-800 dark:bg-gray-200 h-1.5 rounded-full" :style="{ width: stats.total_leave_quota > 0 ? ((stats.leaves_used / stats.total_leave_quota) * 100) + '%' : '0%' }"></div>
                   </div>
                   <div class="flex justify-between text-[11px] font-medium text-gray-500 dark:text-gray-400">
                     <span>Terpakai: {{ stats.leaves_used }}</span>
                     <span>Total: {{ stats.total_leave_quota }}</span>
                   </div>
                </div>
              </div>

              <!-- Lembur Bulan Ini -->
              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-5 flex flex-col justify-between">
                <div>
                   <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs font-semibold tracking-wider mb-5 uppercase">
                      <Timer class="w-4 h-4 mr-2" /> Lembur Bulan Ini
                   </div>
                   <div class="flex items-end space-x-1.5">
                     <span class="text-[36px] leading-none font-bold text-gray-900 dark:text-gray-100">{{ stats.overtime_this_month }}</span>
                     <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Jam</span>
                   </div>
                </div>
                <div class="mt-6 pt-3.5 border-t border-gray-100 dark:border-zinc-800 flex justify-between items-center text-[11px] font-medium text-gray-500 dark:text-gray-400">
                  <span>Menunggu Approval: {{ stats.pending_overtime_this_month }} Jam</span>
                  <MoreHorizontal class="w-4 h-4 text-gray-400" />
                </div>
              </div>
            </div>

            <!-- Bottom Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              
              <!-- Left Column: Schedule & Announcements -->
              <div class="space-y-6">
                
                <!-- Jadwal Kerja Hari Ini -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                  <div class="px-5 py-4 border-b border-gray-100 dark:border-zinc-800 tracking-wider uppercase text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                    Jadwal Kerja Hari Ini
                  </div>
                  <div class="p-2">
                    <div v-if="stats.upcoming_events?.length === 0" class="p-4 text-center text-xs text-gray-500">
                      Tidak ada jadwal hari ini
                    </div>
                    <div v-for="event in stats.upcoming_events" :key="event.id" class="flex justify-between items-center p-3 hover:bg-gray-50 dark:hover:bg-zinc-800/50 rounded-lg transition-colors">
                      <div class="flex items-start space-x-3.5">
                         <div class="p-2 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700/50 rounded-md">
                           <Users class="w-4 h-4 text-gray-600 dark:text-gray-300" v-if="event.type === 'meeting'" />
                           <Code class="w-4 h-4 text-gray-600 dark:text-gray-300" v-else />
                         </div>
                         <div class="pt-0.5">
                           <div class="text-[13px] font-semibold text-gray-900 dark:text-gray-100 leading-tight">{{ event.title }}</div>
                           <div class="text-[11px] text-gray-500 mt-0.5">{{ event.description || 'Online' }}</div>
                         </div>
                      </div>
                      <div class="text-xs font-bold text-gray-900 dark:text-gray-100 font-mono">{{ formatTime(event.date) }}</div>
                    </div>
                  </div>
                </div>

                <!-- Pengumuman Terbaru -->
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm">
                  <div class="px-5 py-4 flex justify-between items-center border-b border-gray-100 dark:border-zinc-800">
                    <span class="tracking-wider uppercase text-[11px] font-semibold text-gray-500 dark:text-gray-400">Pengumuman Terbaru</span>
                    <a href="#" class="text-[11px] font-semibold text-gray-900 dark:text-gray-100 underline decoration-gray-300 underline-offset-4 hover:text-orange-600 transition-colors">Lihat Semua</a>
                  </div>
                  <div class="p-5 space-y-4">
                    <div v-if="stats.announcements?.length === 0" class="text-center text-xs text-gray-500">
                      Tidak ada pengumuman
                    </div>
                    <div v-for="ann in stats.announcements" :key="ann.id" class="border-b border-gray-100 dark:border-zinc-800 pb-4 last:border-0 last:pb-0">
                      <span class="text-[10px] font-bold bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded tracking-wide uppercase">HRD</span>
                      <h4 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100 mt-2.5 leading-snug">{{ ann.title }}</h4>
                      <p class="text-[12px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ ann.content }}</p>
                      <div class="text-[10px] font-medium text-gray-400 mt-2">{{ formatDate(ann.publish_date) }}</div>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Right Column: Absensi History -->
              <div class="lg:col-span-2">
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm h-full flex flex-col">
                  <div class="px-5 py-4 flex justify-between items-center border-b border-gray-100 dark:border-zinc-800">
                    <span class="tracking-wider uppercase text-[11px] font-semibold text-gray-500 dark:text-gray-400">Riwayat Kehadiran Singkat</span>
                    <Button variant="outline" size="sm" class="h-7 px-3 text-[11px] font-semibold border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-300">Ekspor</Button>
                  </div>
                  <div class="overflow-x-auto p-5">
                    <table class="w-full text-left border-collapse">
                      <thead>
                        <tr class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-zinc-800">
                          <th class="pb-3 font-semibold">Tanggal</th>
                          <th class="pb-3 font-semibold">Shift</th>
                          <th class="pb-3 font-semibold">Jam Masuk</th>
                          <th class="pb-3 font-semibold">Jam Keluar</th>
                          <th class="pb-3 font-semibold text-right">Status</th>
                        </tr>
                      </thead>
                      <tbody class="text-[13px]">
                        <tr v-if="stats.recent_attendances?.length === 0">
                          <td colspan="5" class="py-6 text-center text-xs text-gray-500">Tidak ada riwayat kehadiran</td>
                        </tr>
                        <tr v-for="att in stats.recent_attendances" :key="att.id" class="border-b border-gray-50 dark:border-zinc-800/30 last:border-0 hover:bg-gray-50/50 dark:hover:bg-zinc-900/50">
                          <td class="py-3.5 font-medium text-gray-900 dark:text-gray-100">{{ formatDayDate(att.date) }}</td>
                          <td class="py-3.5 text-gray-500">Reguler</td>
                          <td class="py-3.5 font-mono text-gray-700 dark:text-gray-300">{{ att.clock_in_time ? formatTime(att.clock_in_time) : '-' }}</td>
                          <td class="py-3.5 font-mono text-gray-700 dark:text-gray-300">{{ att.clock_out_time ? formatTime(att.clock_out_time) : '-' }}</td>
                          <td class="py-3.5 text-right">
                            <span v-if="att.status === 'present'" class="bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-[11px] px-2 py-1 rounded font-semibold border border-gray-200 dark:border-zinc-700">Tepat Waktu</span>
                            <span v-else-if="att.status === 'late'" class="bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-300 text-[11px] px-2 py-1 rounded font-semibold border border-gray-200 dark:border-zinc-700 shadow-sm">Terlambat</span>
                            <span v-else-if="att.status === 'absent'" class="bg-red-50 text-red-600 dark:bg-red-900/20 text-[11px] px-2 py-1 rounded font-semibold border border-red-100 dark:border-red-800/30">Alpha</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </ScrollArea>
    </main>
  </div>
</template>
