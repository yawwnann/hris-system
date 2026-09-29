<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Plus, 
  Search,
  Trash2,
  Eye,
  Clock
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
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import moment from "moment";

const overtimes = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");

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
const isSubmitting = ref(false);

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const formData = ref({
  date: "",
  start_time: "",
  end_time: "",
  reason: "",
});

const fetchOvertimes = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/overtime-requests", {
      params: { search: searchQuery.value, page: currentPage.value, per_page: Number(itemsPerPage.value) }
    });
    overtimes.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch overtime requests", error);
    toast.error("Gagal memuat data lembur");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchOvertimes();
});

const paginatedOvertimes = computed(() => overtimes.value);

const isDetailDialogOpen = ref(false);
const selectedOt = ref<any>(null);

const openDetailDialog = (ot: any) => {
  selectedOt.value = ot;
  isDetailDialogOpen.value = true;
};

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchOvertimes();
});

watch(currentPage, () => {
  fetchOvertimes();
});

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
    toast.error("Harap isi semua kolom yang wajib");
    return;
  }

  isSubmitting.value = true;
  try {
    await api.post("/overtime-requests", formData.value);
    toast.success("Pengajuan lembur berhasil dikirim");
    isAddDialogOpen.value = false;
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal mengirim pengajuan lembur");
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
              Ajukan dan pantau status permohonan lembur Anda.
            </p>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                placeholder="Cari alasan..."
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button @click="openAddDialog" class="bg-orange-600 hover:bg-orange-700 text-white">
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
                  <TableCell colspan="7" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Memuat data lembur...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedOvertimes.length === 0">
                  <TableCell colspan="7" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Belum ada riwayat pengajuan lembur.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(ot, index) in paginatedOvertimes" 
                  :key="ot.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell class="py-4 text-center text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * Number(itemsPerPage) + index + 1 }}
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
                    <div class="flex items-center justify-end space-x-2">
                      <Button @click="openDetailDialog(ot)" size="icon" variant="ghost" class="h-8 w-8 text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white">
                        <Eye class="w-4 h-4" />
                      </Button>
                      <Button 
                        v-if="ot.status === 'pending'"
                        @click="confirmDelete(ot.id)" 
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

    <!-- Dialog Pengajuan Lembur -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
        <DialogHeader>
          <DialogTitle>Form Pengajuan Lembur</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Isi detail rencana lembur Anda di bawah ini.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitOvertimeRequest" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label>Tanggal</Label>
            <Input type="date" v-model="formData.date" class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700" required />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Jam Mulai</Label>
              <Input type="time" v-model="formData.start_time" class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700" required />
            </div>
            <div class="space-y-2">
              <Label>Jam Selesai</Label>
              <Input type="time" v-model="formData.end_time" class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700" required />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Alasan / Pekerjaan</Label>
            <Textarea v-model="formData.reason" placeholder="Tuliskan tugas atau alasan lembur..." class="bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 resize-none h-24" required />
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
      <DialogContent class="sm:max-w-[450px] bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100" v-if="selectedOt">
        <DialogHeader>
          <DialogTitle>Detail Lembur</DialogTitle>
        </DialogHeader>
        <div class="space-y-3 py-2 text-sm">
          <div class="flex justify-between border-b border-gray-100 dark:border-zinc-800 pb-2">
            <span class="text-gray-500">Tanggal</span>
            <span class="font-medium">{{ formatDate(selectedOt.date) }}</span>
          </div>
          <div class="flex justify-between border-b border-gray-100 dark:border-zinc-800 pb-2">
            <span class="text-gray-500">Waktu</span>
            <span class="font-medium">{{ formatTime(selectedOt.start_time) }} - {{ formatTime(selectedOt.end_time) }} ({{ selectedOt.total_duration }} Jam)</span>
          </div>
          <div class="flex justify-between border-b border-gray-100 dark:border-zinc-800 pb-2">
            <span class="text-gray-500">Status</span>
            <span class="font-medium uppercase text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-zinc-800">{{ selectedOt.status }}</span>
          </div>
          <div>
            <span class="text-gray-500 block mb-1">Alasan:</span>
            <p class="bg-gray-50 dark:bg-zinc-800/50 p-3 rounded-lg text-gray-700 dark:text-zinc-300">{{ selectedOt.reason }}</p>
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
          <AlertDialogTitle>Batalkan Pengajuan Lembur?</AlertDialogTitle>
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
