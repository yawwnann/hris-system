<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Calendar,
  Plus, 
  Search,
  Trash2,
  Eye,
  Paperclip
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
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
import moment from "moment";

const leaves = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");
const filterStatus = ref("all");
const filterType = ref("all");
const sortDir = ref("desc");

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref("10");
watch(itemsPerPage, () => {
  currentPage.value = 1;
  fetchLeaves();
});
const totalPages = ref(1);
const totalItems = ref(0);

// Dialogs State
const isAddDialogOpen = ref(false);
const isSubmitting = ref(false);

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const formData = ref<{
  type: string;
  start_date: string;
  end_date: string;
  reason: string;
  attachment: File | null;
}>({
  type: "annual",
  start_date: "",
  end_date: "",
  reason: "",
  attachment: null,
});

const fetchLeaves = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/leave-requests", {
      params: { 
        search: searchQuery.value, 
        page: currentPage.value, 
        per_page: Number(itemsPerPage.value),
        status: filterStatus.value,
        type: filterType.value,
        sort_by: "created_at",
        sort_dir: sortDir.value
      }
    });
    leaves.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch leave requests", error);
    toast.error("Gagal memuat data cuti");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchLeaves();
});

const paginatedLeaves = computed(() => leaves.value);

watch([searchQuery, filterStatus, filterType, sortDir], () => {
  currentPage.value = 1;
  fetchLeaves();
});

watch(currentPage, () => {
  fetchLeaves();
});

const isDetailDialogOpen = ref(false);
const selectedLeave = ref<any>(null);

const openDetailDialog = (leave: any) => {
  selectedLeave.value = leave;
  isDetailDialogOpen.value = true;
};

const getAttachmentUrl = (path: string) => {
  if (!path) return '#';
  if (path.startsWith('http')) return path;
  return `http://localhost:8000/storage/${path}`;
};

const openAddDialog = () => {
  formData.value = {
    type: "annual",
    start_date: "",
    end_date: "",
    reason: "",
    attachment: null,
  };
  isAddDialogOpen.value = true;
};

const submitLeaveRequest = async () => {
  if (!formData.value.start_date || !formData.value.end_date || !formData.value.reason) {
    toast.error("Harap isi semua kolom yang wajib");
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = new FormData();
    payload.append("type", formData.value.type);
    payload.append("start_date", formData.value.start_date);
    payload.append("end_date", formData.value.end_date);
    payload.append("reason", formData.value.reason);
    if (formData.value.attachment) {
      payload.append("attachment", formData.value.attachment);
    }

    await api.post("/leave-requests", payload, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    });
    
    toast.success("Pengajuan cuti berhasil dikirim");
    isAddDialogOpen.value = false;
    fetchLeaves();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal mengirim pengajuan cuti");
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
    await api.delete(`/leave-requests/${itemToDelete.value}`);
    toast.success("Pengajuan berhasil dibatalkan");
    fetchLeaves();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal membatalkan pengajuan");
  } finally {
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
  }
};

const formatDate = (dateString: string) => moment(dateString).format("DD MMM YYYY");

const getTypeLabel = (type: string) => {
  const map: Record<string, string> = {
    annual: "Cuti Tahunan",
    sick: "Cuti Sakit",
    permission: "Izin",
    maternity: "Cuti Melahirkan"
  };
  return map[type] || type;
};

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    formData.value.attachment = target.files[0];
  } else {
    formData.value.attachment = null;
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
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-zinc-100 flex items-center">
              Pengajuan Cuti & Izin
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              Ajukan dan pantau status permohonan cuti atau izin Anda.
            </p>
          </div>
          
          <div class="flex flex-wrap items-center gap-3">
            <Select v-model="filterStatus">
              <SelectTrigger class="w-[130px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectItem value="all">Semua Status</SelectItem>
                <SelectItem value="pending">Menunggu</SelectItem>
                <SelectItem value="approved">Disetujui</SelectItem>
                <SelectItem value="rejected">Ditolak</SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="filterType">
              <SelectTrigger class="w-[120px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                <SelectValue placeholder="Tipe" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectItem value="all">Semua Tipe</SelectItem>
                <SelectItem value="annual">Cuti Tahunan</SelectItem>
                <SelectItem value="sick">Cuti Sakit</SelectItem>
                <SelectItem value="permission">Izin</SelectItem>
                <SelectItem value="maternity">Cuti Melahirkan</SelectItem>
              </SelectContent>
            </Select>

            <div class="relative w-56 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                placeholder="Cari alasan..."
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            
            <Button @click="openAddDialog" class="bg-orange-600 hover:bg-orange-700 text-white">
              <Plus class="w-4 h-4 mr-2" /> Ajukan Cuti
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
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tipe</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Rentang Tanggal</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Alasan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300 pr-4">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Memuat data pengajuan...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedLeaves.length === 0">
                  <TableCell colspan="6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Belum ada riwayat pengajuan cuti.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(leave, index) in paginatedLeaves" 
                  :key="leave.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell class="py-4 text-center text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * Number(itemsPerPage) + index + 1 }}
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="inline-flex items-center text-gray-700 dark:text-zinc-300 font-medium">
                      <Calendar class="w-3.5 h-3.5 mr-1.5 text-orange-500" />
                      {{ getTypeLabel(leave.type) }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300 whitespace-nowrap">
                      {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300 truncate max-w-[250px]" :title="leave.reason">
                      {{ leave.reason }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <Badge v-if="leave.status === 'approved'" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30">
                      Disetujui
                    </Badge>
                    <Badge v-else-if="leave.status === 'rejected'" variant="outline" class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30">
                      Ditolak
                    </Badge>
                    <Badge v-else variant="outline" class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30">
                      Menunggu
                    </Badge>
                  </TableCell>
                  
                  <TableCell class="text-right py-4 pr-4">
                    <div class="flex items-center justify-end space-x-2">
                      <Button @click="openDetailDialog(leave)" size="icon" variant="ghost" class="h-8 w-8 text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white">
                        <Eye class="w-4 h-4" />
                      </Button>
                      <Button 
                        v-if="leave.status === 'pending'"
                        @click="confirmDelete(leave.id)" 
                        size="icon" 
                        variant="ghost" 
                        class="h-8 w-8 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
                        title="Batalkan"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </ScrollArea>
    </main>

    <!-- Dialog Ajukan Cuti -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
        <DialogHeader>
          <DialogTitle>Form Pengajuan Cuti / Izin</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Isi detail pengajuan cuti atau izin Anda di bawah ini.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitLeaveRequest" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label>Tipe Pengajuan</Label>
            <Select v-model="formData.type">
              <SelectTrigger class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700">
                <SelectValue />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectItem value="annual">Cuti Tahunan</SelectItem>
                <SelectItem value="sick">Cuti Sakit</SelectItem>
                <SelectItem value="permission">Izin</SelectItem>
                <SelectItem value="maternity">Cuti Melahirkan</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Tanggal Mulai</Label>
              <Input type="date" v-model="formData.start_date" class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700" required />
            </div>
            <div class="space-y-2">
              <Label>Tanggal Selesai</Label>
              <Input type="date" v-model="formData.end_date" class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700" required />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Alasan</Label>
            <Textarea v-model="formData.reason" placeholder="Tuliskan alasan pengajuan..." class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 resize-none h-24" required />
          </div>

          <div class="space-y-2">
            <Label>Lampiran Pendukung (Opsional)</Label>
            
            <div v-if="formData.type === 'maternity'" class="mb-2">
              <Select>
                <SelectTrigger class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 mb-2">
                  <SelectValue placeholder="Pilih Jenis Dokumen..." />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectItem value="dokter">Surat Keterangan Dokter/Bidan</SelectItem>
                  <SelectItem value="rs">Surat Keterangan Rumah Sakit</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <Input type="file" @change="handleFileUpload" class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 cursor-pointer" />
            <p class="text-xs text-gray-500">Format: JPG, PNG, atau PDF (Maks. 2MB)</p>
          </div>

          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isAddDialogOpen = false">Batal</Button>
            <Button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white" :disabled="isSubmitting">
              {{ isSubmitting ? 'Mengirim...' : 'Kirim Pengajuan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Dialog Detail -->
    <Dialog v-model:open="isDetailDialogOpen">
      <DialogContent class="sm:max-w-[450px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" v-if="selectedLeave">
        <DialogHeader>
          <DialogTitle>Detail Pengajuan</DialogTitle>
        </DialogHeader>
        <div class="space-y-3 py-2 text-sm">
          <div class="flex justify-between border-b border-gray-100 dark:border-zinc-800 pb-2">
            <span class="text-gray-500">Tipe</span>
            <span class="font-medium">{{ getTypeLabel(selectedLeave.type) }}</span>
          </div>
          <div class="flex justify-between border-b border-gray-100 dark:border-zinc-800 pb-2">
            <span class="text-gray-500">Tanggal</span>
            <span class="font-medium">{{ formatDate(selectedLeave.start_date) }} - {{ formatDate(selectedLeave.end_date) }}</span>
          </div>
          <div class="flex justify-between border-b border-gray-100 dark:border-zinc-800 pb-2">
            <span class="text-gray-500">Status</span>
            <span class="font-medium uppercase text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-zinc-800">{{ selectedLeave.status }}</span>
          </div>
          <div>
            <span class="text-gray-500 block mb-1">Alasan:</span>
            <p class="bg-gray-50 dark:bg-zinc-800/50 p-3 rounded-lg text-gray-700 dark:text-zinc-300">{{ selectedLeave.reason }}</p>
          </div>
          <div v-if="selectedLeave.attachment">
            <span class="text-gray-500 block mb-1">Lampiran:</span>
            <a :href="getAttachmentUrl(selectedLeave.attachment)" target="_blank" class="inline-flex items-center text-orange-600 hover:underline">
              <Paperclip class="w-4 h-4 mr-1.5" /> Lihat Lampiran File
            </a>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isDetailDialogOpen = false">Tutup</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Alert Dialog Delete -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle>Batalkan Pengajuan?</AlertDialogTitle>
          <AlertDialogDescription>
            Pengajuan yang dibatalkan tidak dapat dikembalikan.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Batal</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" class="bg-red-600 hover:bg-red-700 text-white">Ya, Batalkan</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
