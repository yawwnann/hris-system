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
  {
    path: "/",
    name: "Dashboard",
    component: () => import("@/views/admin/Dashboard.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/employees",
    name: "Employees",
    component: () => import("@/views/admin/Employees.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/employees/:id",
    name: "EmployeeDetail",
    component: () => import("@/views/admin/EmployeeDetail.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/departments",
    name: "Departments",
    component: () => import("@/views/admin/Departments.vue"),
    meta: { requiresAuth: true },
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
    meta: { requiresAuth: true },
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
    name: "Settings",
    component: () => import("@/views/admin/Settings.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/reports",
    name: "Reports",
    component: () => import("@/views/admin/Reports.vue"),
    meta: { requiresAuth: true },
  },
];


if (import.meta.env.VITE_INTEGRATION_MODE === 'false') {
  const originalRoutes = [...routes];
  routes.length = 0; // clear existing routes
  
  const loginRoute = originalRoutes.find(r => r.path === '/login');
  if (loginRoute) routes.push(loginRoute);

  const forgotPasswordRoute = originalRoutes.find(r => r.path === '/forgot-password');
  if (forgotPasswordRoute) routes.push(forgotPasswordRoute);

  const addPrefixedRoutes = (prefix: string, role: string) => {
    originalRoutes.forEach((r: any) => {
      if (r.path === '/login' || r.path === '/forgot-password') return;
      const newPath = r.path === '/' ? `${prefix}/dashboard` : `${prefix}${r.path}`;
      const newRoute: any = {
        ...r,
        path: newPath,
        name: r.name ? `${role}-${String(r.name)}` : undefined
      };
      (routes as any[]).push(newRoute);
    });
  };

  addPrefixedRoutes('/admin', 'admin');
  addPrefixedRoutes('/employee', 'employee');

  // Redirect root to admin dashboard so it doesn't show a blank page
  (routes as any[]).push({ path: '/', redirect: '/admin/dashboard' });
}

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
    next();
  }
});

export default router;
