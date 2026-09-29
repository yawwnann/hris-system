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
  Clock,
  Building2,
  Upload,
  FileText,
  Eye,
  Trash2
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
const documents = ref<any[]>([]);
const storageUrl = import.meta.env.VITE_STORAGE_URL || 'http://localhost:8000/storage';

const isUploadDialogOpen = ref(false);
const uploading = ref(false);
const docForm = ref({
  type: '',
  file: null as File | null
});

const monthsList = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];
const currentYear = new Date().getFullYear();
const yearsList = Array.from({ length: 5 }, (_, i) => currentYear - i);

const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(currentYear);

const calculateTenure = (joinDate: string | null) => {
  if (!joinDate) return '-';
  const start = moment(joinDate);
  const now = moment();
  const years = now.diff(start, 'years');
  start.add(years, 'years');
  const months = now.diff(start, 'months');
  
  if (years === 0 && months === 0) return '< 1 bulan';
  let result = [];
  if (years > 0) result.push(`${years} tahun`);
  if (months > 0) result.push(`${months} bulan`);
  return result.join(' ');
};

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
    console.error("Gagal memuat detail karyawan", error);
    toast.error("Gagal memuat detail karyawan");
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
    case 'online': return 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30';
    case 'terlambat': return 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30';
    case 'overtime': return 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800/30';
    case 'absent': return 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30';
    default: return 'bg-gray-50 dark:bg-gray-900/20 text-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-800/30';
  }
};

const statItems = [
  { key: 'online', label: 'Hadir (Online)', icon: CheckCircle2, color: 'text-green-600 dark:text-green-400', bg: 'bg-green-50 dark:bg-green-900/20' },
  { key: 'terlambat', label: 'Terlambat', icon: Clock, color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-50 dark:bg-yellow-900/20' },
  { key: 'overtime', label: 'Overtime', icon: Clock, color: 'text-purple-600 dark:text-purple-400', bg: 'bg-purple-50 dark:bg-purple-900/20' },
  { key: 'absent', label: 'Absen', icon: XCircle, color: 'text-red-600 dark:text-red-400', bg: 'bg-red-50 dark:bg-red-900/20' },
  { key: 'leave', label: 'Cuti', icon: AlertCircle, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-900/20' },
];

const fetchDocuments = async () => {
  try {
    const { data } = await api.get(`/users/${employeeId}/documents`);
    documents.value = data;
  } catch (error) {
    console.error("Failed to fetch documents", error);
  }
};

const openUploadDialog = () => {
  docForm.value.type = '';
  docForm.value.file = null;
  isUploadDialogOpen.value = true;
};

const handleFileUpload = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    docForm.value.file = target.files[0];
  }
};

const uploadDocument = async () => {
  if (!docForm.value.file || !docForm.value.type) {
    toast.error('Pilih file dan isi jenis dokumen');
    return;
  }
  
  uploading.value = true;
  const formData = new FormData();
  formData.append('type', docForm.value.type);
  formData.append('file', docForm.value.file);
  
  try {
    await api.post(`/users/${employeeId}/documents`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    toast.success('Dokumen berhasil diupload');
    isUploadDialogOpen.value = false;
    fetchDocuments();
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Gagal mengupload dokumen');
  } finally {
    uploading.value = false;
  }
};

const deleteDocument = async (id: number) => {
  if (confirm('Yakin ingin menghapus dokumen ini?')) {
    try {
      await api.delete(`/employee-documents/${id}`);
      toast.success('Dokumen berhasil dihapus');
      fetchDocuments();
    } catch (error) {
      toast.error('Gagal menghapus dokumen');
    }
  }
};

onMounted(() => {
  fetchDocuments();
});
</script>

<template>
  <div class="min-h-screen flex bg-[#fbfbfb] dark:bg-zinc-950 text-sm transition-colors">
    <AppSidebar />
    
    <main class="flex-1 flex flex-col min-w-0">
      <AppHeader />
      
      <ScrollArea class="flex-1 p-8">
        <AppBreadcrumb />
        
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8 gap-4">
          <div class="flex items-center gap-3">
            <Button variant="outline" size="icon" @click="router.push('/employees')" class="h-9 w-9 rounded-lg border-gray-200 dark:border-zinc-800 text-gray-500 hover:text-gray-900 hover:border-gray-300 dark:text-zinc-400 dark:hover:text-zinc-100">
              <ArrowLeft class="h-4 w-4" />
            </Button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100">Detail Karyawan</h1>
              <p class="text-gray-500 dark:text-zinc-400 mt-0.5">Profile & rekap absensi karyawan.</p>
            </div>
          </div>

        </div>

        <div v-if="loading" class="flex justify-center items-center py-24">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
        </div>

        <div v-else-if="employee" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <!-- Left Column: Profile Card -->
          <div class="lg:col-span-1">
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
              <div class="h-24 bg-gradient-to-br from-orange-500 to-orange-600 dark:from-orange-600 dark:to-orange-700"></div>
              <div class="p-6 -mt-12 text-center">
                <div class="w-full flex justify-center">
                  <div class="w-24 h-24 rounded-full bg-orange-100 dark:bg-zinc-800 border-4 border-white dark:border-zinc-900 flex items-center justify-center text-orange-700 dark:text-orange-400 text-3xl font-bold shadow-md">
                    {{ employee.name.charAt(0) }}
                  </div>
                </div>
                
                <h2 class="text-xl font-bold text-gray-900 dark:text-zinc-100 mt-4">{{ employee.name }}</h2>
                <p class="text-gray-500 dark:text-zinc-400 mb-3">{{ employee.position?.name || '-' }}</p>
                
                <div class="flex justify-center gap-2 flex-wrap">
                  <Badge v-if="employee.employment_status" variant="outline" class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/30 capitalize">
                    {{ employee.employment_status }}
                  </Badge>
                  <Badge v-else variant="outline" class="bg-gray-50 dark:bg-gray-900/20 text-gray-700 dark:text-gray-400 border-gray-200 dark:border-gray-800/30">
                    -
                  </Badge>
                  <Badge v-if="employee.role" variant="outline" class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/30 capitalize">
                    {{ employee.role }}
                  </Badge>
                </div>
              </div>

              <div class="px-6 pb-6 space-y-1">
                <div class="border-t border-gray-100 dark:border-zinc-800 pt-4 space-y-4">
                  <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                      <UserIcon class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                    </div>
                    <div class="min-w-0">
                      <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-zinc-500">NIK</div>
                      <div class="text-sm font-medium truncate">{{ employee.nik || '-' }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                      <Mail class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                    </div>
                    <div class="min-w-0">
                      <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-zinc-500">Email</div>
                      <div class="text-sm font-medium truncate">{{ employee.email }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                      <Phone class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                    </div>
                    <div class="min-w-0">
                      <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-zinc-500">No. WhatsApp</div>
                      <div class="text-sm font-medium truncate">
                        <a v-if="employee.phone" :href="`https://wa.me/${employee.phone.replace(/[^0-9]/g, '')}`" target="_blank" class="hover:text-green-600">
                          {{ employee.phone }}
                        </a>
                        <span v-else>-</span>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                      <Briefcase class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                    </div>
                    <div class="min-w-0">
                      <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-zinc-500">Bidang (Divisi)</div>
                      <div class="text-sm font-medium truncate">{{ employee.division?.name || '-' }}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 text-gray-600 dark:text-zinc-300">
                    <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-zinc-800 flex items-center justify-center flex-shrink-0">
                      <CalendarIcon class="w-4 h-4 text-gray-400 dark:text-zinc-500" />
                    </div>
                    <div class="min-w-0">
                      <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-zinc-500">Bergabung / Lama Bekerja</div>
                      <div class="text-sm font-medium">
                        {{ employee.join_date ? moment(employee.join_date).format('DD MMM YYYY') : '-' }} 
                        <span class="text-gray-400 text-xs ml-1">({{ calculateTenure(employee.join_date) }})</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Stats & Recent Absensi -->
          <div class="lg:col-span-2 space-y-8 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-gray-200 dark:border-zinc-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-zinc-100">
                  Rekap Absensi
                </h2>
                <p class="text-sm text-gray-500 dark:text-zinc-400 mt-0.5">
                  {{ monthsList[selectedMonth - 1] }} {{ selectedYear }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <select v-model="selectedMonth" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg text-sm px-3 py-2 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30">
                  <option v-for="(m, index) in monthsList" :key="index" :value="index + 1">{{ m }}</option>
                </select>
                <select v-model="selectedYear" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg text-sm px-3 py-2 text-gray-700 dark:text-gray-300 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30">
                  <option v-for="y in yearsList" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
              <div v-for="item in statItems" :key="item.key" class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-5">
                <div class="flex items-center gap-2.5 mb-3">
                  <div class="p-2 rounded-lg" :class="item.bg">
                    <component :is="item.icon" class="w-4 h-4" :class="item.color" />
                  </div>
                  <h3 class="font-medium text-gray-600 dark:text-zinc-400 text-sm">{{ item.label }}</h3>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-zinc-100">
                  <template v-if="item.key === 'leave'">{{ (stats?.leave || 0) + (stats?.sick || 0) }}</template>
                  <template v-else>{{ stats?.[item.key] || 0 }}</template>
                </div>
              </div>
            </div>

            <!-- Recent Absensi Table -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-zinc-100">Riwayat Absensi</h3>
              </div>
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader class="bg-gray-50 dark:bg-zinc-950/50">
                    <TableRow class="border-b border-gray-200 dark:border-zinc-800">
                      <TableHead class="w-16 text-center font-semibold text-gray-600 dark:text-zinc-300">No.</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tanggal</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Masuk</TableHead>
                      <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Keluar</TableHead>
                      <TableHead class="text-center font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="recentAttendances.length === 0">
                      <TableCell colspan="5" class="h-24 text-center text-gray-500 dark:text-zinc-400">
                        Tidak ada riwayat absensi.
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
                      <TableCell class="py-3 text-gray-900 dark:text-zinc-200">{{ moment(att.date).format('dddd, DD MMM YYYY') }}</TableCell>
                      <TableCell class="py-3 text-gray-600 dark:text-zinc-300">{{ att.time_in ? moment(att.time_in, 'HH:mm:ss').format('HH:mm') : '-' }}</TableCell>
                      <TableCell class="py-3 text-gray-600 dark:text-zinc-300">{{ att.time_out ? moment(att.time_out, 'HH:mm:ss').format('HH:mm') : '-' }}</TableCell>
                      <TableCell class="py-3 text-center">
                        <Badge variant="outline" :class="getStatusBadgeVariant(att.status)" class="capitalize">
                          {{ att.status }}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>

            <!-- Documents Section -->
            <div class="mt-8 border-t border-gray-100 dark:border-zinc-800 pt-8">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-zinc-100">
                  Dokumen Karyawan
                </h2>
                <Button @click="openUploadDialog" variant="outline" class="bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                  <Upload class="w-4 h-4 mr-2" /> Upload Dokumen
                </Button>
              </div>
              
              <div v-if="documents.length === 0" class="text-center py-8 text-gray-500 dark:text-zinc-400 border border-dashed rounded-lg border-gray-200 dark:border-zinc-800">
                Belum ada dokumen yang di-upload.
              </div>
              <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="doc in documents" :key="doc.id" class="p-4 border border-gray-200 dark:border-zinc-800 rounded-lg flex items-center justify-between bg-gray-50 dark:bg-zinc-900/50">
                  <div class="flex items-center min-w-0 pr-4">
                    <FileText class="w-8 h-8 text-orange-500 mr-3 flex-shrink-0" />
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-gray-900 dark:text-zinc-100 truncate">{{ doc.type }}</p>
                      <p class="text-xs text-gray-500 truncate">{{ doc.file_name }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <a :href="`${storageUrl}/${doc.file_path}`" target="_blank" class="p-2 text-gray-500 hover:text-orange-600 bg-white dark:bg-zinc-800 rounded-md border border-gray-200 dark:border-zinc-700 shadow-sm">
                      <Eye class="w-4 h-4" />
                    </a>
                    <button @click="deleteDocument(doc.id)" class="p-2 text-gray-500 hover:text-red-600 bg-white dark:bg-zinc-800 rounded-md border border-gray-200 dark:border-zinc-700 shadow-sm">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </ScrollArea>
      
      <!-- Upload Document Dialog -->
      <Dialog v-model:open="isUploadDialogOpen">
        <DialogContent class="sm:max-w-[425px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
          <DialogHeader>
            <DialogTitle class="text-gray-900 dark:text-zinc-100">Upload Dokumen</DialogTitle>
            <DialogDescription class="text-gray-500 dark:text-zinc-400">
              Pilih jenis dokumen dan file untuk diupload (KTP, CV, dll).
            </DialogDescription>
          </DialogHeader>
          
          <form @submit.prevent="uploadDocument" class="space-y-4 py-4">
            <div class="space-y-2">
              <Label class="text-gray-700 dark:text-gray-300">Jenis Dokumen</Label>
              <Input 
                v-model="docForm.type" 
                placeholder="Contoh: KTP, CV, Ijazah" 
                required
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-700 dark:text-gray-300">File Dokumen</Label>
              <Input 
                type="file" 
                @change="handleFileUpload" 
                required
                accept=".pdf,.jpg,.jpeg,.png"
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              />
              <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG (Max 5MB)</p>
            </div>
            
            <DialogFooter class="pt-4">
              <Button type="button" variant="outline" @click="isUploadDialogOpen = false" class="bg-white dark:bg-zinc-900 text-gray-700 border-gray-200">Batal</Button>
              <Button type="submit" :disabled="uploading" class="bg-orange-600 hover:bg-orange-700 text-white">
                {{ uploading ? 'Uploading...' : 'Upload' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </main>
  </div>
</template>
