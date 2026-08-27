<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { 
  ArrowLeft, 
  User as UserIcon, 
  Briefcase, 
  MapPin, 
  Phone, 
  Mail, 
  Calendar as CalendarIcon,
  CheckCircle2,
  AlertCircle,
  XCircle,
  Clock
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { ScrollArea } from "@/components/ui/scroll-area";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import moment from "moment";

const route = useRoute();
const router = useRouter();
const employeeId = route.params.id;

const loading = ref(true);
const employee = ref<any>(null);
const stats = ref<any>(null);
const recentAttendances = ref<any[]>([]);

const monthsList = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];
const currentYear = new Date().getFullYear();
const yearsList = Array.from({ length: 5 }, (_, i) => currentYear - i);

const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(currentYear);

const fetchEmployeeDetail = async () => {
  loading.value = true;
  try {
    const { data } = await api.get(`/users/${employeeId}`, {
      params: { month: selectedMonth.value, year: selectedYear.value }
    });
    employee.value = data.user;
    stats.value = data.stats;
    recentAttendances.value = data.recent_attendances;
  } catch (error) {
    console.error("Failed to fetch employee details", error);
    toast.error("Failed to fetch employee details");
    router.push('/employees');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchEmployeeDetail();
});

watch([selectedMonth, selectedYear], () => {
  fetchEmployeeDetail();
});

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'present': return 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30';
    case 'late': return 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30';
    case 'absent': return 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30';
    default: return 'bg-gray-50 dark:bg-gray-900/20 text-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-800/30';
  }
};
</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <AppSidebar />
    
    <main class="flex-1 flex flex-col min-w-0">
      <AppHeader />
      
      <ScrollArea class="flex-1 p-8">
        <AppBreadcrumb />
        
        <!-- Page Header -->
        <div class="flex items-center mb-8 gap-4">
          <Button variant="ghost" size="icon" @click="router.push('/employees')" class="h-8 w-8 mr-2 text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-zinc-100">
            <ArrowLeft class="h-5 w-5" />
          </Button>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100">
              Detail Karyawan
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">Detailed statistics and profile information.</p>
          </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-20">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
        </div>

        <div v-else-if="employee" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <!-- Left Column: Profile Card -->
          <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm p-6 text-center">
              <div class="w-24 h-24 rounded-full bg-orange-100 dark:bg-orange-900/30 border-4 border-white dark:border-zinc-800 mx-auto mb-4 flex items-center justify-center text-orange-700 dark:text-orange-400 text-3xl font-bold">
                {{ employee.name.charAt(0) }}
              </div>
              <h2 class="text-xl font-bold text-gray-900 dark:text-zinc-100">{{ employee.name }}</h2>
              <p class="text-gray-500 dark:text-zinc-400 mb-2">{{ employee.position?.name || '-' }}</p>
              <Badge v-if="employee.status === 'active'" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30">
                Active
              </Badge>
              <Badge v-else variant="outline" class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30">
                Inactive
              </Badge>

              <div class="mt-6 pt-6 border-t border-gray-100 dark:border-zinc-800 text-left space-y-4">
                <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                  <UserIcon class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                  <span class="text-sm">NIK: {{ employee.nik || '-' }}</span>
                </div>
                <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                  <Mail class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                  <span class="text-sm">{{ employee.email }}</span>
                </div>
                <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                  <Phone class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                  <span class="text-sm">{{ employee.phone || '-' }}</span>
                </div>
                <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                  <Briefcase class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                  <span class="text-sm">{{ employee.division?.name || '-' }}</span>
                </div>
                <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                  <CalendarIcon class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                  <span class="text-sm">Joined: {{ employee.join_date ? moment(employee.join_date).format('DD MMM YYYY') : '-' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Stats & Recent Absensi -->
          <div class="lg:col-span-2 space-y-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <h2 class="text-lg font-bold text-gray-900 dark:text-zinc-100">
                Absensi Statistics for {{ monthsList[selectedMonth - 1] }} {{ selectedYear }}
              </h2>
              <div class="flex items-center space-x-2">
                <select v-model="selectedMonth" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md text-sm px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-orange-500">
                  <option v-for="(m, index) in monthsList" :key="index" :value="index + 1">{{ m }}</option>
                </select>
                <select v-model="selectedYear" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-md text-sm px-3 py-1.5 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-orange-500">
                  <option v-for="y in yearsList" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-2">
                  <div class="p-2 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg">
                    <CheckCircle2 class="w-5 h-5" />
                  </div>
                  <h3 class="font-medium text-gray-600 dark:text-zinc-400">Hadir</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ stats?.present || 0 }}</div>
              </div>

              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-2">
                  <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 rounded-lg">
                    <Clock class="w-5 h-5" />
                  </div>
                  <h3 class="font-medium text-gray-600 dark:text-zinc-400">Terlambat</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ stats?.late || 0 }}</div>
              </div>

              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-2">
                  <div class="p-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg">
                    <XCircle class="w-5 h-5" />
                  </div>
                  <h3 class="font-medium text-gray-600 dark:text-zinc-400">Absen</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ stats?.absent || 0 }}</div>
              </div>

              <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-2">
                  <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg">
                    <AlertCircle class="w-5 h-5" />
                  </div>
                  <h3 class="font-medium text-gray-600 dark:text-zinc-400">Cuti</h3>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-zinc-100">{{ (stats?.leave || 0) + (stats?.sick || 0) }}</div>
              </div>
            </div>

            <!-- Recent Absensi Table -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl overflow-hidden">
              <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-zinc-100">Recent Attendances</h3>
              </div>
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader class="bg-gray-50 dark:bg-zinc-950/50">
                    <TableRow class="border-b border-gray-200 dark:border-zinc-800">
                      <TableHead class="w-16 text-center font-semibold text-gray-600 dark:text-zinc-300">No.</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tanggal</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Time In</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Time Out</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="recentAttendances.length === 0">
                      <TableCell colspan="5" class="h-24 text-center text-gray-500 dark:text-zinc-400">
                        No recent attendances found.
                      </TableCell>
                    </TableRow>
                    <TableRow 
                      v-else
                      v-for="(att, index) in recentAttendances" 
                      :key="att.id"
                      class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                    >
                      <TableCell class="text-center py-3 text-gray-500 dark:text-zinc-400 font-medium">
                        {{ index + 1 }}
                      </TableCell>
                      <TableCell class="py-3 text-gray-900 dark:text-zinc-200">{{ moment(att.date).format('DD MMM YYYY') }}</TableCell>
                      <TableCell class="py-3 text-gray-600 dark:text-zinc-300">{{ att.time_in ? moment(att.time_in, 'HH:mm:ss').format('HH:mm') : '-' }}</TableCell>
                      <TableCell class="py-3 text-gray-600 dark:text-zinc-300">{{ att.time_out ? moment(att.time_out, 'HH:mm:ss').format('HH:mm') : '-' }}</TableCell>
                      <TableCell class="py-3">
                        <Badge variant="outline" :class="getStatusBadgeVariant(att.status)" class="capitalize">
                          {{ att.status }}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>

          </div>
        </div>

      </ScrollArea>
    </main>
  </div>
</template>
