<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Plus, 
  Search,
  MoreHorizontal,
  CheckCircle,
  XCircle,
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
const overtimes = ref<any[]>([]);
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

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const formData = ref({
  date: "",
  start_time: "",
  end_time: "",
  reason: "",
});

const approvalData = ref({
  id: null as number | null,
  status: "approved",
  admin_note: "",
});

const fetchOvertimes = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/overtime-requests", {
      params: { search: searchQuery.value, page: currentPage.value, per_page: itemsPerPage }
    });
    overtimes.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch overtime requests", error);
    toast.error("Gagal mengambil data pengajuan lembur");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOvertimes();
});

const filteredOvertimes = computed(() => overtimes.value);
const paginatedOvertimes = computed(() => overtimes.value);

watch(searchQuery, () => {
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
  };
  isAddDialogOpen.value = true;
};

const submitOvertimeRequest = async () => {
  if (!formData.value.date || !formData.value.start_time || !formData.value.end_time || !formData.value.reason) {
    toast.error("Harap isi semua field yang wajib");
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

const openApprovalDialog = (ot: any) => {
  approvalData.value = {
    id: ot.id,
    status: "approved",
    admin_note: "",
  };
  isApprovalDialogOpen.value = true;
};

const submitApproval = async () => {
  if (!approvalData.value.id) return;
  
  isSubmitting.value = true;
  try {
    await api.put(`/overtime-requests/${approvalData.value.id}/status`, {
      status: approvalData.value.status,
      admin_note: approvalData.value.admin_note,
    });
    toast.success(`Pengajuan berhasil di-${approvalData.value.status === 'approved' ? 'setujui' : 'tolak'}`);
    isApprovalDialogOpen.value = false;
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal memproses persetujuan");
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
              {{ authStore.user?.role === 'admin' ? 'Kelola persetujuan lembur karyawan.' : 'Ajukan dan pantau status lembur Anda.' }}
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
                  <TableHead v-if="authStore.user?.role === 'admin'" class="font-semibold text-gray-600 dark:text-zinc-300">Karyawan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Tanggal</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Waktu</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Durasi</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Alasan</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300 pr-4">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 7 : 6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Memuat data pengajuan...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedOvertimes.length === 0">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 7 : 6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Tidak ada data pengajuan.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="ot in paginatedOvertimes" 
                  :key="ot.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell v-if="authStore.user?.role === 'admin'" class="py-4">
                    <div class="font-medium text-gray-900 dark:text-zinc-100">{{ ot.user?.name || '-' }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-900 dark:text-zinc-200">
                      {{ formatDate(ot.date) }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300 whitespace-nowrap">
                      {{ formatTime(ot.start_time) }} - {{ formatTime(ot.end_time) }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4 text-center">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400">
                      {{ ot.total_duration }} Jam
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300 truncate max-w-[200px]" :title="ot.reason">
                      {{ ot.reason }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
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
                  
                  <TableCell class="text-right py-4 pr-4">
                    <DropdownMenu v-if="ot.status === 'pending'">
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-40 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                        <DropdownMenuItem v-if="authStore.user?.role === 'admin'" @click="openApprovalDialog(ot)" class="cursor-pointer text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                          <CheckCircle class="mr-2 h-4 w-4" /> Proses
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="confirmDelete(ot.id)" class="cursor-pointer text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                          <XCircle class="mr-2 h-4 w-4" /> Batalkan
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
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedOvertimes.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}</span> - 
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

    <!-- Dialog Submit Overtime Request -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Ajukan Lembur</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Isi formulir di bawah ini untuk mengajukan jam lembur Anda.
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
            <Label for="reason" class="text-gray-700 dark:text-gray-300">Pekerjaan/Alasan</Label>
            <Input 
              id="reason" 
              v-model="formData.reason" 
              placeholder="Jelaskan pekerjaan yang dilakukan..." 
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
            Tentukan persetujuan untuk lembur ini.
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
