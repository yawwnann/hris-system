<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Calendar,
  Plus, 
  Search,
  MoreHorizontal,
  CheckCircle,
  XCircle,
  Clock
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
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

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;
const totalPages = ref(1);
const totalItems = ref(0);

// Dialogs State
const isAddDialogOpen = ref(false);
const isApprovalDialogOpen = ref(false);
const isSubmitting = ref(false);

const formData = ref({
  type: "annual",
  start_date: "",
  end_date: "",
  reason: "",
});

const approvalData = ref({
  id: null as number | null,
  status: "approved",
  admin_note: "",
});

const fetchLeaves = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/leave-requests", {
      params: { search: searchQuery.value, page: currentPage.value, per_page: itemsPerPage }
    });
    leaves.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch leave requests", error);
    toast.error("Gagal mengambil data pengajuan cuti/izin");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchLeaves();
});

const filteredLeaves = computed(() => leaves.value);
const paginatedLeaves = computed(() => leaves.value);

watch(searchQuery, () => {
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

const openAddDialog = () => {
  formData.value = {
    type: "annual",
    start_date: "",
    end_date: "",
    reason: "",
  };
  isAddDialogOpen.value = true;
};

const submitLeaveRequest = async () => {
  if (!formData.value.start_date || !formData.value.end_date || !formData.value.reason) {
    toast.error("Harap isi semua field yang wajib");
    return;
  }

  isSubmitting.value = true;
  try {
    await api.post("/leave-requests", formData.value);
    toast.success("Pengajuan cuti/izin berhasil dikirim");
    isAddDialogOpen.value = false;
    fetchLeaves();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal mengirim pengajuan");
  } finally {
    isSubmitting.value = false;
  }
};

const openApprovalDialog = (leave: any) => {
  approvalData.value = {
    id: leave.id,
    status: "approved",
    admin_note: "",
  };
  isApprovalDialogOpen.value = true;
};

const submitApproval = async () => {
  if (!approvalData.value.id) return;
  
  isSubmitting.value = true;
  try {
    await api.put(`/leave-requests/${approvalData.value.id}/status`, {
      status: approvalData.value.status,
      admin_note: approvalData.value.admin_note,
    });
    toast.success(`Pengajuan berhasil di-${approvalData.value.status === 'approved' ? 'setujui' : 'tolak'}`);
    isApprovalDialogOpen.value = false;
    fetchLeaves();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal memproses persetujuan");
  } finally {
    isSubmitting.value = false;
  }
};

const formatDate = (dateString: string) => moment(dateString).format("DD MMM YYYY");

const getTypeLabel = (type: string) => {
  const map: Record<string, string> = {
    annual: "Cuti Tahunan",
    sick: "Sakit",
    permission: "Izin"
  };
  return map[type] || type;
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
              {{ authStore.user?.role === 'admin' ? 'Kelola persetujuan cuti dan izin karyawan.' : 'Ajukan dan pantau status cuti atau izin Anda.' }}
            </p>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                :placeholder="authStore.user?.role === 'admin' ? 'Cari nama atau alasan...' : 'Cari alasan...'"
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button v-if="authStore.user?.role !== 'admin'" @click="openAddDialog" class="bg-indigo-600 hover:bg-indigo-700 text-white dark:text-white">
              <Plus class="w-4 h-4 mr-2" /> Ajukan Cuti/Izin
            </Button>
          </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50 dark:bg-zinc-950/50">
                <TableRow class="border-b border-gray-200 dark:border-zinc-800 hover:bg-transparent">
                  <TableHead v-if="authStore.user?.role === 'admin'" class="font-semibold text-gray-600 dark:text-zinc-300">Karyawan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Jenis</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tanggal</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Alasan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                  <TableHead v-if="authStore.user?.role === 'admin'" class="text-right font-semibold text-gray-600 dark:text-zinc-300 pr-4">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 6 : 5" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Memuat data pengajuan...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedLeaves.length === 0">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 6 : 5" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Tidak ada data pengajuan.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="leave in paginatedLeaves" 
                  :key="leave.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell v-if="authStore.user?.role === 'admin'" class="py-4">
                    <div class="font-medium text-gray-900 dark:text-zinc-100">{{ leave.user?.name || '-' }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="font-medium text-gray-900 dark:text-zinc-200">{{ getTypeLabel(leave.type) }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300">
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
                  
                  <TableCell v-if="authStore.user?.role === 'admin'" class="text-right py-4 pr-4">
                    <DropdownMenu v-if="leave.status === 'pending'">
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-40 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                        <DropdownMenuItem @click="openApprovalDialog(leave)" class="cursor-pointer text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                          <CheckCircle class="mr-2 h-4 w-4" /> Proses
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                    <span v-else class="text-gray-400 dark:text-zinc-600 text-xs">-</span>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <!-- Pagination -->
          <div class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-zinc-950/50">
            <div class="text-sm text-gray-500 dark:text-zinc-400">
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedLeaves.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}</span> - 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span> dari 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalItems }}</span> pengajuan
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

    <!-- Dialog Submit Leave Request -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Ajukan Cuti / Izin</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Isi formulir di bawah ini untuk mengajukan cuti atau izin ketidakhadiran.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitLeaveRequest" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-gray-700 dark:text-gray-300">Jenis Pengajuan</Label>
            <Select v-model="formData.type">
              <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                <SelectValue placeholder="Pilih jenis" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectGroup>
                  <SelectItem value="annual">Cuti Tahunan</SelectItem>
                  <SelectItem value="sick">Sakit</SelectItem>
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
            <Input 
              id="reason" 
              v-model="formData.reason" 
              placeholder="Jelaskan alasan secara singkat..." 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              required
            />
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isAddDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Batal
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white">
              {{ isSubmitting ? 'Mengirim...' : 'Kirim Pengajuan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Dialog Approval -->
    <Dialog v-model:open="isApprovalDialogOpen">
      <DialogContent class="sm:max-w-[425px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Proses Pengajuan</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Tentukan persetujuan untuk pengajuan ini.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitApproval" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-gray-700 dark:text-gray-300">Status Persetujuan</Label>
            <Select v-model="approvalData.status">
              <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                <SelectValue placeholder="Pilih status" />
              </SelectTrigger>
              <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                <SelectGroup>
                  <SelectItem value="approved">Setujui</SelectItem>
                  <SelectItem value="rejected">Tolak</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          
          <div class="space-y-2">
            <Label for="admin_note" class="text-gray-700 dark:text-gray-300">Catatan (Opsional)</Label>
            <Input 
              id="admin_note" 
              v-model="approvalData.admin_note" 
              placeholder="Tambahkan catatan jika perlu..." 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
            />
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isApprovalDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Batal
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white">
              {{ isSubmitting ? 'Memproses...' : 'Simpan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

  </div>
</template>
