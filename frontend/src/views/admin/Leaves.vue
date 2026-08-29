<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Calendar,
  Plus, 
  Search,
  Check,
  X,
  Eye,
  Paperclip
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
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
  SelectGroup,
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
    toast.error("Failed to fetch leave requests");
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

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

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
    toast.error("Please fill in all required fields");
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
    
    toast.success("Leave request submitted successfully");
    isAddDialogOpen.value = false;
    fetchLeaves();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Failed to submit request");
  } finally {
    isSubmitting.value = false;
  }
};



const directAction = async (id: number, status: 'approved' | 'rejected') => {
  isSubmitting.value = true;
  try {
    await api.put(`/leave-requests/${id}/status`, {
      status,
      admin_note: "",
    });
    toast.success(`Pengajuan berhasil ${status}`);
    fetchLeaves();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Failed to process approval");
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
    toast.error(error.response?.data?.message || "Failed to cancel request");
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
    permission: "Izin"
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
              Pengajuan Cuti
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              {{ authStore.user?.role === 'admin' ? 'Manage employee leave requests and approvals.' : 'Ajukan dan pantau status permohonan cuti Anda.' }}
            </p>
          </div>
          
          <div class="flex flex-wrap items-center gap-3">
            <Select v-model="filterStatus">
              <SelectTrigger class="w-[120px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
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
                <SelectItem value="annual">Annual</SelectItem>
                <SelectItem value="sick">Sick</SelectItem>
                <SelectItem value="permission">Izin</SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="sortDir">
              <SelectTrigger class="w-[110px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                <SelectValue placeholder="Urutkan" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectItem value="desc">Terbaru</SelectItem>
                <SelectItem value="asc">Terlama</SelectItem>
              </SelectContent>
            </Select>

            <div class="relative w-56 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                :placeholder="authStore.user?.role === 'admin' ? 'Search name or reason...' : 'Search reason...'"
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button v-if="authStore.user?.role !== 'admin'" @click="openAddDialog" class="bg-orange-600 hover:bg-orange-700 text-white dark:text-white">
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
                  <TableHead v-if="authStore.user?.role === 'admin'" class="font-semibold text-gray-600 dark:text-zinc-300">Employee</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tipe</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Rentang Tanggal</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Alasan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300 pr-4">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 7 : 6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Loading request data...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedLeaves.length === 0">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 7 : 6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    No request data found.
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
                  <TableCell v-if="authStore.user?.role === 'admin'" class="py-4">
                    <div class="font-medium text-gray-900 dark:text-zinc-100">{{ leave.user?.name || '-' }}</div>
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
                    <div class="text-gray-700 dark:text-zinc-300 truncate max-w-[200px]" :title="leave.reason">
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
                    <div v-if="leave.status === 'pending'" class="flex items-center justify-end space-x-2">
                      <Button 
                        v-if="authStore.user?.role === 'admin'" 
                        @click="openDetailDialog(leave)" 
                        size="icon" 
                        variant="outline" 
                        class="h-8 w-auto px-2 text-yellow-600 border-yellow-200 hover:bg-yellow-50 hover:border-yellow-300 hover:text-yellow-700 rounded-full transition-colors"
                        title="View Details"
                      >
                        <Eye class="w-4 h-4" />
                        Need Action
                      </Button>
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
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedLeaves.length > 0 ? (currentPage - 1) * Number(itemsPerPage) + 1 : 0 }}</span> - 
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

    <!-- Dialog Submit Leave Request -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Ajukan Permohonan Cuti</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Isi formulir di bawah ini untuk mengajukan permohonan cuti.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitLeaveRequest" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-gray-700 dark:text-gray-300">Tipe Pengajuan</Label>
            <Select v-model="formData.type">
              <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                <SelectValue placeholder="Select type" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectGroup>
                  <SelectItem value="annual">Cuti Tahunan</SelectItem>
                  <SelectItem value="sick">Cuti Sakit</SelectItem>
                  <SelectItem value="permission">Izin</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="start_date" class="text-gray-700 dark:text-gray-300">Tanggal Mulai</Label>
              <Input 
                id="start_date" 
                type="date"
                v-model="formData.start_date" 
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                required
              />
            </div>
            <div class="space-y-2">
              <Label for="end_date" class="text-gray-700 dark:text-gray-300">Tanggal Selesai</Label>
              <Input 
                id="end_date" 
                type="date"
                v-model="formData.end_date" 
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                required
              />
            </div>
          </div>
          
          <div class="space-y-2">
            <Label for="reason" class="text-gray-700 dark:text-gray-300">Alasan</Label>
            <Textarea 
              id="reason" 
              v-model="formData.reason" 
              placeholder="Jelaskan alasan secara singkat..." 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 resize-none h-24"
              required
            />
          </div>

          <div class="space-y-2" v-if="formData.type === 'sick' || formData.type === 'permission'">
            <Label for="attachment" class="text-gray-700 dark:text-gray-300">Lampiran Bukti (Opsional)</Label>
            <Input 
              id="attachment" 
              type="file"
              @change="handleFileUpload" 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100 cursor-pointer file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
              accept=".pdf,.jpg,.jpeg,.png"
            />
            <p class="text-xs text-gray-500 mt-1">Sertakan surat keterangan dokter atau dokumen pendukung lainnya.</p>
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isAddDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-orange-600 hover:bg-orange-700 text-white">
              {{ isSubmitting ? 'Mengirim...' : 'Submit Request' }}
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
            Are you sure you want to cancel this request?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800">No</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white hover:bg-red-700">Yes, Cancel</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Dialog View Leave Detail -->
    <Dialog v-model:open="isDetailDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Detail Pengajuan Cuti</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Lihat informasi detail tentang pengajuan cuti ini.
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="selectedLeave" class="space-y-4 py-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label class="text-xs text-gray-500">Nama Karyawan</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ selectedLeave.user?.name }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Tipe Cuti</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ getTypeLabel(selectedLeave.type) }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Tanggal Mulai</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ formatDate(selectedLeave.start_date) }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Tanggal Selesai</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ formatDate(selectedLeave.end_date) }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Status</Label>
              <div class="mt-1">
                <Badge v-if="selectedLeave.status === 'approved'" variant="outline" class="bg-green-50 text-green-700 border-green-200 px-2 py-0.5 text-xs rounded-full">Disetujui</Badge>
                <Badge v-else-if="selectedLeave.status === 'rejected'" variant="outline" class="bg-red-50 text-red-700 border-red-200 px-2 py-0.5 text-xs rounded-full">Ditolak</Badge>
                <Badge v-else variant="outline" class="bg-yellow-50 text-yellow-700 border-yellow-200 px-2 py-0.5 text-xs rounded-full">Menunggu</Badge>
              </div>
            </div>
          </div>
          
          <div class="border-t border-gray-100 dark:border-zinc-800 my-4"></div>
          
          <div>
            <Label class="text-xs text-gray-500">Alasan</Label>
            <div class="text-sm mt-1.5 bg-gray-50 dark:bg-zinc-900/50 p-3 rounded-lg border border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              {{ selectedLeave.reason }}
            </div>
          </div>
          
          <div v-if="selectedLeave.attachment">
            <Label class="text-xs text-gray-500">Attachment / Proof</Label>
            <div class="mt-1.5">
              <a :href="getAttachmentUrl(selectedLeave.attachment)" target="_blank" class="inline-flex items-center text-sm px-4 py-2 bg-orange-50 text-orange-700 hover:bg-orange-100 dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-900/50 rounded-lg transition-colors border border-orange-100 dark:border-orange-800/30">
                <Paperclip class="w-4 h-4 mr-2" /> View Document
              </a>
            </div>
          </div>
          
          <div v-if="selectedLeave.admin_note">
            <Label class="text-xs text-gray-500">Admin Note</Label>
            <div class="text-sm mt-1.5 text-gray-700 dark:text-gray-300 italic">
              "{{ selectedLeave.admin_note }}"
            </div>
          </div>
        </div>
        
        <DialogFooter v-if="selectedLeave?.status === 'pending' && authStore.user?.role === 'admin'" class="pt-4 border-t border-gray-100 dark:border-zinc-800">
          <Button variant="outline" @click="directAction(selectedLeave.id, 'rejected'); isDetailDialogOpen = false" class="text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700">
            Reject
          </Button>
          <Button @click="directAction(selectedLeave.id, 'approved'); isDetailDialogOpen = false" class="bg-green-600 hover:bg-green-700 text-white">
            Approve
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
