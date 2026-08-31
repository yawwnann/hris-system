<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  UserCheck, 
  MapPin, 
  Clock, 
  Calendar,
  Search,
  CheckCircle,
  AlertCircle
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Card, CardContent } from "@/components/ui/card";
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
import { useAuthStore } from "@/stores/auth";
import moment from "moment";

const authStore = useAuthStore();
const history = ref<any[]>([]);
const todayRecord = ref<any>(null);
const loading = ref(true);
const locationLoading = ref(false);
const searchQuery = ref("");

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref("10");
watch(itemsPerPage, () => {
  currentPage.value = 1;
  fetchData();
});
const totalPages = ref(1);
const totalItems = ref(0);

// Fetch Data
const fetchData = async () => {
  loading.value = true;
  try {
    const [historyRes, todayRes] = await Promise.all([
      api.get("/attendance", { params: { search: searchQuery.value, page: currentPage.value, per_page: Number(itemsPerPage.value) } }),
      api.get("/attendance/today")
    ]);
    history.value = historyRes.data.data;
    totalPages.value = historyRes.data.last_page;
    totalItems.value = historyRes.data.total;
    todayRecord.value = todayRes.data;
  } catch (error) {
    console.error("Failed to fetch attendance data", error);
    toast.error("Failed to fetch attendance data");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});

// Computed properties for filtering and pagination
const filteredHistory = computed(() => history.value);
const paginatedHistory = computed(() => history.value);

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchData();
});

watch(currentPage, () => {
  fetchData();
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

// Format Date & Time
const formatDate = (dateString: string) => moment(dateString).format("DD MMM YYYY");
const formatTime = (timeString: string) => timeString ? moment(timeString, "HH:mm:ss").format("HH:mm") : "-";

// Geolocation & Clock In / Out
const handleClockAction = (type: 'in' | 'out') => {
  if (!navigator.geolocation) {
    toast.error("Geolocation is not supported by your browser");
    return;
  }

  locationLoading.value = true;
  toast.info("Getting your location...");

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const lat = position.coords.latitude;
      const long = position.coords.longitude;
      
      try {
        const endpoint = type === 'in' ? "/attendance/check-in" : "/attendance/check-out";
        const { data } = await api.post(endpoint, { lat, long });
        
        toast.success(data.message || `Successfully ${type === 'in' ? 'Check In' : 'Check Out'}`);
        fetchData(); 
      } catch (error: any) {
        toast.error(error.response?.data?.message || `Failed to ${type === 'in' ? 'Check In' : 'Check Out'}`);
        if(error.response?.data?.distance) {
            toast.error(`Your distance: ${error.response.data.distance}. Limit: ${error.response.data.allowed_radius}`);
        }
      } finally {
        locationLoading.value = false;
      }
    },
    (error) => {
      console.error(error);
      locationLoading.value = false;
      toast.error("Failed to get location. Make sure you allow location access.");
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
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
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">
              Absensi
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              {{ authStore.user?.role === 'admin' ? 'Monitor attendance history of all employees.' : 'Record your attendance today and view history.' }}
            </p>
          </div>
        </div>

        <!-- Clock In / Out Action Card -->
        <Card v-if="authStore.user?.role !== 'admin'" class="mb-8 border-orange-100 dark:border-orange-900/30 bg-gradient-to-r from-orange-50 to-white dark:from-orange-950/20 dark:to-zinc-900 overflow-hidden relative">
          <div class="absolute right-0 top-0 opacity-10 pointer-events-none -mt-10 -mr-10">
            <Clock class="w-48 h-48 text-orange-600" />
          </div>
          <CardContent class="p-8">
            <div class="flex flex-col md:flex-row items-center justify-between z-10 relative">
              <div class="mb-6 md:mb-0">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 mb-2">
                  {{ moment().format('dddd, DD MMMM YYYY') }}
                </h2>
                
                <div class="flex flex-wrap gap-4 mt-4">
                  <div class="flex items-center space-x-2 text-sm bg-white dark:bg-zinc-800 px-4 py-2 rounded-full border border-gray-200 dark:border-zinc-700 shadow-sm">
                    <Clock class="w-4 h-4 text-green-500" />
                    <span class="text-gray-600 dark:text-zinc-300">In:</span>
                    <span class="font-bold text-gray-900 dark:text-zinc-100">{{ todayRecord?.time_in ? formatTime(todayRecord.time_in) : '--:--' }}</span>
                  </div>
                  <div class="flex items-center space-x-2 text-sm bg-white dark:bg-zinc-800 px-4 py-2 rounded-full border border-gray-200 dark:border-zinc-700 shadow-sm">
                    <Clock class="w-4 h-4 text-orange-500" />
                    <span class="text-gray-600 dark:text-zinc-300">Out:</span>
                    <span class="font-bold text-gray-900 dark:text-zinc-100">{{ todayRecord?.time_out ? formatTime(todayRecord.time_out) : '--:--' }}</span>
                  </div>
                </div>
              </div>
              
              <div class="flex items-center space-x-4">
                <Button 
                  v-if="!todayRecord?.time_in"
                  @click="handleClockAction('in')" 
                  :disabled="locationLoading"
                  class="h-14 px-8 text-base font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all"
                >
                  <MapPin class="w-5 h-5 mr-2" /> 
                  {{ locationLoading ? 'Getting Location...' : 'Clock In Now' }}
                </Button>
                
                <Button 
                  v-else-if="todayRecord?.time_in && !todayRecord?.time_out"
                  @click="handleClockAction('out')" 
                  :disabled="locationLoading"
                  class="h-14 px-8 text-base font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all"
                >
                  <MapPin class="w-5 h-5 mr-2" /> 
                  {{ locationLoading ? 'Getting Location...' : 'Clock Out Now' }}
                </Button>

                <div v-else class="flex items-center space-x-2 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-6 py-4 rounded-xl border border-green-200 dark:border-green-800/30">
                  <CheckCircle class="w-6 h-6" />
                  <span class="font-bold text-base">Today's Tasks Completed</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- History Table -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
          <div class="p-4 border-b border-gray-200 dark:border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50/50 dark:bg-zinc-950/30">
            <h3 class="font-semibold text-gray-800 dark:text-zinc-200 flex items-center">
              <Calendar class="w-4 h-4 mr-2" /> Absensi History
            </h3>
            
            <div class="relative w-full md:w-72">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                :placeholder="authStore.user?.role === 'admin' ? 'Search name or date...' : 'Search date...'" 
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100 h-9"
              />
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50 dark:bg-zinc-950/50">
                <TableRow class="border-b border-gray-200 dark:border-zinc-800 hover:bg-transparent">
                  <TableHead class="w-16 text-center font-semibold text-gray-600 dark:text-zinc-300">No.</TableHead>
                  <TableHead v-if="authStore.user?.role === 'admin'" class="font-semibold text-gray-600 dark:text-zinc-300">Employee</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tanggal</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Jam Masuk</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Jam Keluar</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Total Jam</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 7 : 6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Loading attendance history...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedHistory.length === 0">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 7 : 6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    No attendance history.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(record, index) in paginatedHistory" 
                  :key="record.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell class="text-center py-4 text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * Number(itemsPerPage) + index + 1 }}
                  </TableCell>
                  <TableCell v-if="authStore.user?.role === 'admin'" class="py-4">
                    <div class="font-medium text-gray-900 dark:text-zinc-100">{{ record.user?.name || '-' }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-900 dark:text-zinc-200">{{ formatDate(record.date) }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4 text-center">
                    <div class="inline-flex items-center text-gray-700 dark:text-zinc-300 font-medium">
                      {{ formatTime(record.time_in) }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4 text-center">
                    <div class="inline-flex items-center text-gray-700 dark:text-zinc-300 font-medium">
                      {{ formatTime(record.time_out) }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4 text-center">
                    <div class="text-gray-900 dark:text-zinc-200 font-medium">
                      {{ record.total_hours ? record.total_hours + ' hours' : '-' }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4 text-center">
                    <Badge v-if="record.status === 'present'" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30">
                      On Time
                    </Badge>
                    <Badge v-else-if="record.status === 'late'" variant="outline" class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30">
                      Terlambat
                    </Badge>
                    <Badge v-else variant="outline" class="bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 border-gray-200 dark:border-zinc-700">
                      {{ record.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <!-- Pagination -->
          <div class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-zinc-950/50">
            <div class="flex items-center">
            <div class="flex items-center space-x-2 mr-4">
              <span class="text-sm text-gray-500 dark:text-zinc-400">Tampilkan</span>
              <Select v-model="itemsPerPage">
                <SelectTrigger class="w-[70px] h-8 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-xs">
                  <SelectValue placeholder="10" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectItem value="10">10</SelectItem>
                  <SelectItem value="25">25</SelectItem>
                  <SelectItem value="50">50</SelectItem>
                  <SelectItem value="100">100</SelectItem>
                </SelectContent>
              </Select>
            </div>
<div class="text-sm text-gray-500 dark:text-zinc-400">
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedHistory.length > 0 ? (currentPage - 1) * Number(itemsPerPage) + 1 : 0 }}</span> - 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * Number(itemsPerPage), totalItems) }}</span> dari 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalItems }}</span> attendance records
            </div>
            </div>
            <div class="flex items-center space-x-2">
              <Button 
                variant="outline" 
                size="sm" 
                @click="prevPage" 
                :disabled="currentPage === 1"
                class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
              >
                Previous
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                @click="nextPage" 
                :disabled="currentPage >= totalPages"
                class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
              >
                Next
              </Button>
            </div>
          </div>
        </div>
      </ScrollArea>
    </main>
  </div>
</template>
