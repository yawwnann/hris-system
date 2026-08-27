<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Plus, 
  Search, 
  MoreHorizontal, 
  Edit, 
  Trash2,
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

// Data
const departments = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref("10");
watch(itemsPerPage, () => {
  currentPage.value = 1;
  fetchDepartments();
});
const totalPages = ref(1);
const totalItems = ref(0);

// Dialog State
const isDialogOpen = ref(false);
const isSubmitting = ref(false);
const editId = ref<number | null>(null);

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

const editMode = ref(false);
const formData = ref({
  id: null as null | number,
  name: "",
});

// Fetch Departments
const fetchDepartments = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/divisions", {
      params: { search: searchQuery.value, page: currentPage.value, per_page: Number(itemsPerPage.value) }
    });
    departments.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch departments", error);
    toast.error("Failed to fetch department data");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDepartments();
});

// Computed properties for filtering and pagination (Now handled by backend)
const filteredDepartments = computed(() => departments.value);
const paginatedDepartments = computed(() => departments.value);

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchDepartments();
});

watch(currentPage, () => {
  fetchDepartments();
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
    toast.error("Department name cannot be empty");
    return;
  }
  
  isSubmitting.value = true;
  try {
    if (editMode.value) {
      await api.put(`/divisions/${formData.value.id}`, { name: formData.value.name });
      toast.success("Department successfully updated");
    } else {
      await api.post("/divisions", { name: formData.value.name });
      toast.success("New department successfully added");
    }
    isDialogOpen.value = false;
    fetchDepartments();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Failed to save department");
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
    await api.delete(`/divisions/${itemToDelete.value}`);
    toast.success("Department successfully deleted");
    fetchDepartments();
  } catch (error) {
    toast.error("Failed to delete department");
  } finally {
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
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
            <p class="text-gray-500 dark:text-zinc-400 mt-1">Kelola daftar divisi beserta jumlah karyawannya.</p>
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
            <Button @click="openAddDialog" class="bg-orange-600 hover:bg-orange-700 text-white dark:text-white">
              <Plus class="w-4 h-4 mr-2" /> Add Department
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
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Nama Departemen</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Total Karyawan</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="5" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Loading department data...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedDepartments.length === 0">
                  <TableCell colspan="5" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    No departments found.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(dept, index) in paginatedDepartments" 
                  :key="dept.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell class="py-4 text-center text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                  </TableCell>
                  
            
                  
                  <TableCell class="py-4">
                    <div class="font-semibold text-gray-900 dark:text-zinc-100">{{ dept.name }}</div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400">
                      {{ dept.users_count || 0 }} Karyawan
                    </div>
                  </TableCell>
                  
                  <TableCell class="text-right py-4">
                    <div class="flex items-center justify-end space-x-2">
                      <Button variant="outline" size="sm" @click="openEditDialog(dept)" class="h-8 border-orange-200 dark:border-orange-800 text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20">
                        <Edit class="h-4 w-4 mr-1" /> Edit
                      </Button>
                      <Button variant="outline" size="sm" @click="confirmDelete(dept.id)" class="h-8 border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <Trash2 class="h-4 w-4 mr-1" /> Delete
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
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedDepartments.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}</span> - 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span> dari 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalItems }}</span> departemen
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

    <!-- Dialog Create / Edit -->
    <Dialog v-model:open="isDialogOpen">
      <DialogContent class="sm:max-w-[425px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">{{ editMode ? 'Edit Department' : 'Add Department' }}</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            {{ editMode ? 'Change the department name below.' : 'Enter a new department/division name.' }}
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="saveDepartment" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="name" class="text-gray-700 dark:text-gray-300">Nama Departemen</Label>
            <Input 
              id="name" 
              v-model="formData.name" 
              placeholder="Example: IT Support" 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              autoFocus
            />
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-orange-600 hover:bg-orange-700 text-white">
              {{ isSubmitting ? 'Saving...' : 'Save' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Alert Dialog Konfirmasi Hapus -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Delete Department?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-500 dark:text-zinc-400">
            This department will be deleted. Tindakan ini tidak dapat dibatalkan.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800">Batal</AlertDialogCancel>
          <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white hover:bg-red-700">Yes, Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>
