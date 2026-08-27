<script setup lang="ts">
import { ref, onMounted } from "vue";
import {
  Search,
  Download,
  ChevronDown,
  Eye,
  MoreVertical,
} from "lucide-vue-next";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Checkbox } from "@/components/ui/checkbox";
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useRouter } from "vue-router";
import api from "@/lib/axios";

const router = useRouter();
const employees = ref<any[]>([]);
const loading = ref(true);

const fetchEmployees = async () => {
  try {
    const { data } = await api.get("/users");
    // Asumsikan data dari API berbentuk paginated object
    employees.value = data.data;
  } catch (error) {
    console.error("Failed to fetch employees", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchEmployees();
});
</script>

<template>
  <Card class="border-gray-200 dark:border-zinc-800 border-2">
    <CardHeader class="pb-4">
      <CardTitle class="text-sm font-semibold text-gray-800 dark:text-zinc-200"
        >All Employees</CardTitle
      >
    </CardHeader>
    <CardContent>
      <div class="flex items-center justify-between mb-4">
        <div class="flex space-x-3">
          <div class="relative w-64">
            <Search
              class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500"
            />
            <Input
              class="pl-9 h-9 border-gray-200 dark:border-zinc-800 text-xs"
              placeholder="Search employee..."
            />
          </div>
          <Button
            variant="outline"
            class="h-9 text-xs text-gray-600 dark:text-zinc-300 border-gray-200 dark:border-zinc-800"
          >
            All Status <ChevronDown class="w-3 h-3 ml-2" />
          </Button>
          <Button
            variant="outline"
            class="h-9 text-xs text-gray-600 dark:text-zinc-300 border-gray-200 dark:border-zinc-800"
          >
            All Role <ChevronDown class="w-3 h-3 ml-2" />
          </Button>
        </div>
        <Button
          variant="outline"
          class="h-9 text-xs text-gray-600 dark:text-zinc-300 border-gray-200 dark:border-zinc-800"
        >
          <Download class="w-3 h-3 mr-2" /> Export
        </Button>
      </div>

      <div class="border rounded-lg overflow-hidden relative min-h-[200px]">
        <div
          v-if="loading"
          class="absolute inset-0 bg-white dark:bg-zinc-950/80 flex items-center justify-center z-10"
        >
          <span class="text-sm text-gray-500 dark:text-zinc-400">Loading data...</span>
        </div>
        <Table>
          <TableHeader class="bg-gray-50 dark:bg-zinc-900/50">
            <TableRow>
              <TableHead class="w-12 text-center"><Checkbox /></TableHead>
              <TableHead class="w-12 text-center text-xs font-semibold text-gray-500 dark:text-zinc-400">No.</TableHead>
              <TableHead class="text-xs font-semibold text-gray-500 dark:text-zinc-400"
                >Employee ID</TableHead
              >
              <TableHead class="text-xs font-semibold text-gray-500 dark:text-zinc-400"
                >Employee name</TableHead
              >
              <TableHead class="text-xs font-semibold text-gray-500 dark:text-zinc-400"
                >Email</TableHead
              >
              <TableHead class="text-xs font-semibold text-gray-500 dark:text-zinc-400"
                >Peran</TableHead
              >
              <TableHead class="text-xs font-semibold text-gray-500 dark:text-zinc-400"
                >Status</TableHead
              >
              <TableHead class="text-right text-xs font-semibold text-gray-500 dark:text-zinc-400"
                >Aksi</TableHead
              >
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="(emp, index) in employees.slice(0, 5)"
              :key="emp.id"
              class="hover:bg-gray-50 dark:bg-zinc-900/50"
            >
              <TableCell class="text-center"><Checkbox /></TableCell>
              <TableCell class="text-center text-xs text-gray-500">{{ index + 1 }}</TableCell>
              <TableCell class="font-medium text-xs">{{
                emp.nik || emp.id
              }}</TableCell>
              <TableCell>
                <div class="flex items-center space-x-3">
                  <Avatar class="w-6 h-6 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-800">
                    <AvatarFallback class="text-[10px]">{{
                      emp.name.charAt(0)
                    }}</AvatarFallback>
                  </Avatar>
                  <span class="text-xs font-medium text-gray-900 dark:text-zinc-100">{{
                    emp.name
                  }}</span>
                </div>
              </TableCell>
              <TableCell class="text-xs text-gray-500 dark:text-zinc-400">{{
                emp.email
              }}</TableCell>
              <TableCell class="text-xs text-gray-600 dark:text-zinc-300">{{
                emp.position?.name || emp.role
              }}</TableCell>
              <TableCell>
                <Badge
                  variant="outline"
                  :class="
                    emp.status === 'active'
                      ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-200'
                      : 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 border-orange-200'
                  "
                  class="text-[10px] gap-1"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="
                      emp.status === 'active' ? 'bg-green-50 dark:bg-green-900/200' : 'bg-orange-50 dark:bg-orange-900/200'
                    "
                  ></span>
                  {{ emp.status === "active" ? "Active" : "Inactive" }}
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex items-center justify-end space-x-2">
                  <Button
                    @click="router.push(`/employees/${emp.id}`)"
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7 text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:text-zinc-300"
                    ><Eye class="w-4 h-4"
                  /></Button>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:text-zinc-300 border border-gray-200 dark:border-zinc-800"
                      >
                        <MoreVertical class="w-3 h-3" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-40 bg-white dark:bg-zinc-900 border-gray-200 dark:border-zinc-800">
                      <DropdownMenuItem @click="router.push(`/employees/${emp.id}`)" class="cursor-pointer text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                        <Eye class="mr-2 h-4 w-4" /> View Details
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="router.push('/employees')" class="cursor-pointer text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20">
                        Manage
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="!loading && employees.length === 0">
              <TableCell colspan="8" class="text-center py-10 text-gray-500 dark:text-zinc-400"
                >No employees found.</TableCell
              >
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-gray-500 dark:text-zinc-400">
          Showing {{ Math.min(5, employees.length) }} of {{ employees.length }} employees
        </p>
        <div class="flex space-x-2">
          <router-link to="/employees">
            <Button variant="outline" size="sm" class="h-8 text-xs font-medium"
              >Lihat Semua Karyawan</Button
            >
          </router-link>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
