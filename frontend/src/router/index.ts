import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const routes = [
  {
    path: "/login",
    name: "Login",
    component: () => import("@/views/auth/Login.vue"),
    meta: { guest: true },
  },
  {
    path: "/forgot-password",
    name: "ForgotPassword",
    component: () => import("@/views/auth/ForgotPassword.vue"),
    meta: { guest: true },
  },
  // Root Dashboard (Dynamic Component based on role)
  {
    path: "/",
    name: "Dashboard",
    component: () => {
      const authStore = useAuthStore();
      if (authStore.user?.role === 'employee') {
        return import("@/views/employee/Dashboard.vue");
      }
      return import("@/views/admin/Dashboard.vue");
    },
    meta: { requiresAuth: true },
  },

  // Admin Routes
  {
    path: "/employees",
    name: "AdminEmployees",
    component: () => import("@/views/admin/Employees.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/employees/:id",
    name: "AdminEmployeeDetail",
    component: () => import("@/views/admin/EmployeeDetail.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/departments",
    name: "AdminDepartments",
    component: () => import("@/views/admin/Departments.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/attendance",
    name: "Attendance",
    component: () => import("@/views/admin/Attendance.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/calendar",
    name: "Calendar",
    component: () => import("@/views/admin/Calendar.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/leaves",
    name: "Leaves",
    component: () => import("@/views/admin/Leaves.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/overtime",
    name: "Overtime",
    component: () => import("@/views/admin/Overtime.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/announcements",
    name: "Announcements",
    component: () => import("@/views/admin/Announcements.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/settings",
    name: "AdminSettings",
    component: () => import("@/views/admin/Settings.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },


  // Employee Explicit Routes (alias/fallback)
  {
    path: "/employee/dashboard",
    name: "EmployeeDashboard",
    component: () => import("@/views/employee/Dashboard.vue"),
    meta: { requiresAuth: true, role: "employee" },
  },
  {
    path: "/employee/attendance",
    name: "EmployeeAttendance",
    component: () => import("@/views/employee/Attendance.vue"),
    meta: { requiresAuth: true, role: "employee" },
  },
  {
    path: "/employee/calendar",
    name: "EmployeeCalendar",
    component: () => import("@/views/admin/Calendar.vue"),
    meta: { requiresAuth: true, role: "employee" },
  },
  {
    path: "/employee/leaves",
    name: "EmployeeLeaves",
    component: () => import("@/views/employee/Leaves.vue"),
    meta: { requiresAuth: true, role: "employee" },
  },
  {
    path: "/employee/overtime",
    name: "EmployeeOvertime",
    component: () => import("@/views/employee/Overtime.vue"),
    meta: { requiresAuth: true, role: "employee" },
  },
  {
    path: "/employee/announcements",
    name: "EmployeeAnnouncements",
    component: () => import("@/views/admin/Announcements.vue"),
    meta: { requiresAuth: true, role: "employee" },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore();

  if (!authStore.user && authStore.token) {
    await authStore.fetchUser();
  }

  if (to.meta.requiresAuth && !authStore.user) {
    next("/login");
  } else if (to.meta.guest && authStore.user) {
    next("/");
  } else {
    if (authStore.user && to.meta.role && to.meta.role !== authStore.user.role) {
      next("/");
      return;
    }
    next();
  }
});

export default router;
