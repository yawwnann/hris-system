<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Users, 
  Plus, 
  Search, 
  MoreHorizontal, 
  Edit, 
  Trash2,
  Download,
  Eye
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
import EmployeeFormSheet from "@/components/dashboard/EmployeeFormSheet.vue";
import api from "@/lib/axios";
import { toast } from "vue-sonner";
import { useRouter } from "vue-router";

// Data
const router = useRouter();
const employees = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref("");

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref("10");
watch(itemsPerPage, () => {
  currentPage.value = 1;
  fetchEmployees();
});
const totalPages = ref(1);
const totalItems = ref(0);

// Form State
const isFormOpen = ref(false);
const selectedEmployee = ref<any>(null);

const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<number | null>(null);

// Fetch Karyawan
const fetchEmployees = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/users", {
      params: { search: searchQuery.value, page: currentPage.value, per_page: Number(itemsPerPage.value) }
    });
    employees.value = data.data;
    totalPages.value = data.last_page;
    totalItems.value = data.total;
  } catch (error) {
    console.error("Failed to fetch employees", error);
    toast.error("Failed to fetch employees data");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchEmployees();
});

const openAddForm = () => {
  selectedEmployee.value = null;
  isFormOpen.value = true;
};

const openEditForm = (employee: any) => {
  selectedEmployee.value = employee;
  isFormOpen.value = true;
};

const confirmDelete = (id: number) => {
  itemToDelete.value = id;
  isDeleteDialogOpen.value = true;
};

const executeDelete = async () => {
  if (!itemToDelete.value) return;
  
  try {
    await api.delete(`/users/${itemToDelete.value}`);
    toast.success("Employee successfully deleted");
    fetchEmployees();
  } catch (error) {
    toast.error("Failed to delete employee");
  } finally {
    isDeleteDialogOpen.value = false;
    itemToDelete.value = null;
  }
};

// Computed properties for filtering and pagination (Now handled by backend)
const filteredEmployees = computed(() => employees.value);
const paginatedEmployees = computed(() => employees.value);

watch(searchQuery, () => {
  currentPage.value = 1;
  fetchEmployees();
});

watch(currentPage, () => {
  fetchEmployees();
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
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
              Karyawan
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">Kelola data untuk semua karyawan, divisi, dan posisi.</p>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                placeholder="Cari berdasarkan nama atau NIK..." 
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button variant="outline" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              <Download class="w-4 h-4 mr-2" /> Export
            </Button>
            <Button @click="openAddForm" class="bg-orange-600 hover:bg-orange-700 text-white dark:text-white">
              <Plus class="w-4 h-4 mr-2" /> Add Employee
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
                  <TableHead class="w-[250px] font-semibold text-gray-600 dark:text-zinc-300">Employee</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Position & Division</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Contact</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell colspan="6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Loading employee data...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedEmployees.length === 0">
                  <TableCell colspan="6" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    No data found.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(employee, index) in paginatedEmployees" 
                  :key="employee.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <!-- No -->
                  <TableCell class="text-center py-4 text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                  </TableCell>
                  
                  <!-- Karyawan Info -->
                  <TableCell class="py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800 flex items-center justify-center text-orange-700 dark:text-orange-400 font-bold flex-shrink-0">
                        {{ employee.name.charAt(0) }}
                      </div>
                      <div class="min-w-0">
                        <div class="font-semibold text-gray-900 dark:text-zinc-100 truncate">{{ employee.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-zinc-400 truncate">NIK: {{ employee.nik || '-' }}</div>
                      </div>
                    </div>
                  </TableCell>
                  
                  <!-- Posisi & Divisi -->
                  <TableCell class="py-4">
                    <div class="text-gray-900 dark:text-zinc-200 font-medium">{{ employee.position?.name || '-' }}</div>
                    <div class="text-xs text-gray-500 dark:text-zinc-400">{{ employee.division?.name || '-' }}</div>
                  </TableCell>
                  
                  <!-- Kontak -->
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300 truncate max-w-[180px]">{{ employee.email }}</div>
                    <div class="text-xs text-gray-500 dark:text-zinc-400">{{ employee.phone || '-' }}</div>
                  </TableCell>
                  
                  <!-- Status -->
                  <TableCell class="py-4">
                    <Badge v-if="employee.status === 'active'" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30">
                      Active
                    </Badge>
                    <Badge v-else variant="outline" class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30">
                      Inactive
                    </Badge>
                  </TableCell>
                  
                  <!-- Aksi -->
                  <TableCell class="text-right py-4">
                    <div class="flex items-center justify-end space-x-2">
                      <Button variant="outline" size="sm" @click="router.push(`/employees/${employee.id}`)" class="h-8 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                        <Eye class="h-4 w-4 mr-1" /> Detail
                      </Button>
                      <Button variant="outline" size="sm" @click="openEditForm(employee)" class="h-8 border-orange-200 dark:border-orange-800 text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20">
                        <Edit class="h-4 w-4 mr-1" /> Edit
                      </Button>
                      <Button variant="outline" size="sm" @click="confirmDelete(employee.id)" class="h-8 border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
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
              Menampilkan <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedEmployees.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}</span> - 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span> dari 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalItems }}</span> karyawan
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
    
    <!-- Form Create / Edit Karyawan -->
    <EmployeeFormSheet 
      v-model="isFormOpen" 
      :employeeToEdit="selectedEmployee" 
      @saved="fetchEmployees" 
    />

    <!-- Alert Dialog Konfirmasi Hapus -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Delete Employee?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-500 dark:text-zinc-400">
            This employee will be deleted. All related data cannot be recovered.
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
