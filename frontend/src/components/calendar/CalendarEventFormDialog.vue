<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  DropdownMenu,
  DropdownMenuItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Check } from "lucide-vue-next";

const props = defineProps<{
  open: boolean;
  editId: number | null;
  eventForm: any;
  categoriesList: any[];
  divisionsList: any[];
  usersList: any[];
  isSubmitting: boolean;
}>();

const emit = defineEmits(['update:open', 'save']);

const toggleUser = (id: any) => {
  const index = props.eventForm.user_ids.findIndex((uId: any) => uId == id);
  if (index > -1) props.eventForm.user_ids.splice(index, 1);
  else props.eventForm.user_ids.push(id);
};

const toggleDivision = (id: any) => {
  const index = props.eventForm.division_ids.findIndex((dId: any) => dId == id);
  if (index > -1) props.eventForm.division_ids.splice(index, 1);
  else props.eventForm.division_ids.push(id);
};
</script>

<template>
  <Dialog :open="open" @update:open="val => emit('update:open', val)">
    <DialogContent class="sm:max-w-[600px] bg-white dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 p-0 overflow-hidden">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle class="text-xl text-gray-900 dark:text-zinc-100">{{ editId ? 'Edit Event' : 'Add Event' }}</DialogTitle>
        <DialogDescription>Fill in the calendar event details below.</DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="emit('save')" class="p-6">
        <ScrollArea class="h-[60vh] pr-4 -mr-4">
          <div class="space-y-4">
            <!-- Title -->
            <div>
              <Label>Event Name</Label>
              <Input v-model="eventForm.title" placeholder="E.g., Team Meeting, National Holiday" required class="mt-1" />
            </div>
            
            <!-- Category -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label>Category</Label>
                <Select v-model="eventForm.category">
                  <SelectTrigger class="mt-1">
                    <SelectValue placeholder="Select Category" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="cat in categoriesList" :key="cat.value" :value="cat.value">
                      <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: cat.color }"></div>
                        {{ cat.label }}
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Custom Color (Optional)</Label>
                <div class="flex mt-1 space-x-2">
                  <Input type="color" v-model="eventForm.color" class="w-12 h-9 p-1" />
                  <Input v-model="eventForm.color" class="flex-1" />
                </div>
              </div>
            </div>

            <!-- Options -->
            <div class="flex flex-wrap gap-6 py-2">
              <div class="flex items-center space-x-2">
                <Checkbox id="is_all_day" :checked="eventForm.is_all_day" @update:checked="eventForm.is_all_day = !!$event" />
                <label for="is_all_day" class="text-sm font-medium leading-none cursor-pointer">All Day</label>
              </div>
              <div class="flex items-center space-x-2">
                <Checkbox id="is_working_day" :checked="eventForm.is_working_day" @update:checked="eventForm.is_working_day = !!$event" />
                <label for="is_working_day" class="text-sm font-medium leading-none cursor-pointer">Count as Workday</label>
              </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label>Start Date</Label>
                <Input type="datetime-local" v-model="eventForm.start_datetime" required class="mt-1" />
              </div>
              <div>
                <Label>End Date</Label>
                <Input type="datetime-local" v-model="eventForm.end_datetime" required class="mt-1" />
              </div>
            </div>

            <!-- Location -->
            <div>
              <Label>Location</Label>
              <Input v-model="eventForm.location" placeholder="Meeting Room A, Zoom, etc." class="mt-1" />
            </div>

            <!-- Description -->
            <div>
              <Label>Description</Label>
              <Textarea v-model="eventForm.description" rows="3" placeholder="Additional event details..." class="mt-1" />
            </div>

            <!-- Participants -->
            <div class="border-t border-gray-100 dark:border-zinc-800 pt-4 mt-2">
              <Label class="text-sm font-semibold mb-3 block">Participants (Optional)</Label>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-xs text-gray-500 mb-2 block">Select Division</Label>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="outline" class="w-full justify-between font-normal text-left">
                        <span>{{ eventForm.division_ids.length > 0 ? `${eventForm.division_ids.length} Divisions Selected` : 'Select Division...' }}</span>
                        <span class="opacity-50 text-xs">▼</span>
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-[250px] max-h-[200px] overflow-y-auto">
                      <DropdownMenuItem
                        v-for="div in divisionsList"
                        :key="div.id"
                        @select.prevent="toggleDivision(div.id)"
                        class="flex items-center justify-between cursor-pointer"
                      >
                        <span>{{ div.name }}</span>
                        <Check v-if="eventForm.division_ids.some((dId: any) => dId == div.id)" class="w-4 h-4 text-indigo-600" />
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
                <div>
                  <Label class="text-xs text-gray-500 mb-2 block">Select Employee</Label>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="outline" class="w-full justify-between font-normal text-left">
                        <span>{{ eventForm.user_ids.length > 0 ? `${eventForm.user_ids.length} Employees Selected` : 'Select Employee...' }}</span>
                        <span class="opacity-50 text-xs">▼</span>
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-[250px] max-h-[200px] overflow-y-auto">
                      <DropdownMenuItem
                        v-for="usr in usersList"
                        :key="usr.id"
                        @select.prevent="toggleUser(usr.id)"
                        class="flex items-center justify-between cursor-pointer"
                      >
                        <span>{{ usr.name }}</span>
                        <Check v-if="eventForm.user_ids.some((uId: any) => uId == usr.id)" class="w-4 h-4 text-indigo-600" />
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
              </div>
            </div>
          </div>
        </ScrollArea>
        
        <div class="flex justify-end gap-3 pt-6 mt-2 border-t border-gray-100 dark:border-zinc-800">
          <Button type="button" variant="outline" @click="emit('update:open', false)">Cancel</Button>
          <Button type="submit" :disabled="isSubmitting" class="bg-indigo-600 text-white hover:bg-indigo-700">
            {{ isSubmitting ? 'Saving...' : 'Save Event' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>
