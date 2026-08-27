<script setup lang="ts">
import { ref, onMounted, computed, watch } from "vue";
import { 
  Plus, 
  Search,
  Check,
  X,
  Eye,
  Clock
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
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
    toast.error("Failed to fetch overtime requests");
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
    toast.error("Please fill in all required fields");
    return;
  }

  isSubmitting.value = true;
  try {
    await api.post("/overtime-requests", formData.value);
    toast.success("Overtime request submitted successfully");
    isAddDialogOpen.value = false;
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Failed to submit request");
  } finally {
    isSubmitting.value = false;
  }
};

const directAction = async (id: number, status: 'approved' | 'rejected') => {
  isSubmitting.value = true;
  try {
    await api.put(`/overtime-requests/${id}/status`, {
      status,
      admin_note: "",
    });
    toast.success(`Request successfully ${status}`);
    fetchOvertimes();
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
    await api.delete(`/overtime-requests/${itemToDelete.value}`);
    toast.success("Request cancelled successfully");
    fetchOvertimes();
  } catch (error: any) {
    toast.error(error.response?.data?.message || "Failed to cancel request");
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
              Overtime Requests
            </h1>
            <p class="text-gray-500 dark:text-zinc-400 mt-1">
              {{ authStore.user?.role === 'admin' ? 'Manage employee overtime requests and approvals.' : 'Submit and track your overtime request status.' }}
            </p>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="relative w-64 hidden md:block">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500" />
              <Input 
                v-model="searchQuery"
                :placeholder="authStore.user?.role === 'admin' ? 'Search name or reason...' : 'Search reason...'"
                class="pl-9 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-gray-100"
              />
            </div>
            <Button v-if="authStore.user?.role !== 'admin'" @click="openAddDialog" class="bg-indigo-600 hover:bg-indigo-700 text-white dark:text-white">
              <Plus class="w-4 h-4 mr-2" /> Request Overtime
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
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Date</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Time</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300 text-center">Duration</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Reason</TableHead>
                  <TableHead class="font-semibold text-gray-600 dark:text-zinc-300">Status</TableHead>
                  <TableHead class="text-right font-semibold text-gray-600 dark:text-zinc-300 pr-4">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="loading">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 8 : 7" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    Loading request data...
                  </TableCell>
                </TableRow>
                <TableRow v-else-if="paginatedOvertimes.length === 0">
                  <TableCell :colspan="authStore.user?.role === 'admin' ? 8 : 7" class="h-32 text-center text-gray-500 dark:text-zinc-400">
                    No request data found.
                  </TableCell>
                </TableRow>
                <TableRow 
                  v-else
                  v-for="(ot, index) in paginatedOvertimes" 
                  :key="ot.id"
                  class="border-b border-gray-100 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                  <TableCell class="py-4 text-center text-gray-500 dark:text-zinc-400 font-medium">
                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                  </TableCell>
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
                      {{ ot.total_duration }} Hours
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <div class="text-gray-700 dark:text-zinc-300 truncate max-w-[200px]" :title="ot.reason">
                      {{ ot.reason }}
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-4">
                    <Badge v-if="ot.status === 'approved'" variant="outline" class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30">
                      Approved
                    </Badge>
                    <Badge v-else-if="ot.status === 'rejected'" variant="outline" class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30">
                      Rejected
                    </Badge>
                    <Badge v-else variant="outline" class="bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30">
                      Pending
                    </Badge>
                  </TableCell>
                  
                  <TableCell class="text-right py-4 pr-4">
                    <div class="flex items-center justify-end space-x-2">
                    
                      <template v-if="ot.status === 'pending'">
                        <Button 
                          v-if="authStore.user?.role === 'admin'" 
                          @click="directAction(ot.id, 'approved')" 
                          size="icon" 
                          variant="outline" 
                          class="h-8 w-8 text-green-600 border-green-200 hover:bg-green-50 hover:border-green-300 hover:text-green-700 rounded-full transition-colors"
                          title="Approve"
                        >
                          <Check class="w-4 h-4" />
                        </Button>
                        <Button 
                          v-if="authStore.user?.role === 'admin'" 
                          @click="directAction(ot.id, 'rejected')" 
                          size="icon" 
                          variant="outline" 
                          class="h-8 w-8 text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 hover:text-red-700 rounded-full transition-colors"
                          title="Reject"
                        >
                          <X class="w-4 h-4" />
                        </Button>
                        <Button 
                          v-else 
                          @click="confirmDelete(ot.id)" 
                          size="icon" 
                          variant="outline" 
                          class="h-8 w-8 text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 hover:text-red-700 rounded-full transition-colors"
                          title="Cancel Request"
                        >
                          <X class="w-4 h-4" />
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
            <div class="text-sm text-gray-500 dark:text-zinc-400">
              Showing <span class="font-medium text-gray-900 dark:text-zinc-100">{{ paginatedOvertimes.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }}</span> - 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span> of 
              <span class="font-medium text-gray-900 dark:text-zinc-100">{{ totalItems }}</span> requests
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

    <!-- Dialog Submit Overtime Request -->
    <Dialog v-model:open="isAddDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Submit Overtime Request</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            Fill out the form below to request overtime hours.
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitOvertimeRequest" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label for="date" class="text-gray-700 dark:text-gray-300">Overtime Date</Label>
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
              <Label for="start_time" class="text-gray-700 dark:text-gray-300">Start Time</Label>
              <Input 
                id="start_time" 
                type="time"
                v-model="formData.start_time" 
                class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
                required
              />
            </div>
            <div class="space-y-2">
              <Label for="end_time" class="text-gray-700 dark:text-gray-300">End Time</Label>
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
            <Label for="reason" class="text-gray-700 dark:text-gray-300">Task/Reason</Label>
            <Input 
              id="reason" 
              v-model="formData.reason" 
              placeholder="Explain the tasks performed..." 
              class="bg-gray-50 dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-900 dark:text-zinc-100"
              required
            />
          </div>
          
          <DialogFooter class="pt-4">
            <Button type="button" variant="outline" @click="isAddDialogOpen = false" class="bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 hover:bg-indigo-700 text-white">
              {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Dialog View Overtime Detail -->
    <Dialog v-model:open="isDetailDialogOpen">
      <DialogContent class="sm:max-w-[500px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-zinc-100">Overtime Request Details</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-zinc-400">
            View detailed information about this overtime request.
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="selectedOt" class="space-y-4 py-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label class="text-xs text-gray-500">Employee Name</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ selectedOt.user?.name }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Date</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ formatDate(selectedOt.date) }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Time</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ formatTime(selectedOt.start_time) }} - {{ formatTime(selectedOt.end_time) }}</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Total Duration</Label>
              <div class="font-medium text-sm mt-1 text-gray-900 dark:text-gray-100">{{ selectedOt.total_duration }} Hours</div>
            </div>
            <div>
              <Label class="text-xs text-gray-500">Status</Label>
              <div class="mt-1">
                <Badge v-if="selectedOt.status === 'approved'" variant="outline" class="bg-green-50 text-green-700 border-green-200 px-2 py-0.5 text-xs rounded-full">Approved</Badge>
                <Badge v-else-if="selectedOt.status === 'rejected'" variant="outline" class="bg-red-50 text-red-700 border-red-200 px-2 py-0.5 text-xs rounded-full">Rejected</Badge>
                <Badge v-else variant="outline" class="bg-yellow-50 text-yellow-700 border-yellow-200 px-2 py-0.5 text-xs rounded-full">Pending</Badge>
              </div>
            </div>
          </div>
          
          <div class="border-t border-gray-100 dark:border-zinc-800 my-4"></div>
          
          <div>
            <Label class="text-xs text-gray-500">Reason / Task</Label>
            <div class="text-sm mt-1.5 bg-gray-50 dark:bg-zinc-900/50 p-3 rounded-lg border border-gray-200 dark:border-zinc-800 text-gray-700 dark:text-gray-300">
              {{ selectedOt.reason }}
            </div>
          </div>
          
          <div v-if="selectedOt.admin_note">
            <Label class="text-xs text-gray-500">Admin Note</Label>
            <div class="text-sm mt-1.5 text-gray-700 dark:text-gray-300 italic">
              "{{ selectedOt.admin_note }}"
            </div>
          </div>
        </div>
        
        <DialogFooter v-if="selectedOt?.status === 'pending' && authStore.user?.role === 'admin'" class="pt-4 border-t border-gray-100 dark:border-zinc-800">
          <Button variant="outline" @click="directAction(selectedOt.id, 'rejected'); isDetailDialogOpen = false" class="text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700">
            Reject
          </Button>
          <Button @click="directAction(selectedOt.id, 'approved'); isDetailDialogOpen = false" class="bg-green-600 hover:bg-green-700 text-white">
            Approve
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Alert Dialog Konfirmasi Hapus -->
    <AlertDialog v-model:open="isDeleteDialogOpen">
      <AlertDialogContent class="bg-white dark:bg-zinc-950 border-gray-200 dark:border-zinc-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-gray-900 dark:text-zinc-100">Cancel Request?</AlertDialogTitle>
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

  </div>
</template>
