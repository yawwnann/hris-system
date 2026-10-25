<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Plus, 
  Search, 
  Check, 
  X, 
  Eye, 
  Clock,
  FileText,
  CheckCircle2,
  AlertCircle,
  Calendar,
  User,
  Filter
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import { useAuthStore } from "@/stores/auth";
import moment from "moment";

const authStore = useAuthStore();
const overtimes = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");
const filterStatus = ref("all");

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref("10");
watch(itemsPerPage, () => {
  currentPage.value = 1;
  fetchOvertimes();
});
const totalPages = ref(1);
const totalItems = ref(0);

// Dialogs State
const isAddDialogOpen = ref(false);
const isDetailDialogOpen = ref(false);
const isSubmitting = ref(false);
const selectedOt = ref<any>(null);
const adminNoteInput = ref("");

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const formData = ref({
  date: "",
  start_time: "",
  end_time: "",
  reason: "",
  proof: "Hasil Pekerjaan",
});

const fetchOvertimes = async () => {
  loading.value = true;
  try {
    const params: any = { 
      search: searchQuery.value, 
      page: currentPage.value, 
      per_page: Number(itemsPerPage.value) 
    };
    if (filterStatus.value !== "all") {
      params.status = filterStatus.value;
    }
    const { data } = await api.get("/overtime-requests", { params });
    overtimes.value = data.data || [];
    totalPages.value = data.last_page || 1;
    totalItems.value = data.total || 0;
  } catch (error) {
    console.error("Gagal memuat data lembur", error);
    toast.error("Gagal memuat data lembur");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOvertimes();
});

const paginatedOvertimes = computed(() => overtimes.value);

const openDetailDialog = (ot: any) => {
  selectedOt.value = ot;
  adminNoteInput.value = ot.admin_note || "";
  isDetailDialogOpen.value = true;
};

watch([searchQuery, filterStatus], () => {
  currentPage.value = 1;
  fetchOvertimes();
});

watch(currentPage, () => {
  fetchOvertimes();
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const openAddDialog = () => {
  formData.value = {
    date: "",
    start_time: "",
    end_time: "",
    reason: "",
    proof: "Hasil Pekerjaan",
  };
  isAddDialogOpen.value = true;
};

const submitOvertimeRequest = async () => {
  if (!formData.value.date || !formData.value.start_time || !formData.value.end_time || !formData.value.reason) {
    toast.error("Harap isi semua kolom wajib");
    return;
  }

  isSubmitting.value = true;
  try {
    await api.post("/overtime-requests", formData.value);
    toast.success("Pengajuan lembur berhasil dikirim");
    isAddDialogOpen.value = false;
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal mengirim pengajuan");
  } finally {
    isSubmitting.value = false;
  }
};

const directAction = async (id: number, status: 'approved' | 'rejected', note = "") => {
  isSubmitting.value = true;
  try {
    await api.put(`/overtime-requests/${id}/status`, {
      status,
      admin_note: note,
    });
    toast.success(`Pengajuan lembur berhasil ${status === 'approved' ? 'disetujui' : 'ditolak'}`);
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal memproses persetujuan");
  } finally {
    isSubmitting.value = false;
  }
};

const actionFromDetail = async (status: 'approved' | 'rejected') => {
  if (!selectedOt.value) return;
  await directAction(selectedOt.value.id, status, adminNoteInput.value);
  isDetailDialogOpen.value = false;
};

const confirmDelete = (id: number) => {
  itemToDelete.value = id;
  isDeleteDialogOpen.value = true;
};

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  try {
    await api.delete(`/overtime-requests/${itemToDelete.value}`);
    toast.success("Pengajuan berhasil dibatalkan");
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal membatalkan pengajuan");
  } finally {
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
  }
};

const formatDate = (dateString: string) => moment(dateString).format("DD MMM YYYY");
const formatTime = (timeString: string) => timeString ? moment(timeString, "HH:mm:ss").format("HH:mm") : "-";
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
              Pengajuan Lembur
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Pantau kegiatan pengerjaan lembur karyawan, tinjau laporan output, dan kelola persetujuan.
            </p>
          </div>
          
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Filter Status -->
            <Select v-model="filterStatus">
              <SelectTrigger class="w-[150px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100 h-9">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectItem value="all">Semua Status</SelectItem>
                <SelectItem value="pending">Menunggu</SelectItem>
                <SelectItem value="approved">Disetujui</SelectItem>
                <SelectItem value="rejected">Ditolak</SelectItem>
              </SelectContent>
            </Select>

            <!-- Search -->
            <div class="relative w-full sm:w-64">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                placeholder="Cari nama atau kegiatan..."
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100 h-9"
              />
            </div>

            <Button v-if="authStore.user?.role !== 'admin'" @click="openAddDialog" class="bg-orange-600 hover:bg-orange-700 text-white">
              <Plus class="w-4 h-4 mr-2" /> Ajukan Lembur
            </Button>
          </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50 dark:bg-zinc-950/50">
                <TableRow class="border-b border-gray-200 dark:border-zinc-800 hover:bg-transparent">
                  <TableHead class="w-16 text-center font-semibold text-gray-600 dark:text-zinc-300">No.</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Karyawan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tanggal</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Waktu & Durasi</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Kegiatan Pengerjaan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Laporan Output</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Status</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300 pr-4">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="8" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Memuat data pengajuan lembur...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedOvertimes.length === 0">
                  <TableCell colspan="8" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Tidak ada data pengajuan lembur ditemukan.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(ot, index) in paginatedOvertimes" 
                  :key="ot.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <!-- No -->
                  <TableCell class="py-4 text-center text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * Number(itemsPerPage) + index + 1 }}
                  </TableCell>

                  <!-- Karyawan -->
                  <TableCell class="py-4">
                    <div class="font-semibold text-gray-900 dark:text-zinc-100">{{ ot.user?.name || '-' }}</div>
                    <div class="text-xs text-gray-500 dark:text-zinc-400">{{ ot.user?.email || '-' }}</div>
                  </TableCell>
                  
                  <!-- Tanggal -->
                  <TableCell class="py-4">
                    <div class="text-gray-900 dark:text-zinc-200 font-medium whitespace-nowrap">
                      {{ formatDate(ot.date) }}
                    </div>
                  </TableCell>
                  
                  <!-- Waktu & Durasi -->
                  <TableCell class="py-4 whitespace-nowrap">
                    <div class="text-gray-700 dark:text-zinc-300 font-mono text-xs">
                      {{ formatTime(ot.start_time) }} - {{ formatTime(ot.end_time) }}
                    </div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 mt-1">
                      <Clock class="w-3 h-3 mr-1" /> {{ ot.total_duration }} Jam
                    </div>
                  </TableCell>
                  
                  <!-- Kegiatan Pengerjaan -->
                  <TableCell class="py-4 max-w-[220px]">
                    <div class="text-gray-900 dark:text-zinc-100 font-medium truncate" :title="ot.reason || '-'">
                      {{ ot.reason || '-' }}
                    </div>
                    <div class="text-[11px] text-gray-400 truncate mt-0.5" v-if="ot.proof">
                      Bukti: {{ ot.proof }}
                    </div>
                  </TableCell>

                  <!-- Laporan Output -->
                  <TableCell class="py-4 max-w-[220px]">
                    <div v-if="ot.output" class="flex flex-col gap-1">
                      <span class="inline-flex items-center w-fit px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                        <CheckCircle2 class="w-3 h-3 mr-1 shrink-0" /> Ada Laporan
                      </span>
                      <span class="text-xs text-gray-700 dark:text-zinc-300 truncate" :title="ot.output">
                        {{ ot.output }}
                      </span>
                    </div>
                    <div v-else class="text-xs text-gray-400 dark:text-zinc-500 italic flex items-center">
                      <AlertCircle class="w-3.5 h-3.5 mr-1 text-gray-400 shrink-0" /> Belum diisi
                    </div>
                  </TableCell>
                  
                  <!-- Status -->
                  <TableCell class="py-4 text-center">
                    <Badge v-if="ot.status === 'approved'" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30">
                      Disetujui
                    </Badge>
                    <Badge v-else-if="ot.status === 'rejected'" variant="outline" class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30">
                      Ditolak
                    </Badge>
                    <Badge v-else variant="outline" class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30">
                      Menunggu
                    </Badge>
                  </TableCell>
                  
                  <!-- Aksi -->
                  <TableCell class="text-right py-4 pr-4 whitespace-nowrap">
                    <div class="flex items-center justify-end space-x-2">
                      <Button 
                        variant="outline" 
                        size="sm" 
                        @click="openDetailDialog(ot)" 
                        class="h-8 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800/50"
                      >
                        <Eye class="w-4 h-4 mr-1" /> Detail
                      </Button>

                      <template v-if="ot.status === 'pending' && authStore.user?.role === 'admin'">
                        <Button 
                          @click="directAction(ot.id, 'approved')" 
                          size="sm"
                          class="h-8 bg-green-600 hover:bg-green-700 text-white px-2.5"
                          title="Setujui"
                        >
                          <Check class="w-4 h-4 mr-1" /> Setujui
                        </Button>
                        <Button 
                          @click="directAction(ot.id, 'rejected')" 
                          size="sm"
                          variant="outline" 
                          class="h-8 text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700 px-2.5"
                          title="Tolak"
                        >
                          <X class="w-4 h-4 mr-1" /> Tolak
                        </Button>
                      </template>
                    </div>
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
                Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedOvertimes.length > 0 ? (currentPage - 1) * Number(itemsPerPage) + 1 : 0 }}</span> - 
                <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * Number(itemsPerPage), totalItems) }}</span> dari 
                <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalItems }}</span> pengajuan
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
                Sebelumnya
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                @click="nextPage" 
                :disabled="currentPage >= totalPages"
                class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
              >
                Selanjutnya
              </Button>
            </div>
          </div>
        </div>
      </ScrollArea>
    </main>

    <!-- Dialog Detail Pengajuan & Laporan Hasil Lembur -->
    <Dialog v-model:open="isDetailDialogOpen">
      <DialogContent class="sm:max-w-[620px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">
            <Clock class="w-5 h-5 mr-2 text-orange-600" /> Detail Pengajuan & Hasil Lembur
          </DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Tinjau rencana kegiatan dan laporan hasil pengerjaan nyata dari karyawan.
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="selectedOt" class="space-y-5 py-4">
          <!-- Grid Info Utama -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-gray-50 dark:bg-zinc-900/60 rounded-xl border border-gray-100 dark:border-zinc-800 text-sm">
            <div>
              <span class="text-xs text-gray-500 block">Karyawan</span>
              <span class="font-semibold text-gray-900 dark:text-zinc-100">{{ selectedOt.user?.name || '-' }}</span>
            </div>
            <div>
              <span class="text-xs text-gray-500 block">Tanggal</span>
              <span class="font-semibold text-gray-900 dark:text-zinc-100">{{ formatDate(selectedOt.date) }}</span>
            </div>
            <div>
              <span class="text-xs text-gray-500 block">Waktu Lembur</span>
              <span class="font-semibold text-gray-900 dark:text-zinc-100">{{ formatTime(selectedOt.start_time) }} - {{ formatTime(selectedOt.end_time) }}</span>
            </div>
            <div>
              <span class="text-xs text-gray-500 block">Total Durasi</span>
              <span class="font-semibold text-blue-600 dark:text-blue-400">{{ selectedOt.total_duration }} Jam</span>
            </div>
          </div>

          <!-- Status Bar -->
          <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50/50 dark:bg-zinc-900/40 rounded-lg border border-gray-100 dark:border-zinc-800 text-sm">
            <span class="text-gray-500">Status Persetujuan:</span>
            <Badge v-if="selectedOt.status === 'approved'" variant="outline" class="bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800/30">
              Disetujui
            </Badge>
            <Badge v-else-if="selectedOt.status === 'rejected'" variant="outline" class="bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/30">
              Ditolak
            </Badge>
            <Badge v-else variant="outline" class="bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800/30">
              Menunggu Persetujuan
            </Badge>
          </div>

          <!-- 1. Kegiatan Pengerjaan Lembur (Rencana Kerja) -->
          <div>
            <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wider mb-2">
              <FileText class="w-4 h-4 mr-1.5 text-orange-500" /> Kegiatan Pengerjaan Lembur
            </div>
            <div class="p-3.5 bg-gray-50 dark:bg-zinc-900/70 border border-gray-200 dark:border-zinc-800 rounded-xl text-sm leading-relaxed text-gray-800 dark:text-zinc-200 whitespace-pre-wrap">
              {{ selectedOt.reason || 'Tidak ada keterangan kegiatan.' }}
            </div>
          </div>

          <!-- 2. Laporan Output / Hasil Pengerjaan (Apa saja yang sudah dikerjakan) -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center text-xs font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wider">
                <CheckCircle2 class="w-4 h-4 mr-1.5 text-green-500" /> Laporan Output / Hasil Pekerjaan
              </div>
              <Badge v-if="selectedOt.output" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 text-[10px]">
                Sudah Dilaporkan
              </Badge>
              <Badge v-else variant="outline" class="bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400 border-gray-200 text-[10px]">
                Belum Diisi Karyawan
              </Badge>
            </div>
            
            <div v-if="selectedOt.output" class="p-3.5 bg-green-50/40 dark:bg-green-950/20 border border-green-200/70 dark:border-green-800/30 rounded-xl text-sm leading-relaxed text-gray-900 dark:text-zinc-100 whitespace-pre-wrap">
              {{ selectedOt.output }}
            </div>
            <div v-else class="p-4 bg-gray-50 dark:bg-zinc-900/40 border border-dashed border-gray-200 dark:border-zinc-800 rounded-xl text-xs text-gray-400 italic text-center">
              Karyawan belum menginput laporan output pekerjaan untuk sesi lembur ini.
            </div>
          </div>

          <!-- Catatan Admin -->
          <div v-if="selectedOt.status === 'pending' && authStore.user?.role === 'admin'">
            <Label for="admin_note" class="text-xs text-gray-600 dark:text-zinc-400 font-medium">Catatan Admin (Opsional):</Label>
            <Input 
              id="admin_note" 
              v-model="adminNoteInput" 
              placeholder="Tuliskan catatan persetujuan atau alasan jika menolak..." 
              class="mt-1.5 text-sm bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800"
            />
          </div>
          <div v-else-if="selectedOt.admin_note">
            <Label class="text-xs text-gray-500">Catatan Admin:</Label>
            <div class="text-sm mt-1 p-2.5 bg-gray-50 dark:bg-zinc-900/40 border border-gray-200 dark:border-zinc-800 rounded-lg text-gray-700 dark:text-zinc-300 italic">
              "{{ selectedOt.admin_note }}"
            </div>
          </div>
        </div>
        
        <DialogFooter class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between sm:justify-between w-full">
          <Button variant="outline" @click="isDetailDialogOpen = false">
            Tutup
          </Button>

          <div v-if="selectedOt?.status === 'pending' && authStore.user?.role === 'admin'" class="flex items-center space-x-2">
            <Button 
              variant="outline" 
              @click="actionFromDetail('rejected')" 
              :disabled="isSubmitting"
              class="text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700"
            >
              <X class="w-4 h-4 mr-1.5" /> Tolak Pengajuan
            </Button>
            <Button 
              @click="actionFromDetail('approved')" 
              :disabled="isSubmitting"
              class="bg-green-600 hover:bg-green-700 text-white"
            >
              <Check class="w-4 h-4 mr-1.5" /> Setujui Pengajuan
            </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Dialog Submit Overtime Request (Karyawan) -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Ajukan Permohonan Lembur</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Isi formulir di bawah ini untuk mengajukan jam lembur dan rencana kegiatan.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitOvertimeRequest" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="date" class="text-gray-700 dark:text-gray-300">Tanggal Lembur</Label>
            <Input 
              id="date" 
              type="date" 
              v-model="formData.date" 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" 
              required 
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="start_time" class="text-gray-700 dark:text-gray-300">Jam Mulai</Label>
              <Input 
                id="start_time" 
                type="time" 
                v-model="formData.start_time" 
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" 
                required 
              />
            </div>
            <div class="space-y-2">
              <Label for="end_time" class="text-gray-700 dark:text-gray-300">Jam Selesai</Label>
              <Input 
                id="end_time" 
                type="time" 
                v-model="formData.end_time" 
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" 
                required 
              />
            </div>
          </div>

          <div class="space-y-2">
            <Label for="reason" class="text-gray-700 dark:text-gray-300">Kegiatan Pengerjaan</Label>
            <Input 
              id="reason" 
              v-model="formData.reason" 
              placeholder="Tuliskan tugas yang akan dikerjakan saat lembur..." 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" 
              required 
            />
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isAddDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Batal
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-orange-600 hover:bg-orange-700 text-white">
              {{ isSubmitting ? 'Mengirim...' : 'Kirim Pengajuan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Alert Dialog Konfirmasi Hapus -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Batalkan Pengajuan?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-500 dark:text-zinc-400">
            Apakah Anda yakin ingin membatalkan pengajuan ini?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Tidak</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white hover:bg-red-700">Ya, Batalkan</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
