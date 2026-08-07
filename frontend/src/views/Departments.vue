<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { 
  Building2, 
  Plus, 
  Search, 
  MoreHorizontal, 
  Edit, 
  Trash2,
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";

// Data
const departments = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// Dialog State
const isDialogOpen = ref(false);
const isSubmitting = ref(false);
const editMode = ref(false);
const formData = ref({
  id: null as null | number,
  name: "",
});

// Fetch Departments
const fetchDepartments = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/divisions");
    departments.value = data;
  } catch (error) {
    console.error("Failed to fetch departments", error);
    toast.error("Gagal mengambil data departemen");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDepartments();
});

// Computed properties for filtering and pagination
const filteredDepartments = computed(() => {
  if (!searchQuery.value) return departments.value;
  const lowerCaseQuery = searchQuery.value.toLowerCase();
  return departments.value.filter(
    (dept) => dept.name?.toLowerCase().includes(lowerCaseQuery)
  );
});

const totalPages = computed(() => Math.ceil(filteredDepartments.value.length / itemsPerPage));

const paginatedDepartments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredDepartments.value.slice(start, end);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

// Handlers
const openAddDialog = () => {
  editMode.value = false;
  formData.value = { id: null, name: "" };
  isDialogOpen.value = true;
};

const openEditDialog = (dept: any) => {
  editMode.value = true;
  formData.value = { id: dept.id, name: dept.name };
  isDialogOpen.value = true;
};

const saveDepartment = async () => {
  if (!formData.value.name.trim()) {
    toast.error("Nama departemen tidak boleh kosong");
    return;
  }
  
  isSubmitting.value = true;
  try {
    if (editMode.value) {
      await api.put(`/divisions/${formData.value.id}`, { name: formData.value.name });
      toast.success("Departemen berhasil diperbarui");
    } else {
      await api.post("/divisions", { name: formData.value.name });
      toast.success("Departemen baru berhasil ditambahkan");
    }
    isDialogOpen.value = false;
    fetchDepartments();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Gagal menyimpan departemen");
  } finally {
    isSubmitting.value = false;
  }
};

const deleteDepartment = async (id: number) => {
  if (!confirm("Apakah Anda yakin ingin menghapus departemen ini?")) return;
  
  try {
    await api.delete(`/divisions/${id}`);
    toast.success("Departemen berhasil dihapus");
    fetchDepartments();
  } catch (error) {
    toast.error("Gagal menghapus departemen");
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
              Departemen & Divisi
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">Kelola daftar divisi dan jumlah karyawannya.</p>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                placeholder="Cari departemen..." 
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button @click="openAddDialog" class="bg-indigo-600 hover:bg-indigo-700 text-white dark:text-white">
              <Plus class="w-4 h-4 mr-2" /> Tambah Departemen
            </Button>
          </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50 dark:bg-zinc-950/50">
                <TableRow class="border-b border-gray-200 dark:border-zinc-800 hover:bg-transparent">
                  <TableHead class="w-16 text-center font-semibold text-gray-600 dark:text-zinc-300">ID</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Nama Departemen</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Total Karyawan</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="4" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Memuat data departemen...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedDepartments.length === 0">
                  <TableCell colspan="4" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Tidak ada departemen ditemukan.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="dept in paginatedDepartments" 
                  :key="dept.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell class="py-4 text-center text-gray-500 dark:text-zinc-400 font-medium">
                    #{{ dept.id }}
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="font-semibold text-gray-900 dark:text-zinc-100">{{ dept.name }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400">
                      {{ dept.users_count || 0 }} Karyawan
                    </div>
                  </TableCell>
                  
                  <TableCell class="text-right py-4">
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-500 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-100">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-40 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                        <DropdownMenuItem @click="openEditDialog(dept)" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-700 dark:text-gray-300">
                          <Edit class="mr-2 h-4 w-4" /> Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="deleteDepartment(dept.id)" class="cursor-pointer text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 focus:text-red-600 dark:focus:text-red-400 focus:bg-red-50 dark:focus:bg-red-900/20">
                          <Trash2 class="mr-2 h-4 w-4" /> Hapus
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <!-- Pagination -->
          <div class="border-t border-gray-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-zinc-950/50">
            <div class="text-sm text-gray-500 dark:text-zinc-400">
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedDepartments.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}</span> - 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * itemsPerPage, filteredDepartments.length) }}</span> dari 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ filteredDepartments.length }}</span> departemen
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

    <!-- Dialog Create / Edit -->
    <Dialog v-model:open="isDialogOpen">
      <DialogContent class="sm:max-w-[425px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">{{ editMode ? 'Edit Departemen' : 'Tambah Departemen' }}</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            {{ editMode ? 'Ubah nama departemen di bawah ini.' : 'Masukkan nama departemen/divisi baru.' }}
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="saveDepartment" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="name" class="text-gray-700 dark:text-gray-300">Nama Departemen</Label>
            <Input 
              id="name" 
              v-model="formData.name" 
              placeholder="Contoh: IT Support" 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              autoFocus
            />
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Batal
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white">
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>
