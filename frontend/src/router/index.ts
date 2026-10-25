import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { usersMock } from "@/mock/data/dashboard";

const routes = [
  // Auth Routes
  {
    path: "/login",
    name: "Login",
    component: () => import("@/views/auth/Login.vue"),
    meta: { guest: true },
  },
  {
    path: "/register",
    name: "Register",
    component: () => import("@/views/auth/Register.vue"),
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

  // Admin Routes (Directly accessible via /admin/* or canonical paths)
  {
    path: "/admin/dashboard",
    name: "AdminDashboard",
    component: () => import("@/views/admin/Dashboard.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/employees",
    alias: ["/admin/employees"],
    name: "AdminEmployees",
    component: () => import("@/views/admin/Employees.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/employees/:id",
    alias: ["/admin/employees/:id"],
    name: "AdminEmployeeDetail",
    component: () => import("@/views/admin/EmployeeDetail.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/departments",
    alias: ["/admin/departments"],
    name: "AdminDepartments",
    component: () => import("@/views/admin/Departments.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/attendance",
    alias: ["/admin/attendance"],
    name: "Attendance",
    component: () => import("@/views/admin/Attendance.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/calendar",
    alias: ["/admin/calendar"],
    name: "Calendar",
    component: () => import("@/views/admin/Calendar.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/leaves",
    alias: ["/admin/leaves"],
    name: "Leaves",
    component: () => import("@/views/admin/Leaves.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/overtime",
    alias: ["/admin/overtime"],
    name: "Overtime",
    component: () => import("@/views/admin/Overtime.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/announcements",
    alias: ["/admin/announcements"],
    name: "Announcements",
    component: () => import("@/views/admin/Announcements.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
  {
    path: "/settings",
    alias: ["/admin/settings"],
    name: "AdminSettings",
    component: () => import("@/views/admin/Settings.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },

  // Employee Explicit Routes
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
  const isMockMode = import.meta.env.VITE_INTEGRATION_MODE === 'false';

  // 1. In Mock Mode (VITE_INTEGRATION_MODE=false):
  // Allow direct access to ANY route without authentication barrier,
  // automatically injecting the appropriate mock user (admin or employee).
  if (isMockMode) {
    if (to.meta.guest) {
      next();
      return;
    }

    const isEmployeeRoute = to.path.startsWith('/employee') || to.meta.role === 'employee';
    const activeRole = isEmployeeRoute ? 'employee' : 'admin';
    const mockUser = usersMock.data.find(u => u.role === activeRole) || usersMock.data[0];

    // Automatically align active user state with the route being visited
    if (!authStore.user || authStore.user.role !== activeRole) {
      authStore.user = { ...mockUser };
      authStore.token = `mock-token-${activeRole}`;
      localStorage.setItem('access_token', `mock-token-${activeRole}`);
    }

    next();
    return;
  }

  // 2. Real Integration Mode:
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
