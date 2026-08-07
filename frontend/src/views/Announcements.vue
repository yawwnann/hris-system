<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Plus, 
  Search,
  MoreHorizontal,
  Edit,
  Trash2,
  Megaphone,
  CalendarDays
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Textarea } from "@/components/ui/textarea";
import { ScrollArea } from "@/components/ui/scroll-area";
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
const announcements = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");

// Pagination
const currentPage = ref(1);
const itemsPerPage = 9; // 3x3 grid
const totalPages = ref(1);
const totalItems = ref(0);

const isAddDialogOpen = ref(false);
const isSubmitting = ref(false);
const editId = ref<number | null>(null);

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const formData = ref({
  title: "",
  content: "",
  status: "published",
  publish_date: "",
});

const fetchAnnouncements = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/announcements", {
      params: { search: searchQuery.value, page: currentPage.value, per_page: itemsPerPage }
    });
    announcements.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch announcements", error);
    toast.error("Gagal mengambil data pengumuman");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAnnouncements();
});

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchAnnouncements();
});

watch(currentPage, () => {
  fetchAnnouncements();
});

const openAddDialog = () => {
  editId.value = null;
  formData.value = {
    title: "",
    content: "",
    status: "published",
    publish_date: moment().format("YYYY-MM-DDTHH:mm"),
  };
  isAddDialogOpen.value = true;
};

const openEditDialog = (announcement: any) => {
  editId.value = announcement.id;
  formData.value = {
    title: announcement.title,
    content: announcement.content,
    status: announcement.status,
    publish_date: announcement.publish_date ? moment(announcement.publish_date).format("YYYY-MM-DDTHH:mm") : "",
  };
  isAddDialogOpen.value = true;
};

const submitAnnouncement = async () => {
  if (!formData.value.title || !formData.value.content) {
    toast.error("Harap isi judul dan konten pengumuman");
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = {
      ...formData.value,
      publish_date: formData.value.publish_date ? moment(formData.value.publish_date).format("YYYY-MM-DD HH:mm:ss") : null,
    };
    if (editId.value) {
      await api.put(`/announcements/${editId.value}`, payload);
      toast.success("Pengumuman berhasil diperbarui");
    } else {
      await api.post("/announcements", payload);
      toast.success("Pengumuman berhasil dibuat");
    }
    isAddDialogOpen.value = false;
    fetchAnnouncements();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal menyimpan pengumuman");
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
    await api.delete(`/announcements/${itemToDelete.value}`);
    toast.success("Pengumuman berhasil dihapus");
    fetchAnnouncements();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal menghapus pengumuman");
  } finally {
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
  }
};

const formatDate = (dateString: string) => moment(dateString).format("DD MMM YYYY, HH:mm");
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
              <Megaphone class="w-6 h-6 mr-3 text-indigo-600 dark:text-indigo-400" />
              Pengumuman
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              {{ authStore.user?.role === 'admin' ? 'Kelola informasi dan pengumuman untuk seluruh karyawan.' : 'Informasi dan pengumuman terbaru dari perusahaan.' }}
            </p>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                placeholder="Cari pengumuman..."
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button v-if="authStore.user?.role === 'admin'" @click="openAddDialog" class="bg-indigo-600 hover:bg-indigo-700 text-white">
              <Plus class="w-4 h-4 mr-2" /> Buat Pengumuman
            </Button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="loading" class="flex justify-center items-center h-64">
           <div class="text-gray-500 dark:text-zinc-400">Memuat pengumuman...</div>
        </div>
        <div v-else-if="announcements.length === 0" class="flex flex-col items-center justify-center h-64 bg-white dark:bg-zinc-900 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700">
           <Megaphone class="w-12 h-12 text-gray-300 dark:text-zinc-600 mb-4" />
           <p class="text-gray-500 dark:text-zinc-400">Belum ada pengumuman saat ini.</p>
        </div>

        <!-- Grid Container -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="item in announcements" 
            :key="item.id"
            class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm hover:shadow-md transition-shadow flex flex-col overflow-hidden"
          >
            <div class="p-6 flex-1 flex flex-col">
              <div class="flex justify-between items-start mb-4">
                <Badge :variant="item.status === 'published' ? 'default' : 'secondary'" :class="item.status === 'published' ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400'">
                  {{ item.status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                </Badge>
                
                <DropdownMenu v-if="authStore.user?.role === 'admin'">
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" class="h-8 w-8 -mr-2 text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-36 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                    <DropdownMenuItem @click="openEditDialog(item)" class="cursor-pointer text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                      <Edit class="mr-2 h-4 w-4" /> Edit
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="confirmDelete(item.id)" class="cursor-pointer text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                      <Trash2 class="mr-2 h-4 w-4" /> Hapus
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
              
              <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100 mb-2 leading-tight">
                {{ item.title }}
              </h3>
              
              <p class="text-gray-600 dark:text-zinc-400 line-clamp-4 leading-relaxed whitespace-pre-wrap flex-1">
                {{ item.content }}
              </p>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950/50 flex items-center justify-between text-xs text-gray-500 dark:text-zinc-500 mt-auto">
               <div class="flex items-center">
                 <CalendarDays class="w-3.5 h-3.5 mr-1.5" />
                 {{ item.publish_date ? formatDate(item.publish_date) : formatDate(item.created_at) }}
               </div>
            </div>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-8 flex items-center justify-between bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 px-6 py-4 rounded-xl shadow-sm">
          <div class="text-sm text-gray-500 dark:text-zinc-400">
            Halaman <span class="font-medium text-gray-900 dark:text-zinc-100">{{ currentPage }}</span> dari <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalPages }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <Button 
              variant="outline" 
              size="sm" 
              @click="currentPage--" 
              :disabled="currentPage === 1"
              class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
            >
              Sebelumnya
            </Button>
            <Button 
              variant="outline" 
              size="sm" 
              @click="currentPage++" 
              :disabled="currentPage >= totalPages"
              class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300"
            >
              Selanjutnya
            </Button>
          </div>
        </div>

      </ScrollArea>
    </main>

    <!-- Dialog Submit/Edit Announcement -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[600px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">{{ editId ? 'Edit Pengumuman' : 'Buat Pengumuman Baru' }}</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Pastikan isi pesan jelas dan mudah dipahami oleh semua karyawan.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitAnnouncement" class="space-y-5 py-4">
          <div class="space-y-2">
            <Label for="title" class="text-gray-700 dark:text-gray-300">Judul Pengumuman</Label>
            <Input 
              id="title" 
              v-model="formData.title" 
              placeholder="Contoh: Perubahan Jam Kerja..." 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              required
            />
          </div>

          <div class="space-y-2">
            <Label for="content" class="text-gray-700 dark:text-gray-300">Isi Pengumuman</Label>
            <Textarea 
              id="content" 
              v-model="formData.content" 
              placeholder="Tuliskan rincian pengumuman disini..." 
              class="min-h-[150px] bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="text-gray-700 dark:text-gray-300">Status</Label>
              <Select v-model="formData.status">
                <SelectTrigger class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100">
                  <SelectValue placeholder="Pilih status" />
                </SelectTrigger>
                <SelectContent class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                  <SelectGroup>
                    <SelectItem value="published">Publish Sekarang</SelectItem>
                    <SelectItem value="draft">Simpan sbg Draft</SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="publish_date" class="text-gray-700 dark:text-gray-300">Tanggal Publikasi (Opsional)</Label>
              <Input 
                id="publish_date" 
                type="datetime-local"
                v-model="formData.publish_date" 
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              />
            </div>
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isAddDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Batal
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white">
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pengumuman' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Alert Dialog Konfirmasi Hapus -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Apakah Anda yakin?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-500 dark:text-zinc-400">
            Pengumuman ini akan dihapus secara permanen dan tidak dapat dikembalikan.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Batal</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white hover:bg-red-700">Hapus Pengumuman</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>
