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
    path: "/",
    name: "Dashboard",
    component: () => import("@/views/Dashboard.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/employees",
    name: "Employees",
    component: () => import("@/views/Employees.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/departments",
    name: "Departments",
    component: () => import("@/views/Departments.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/attendance",
    name: "Attendance",
    component: () => import("@/views/Attendance.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/leaves",
    name: "Leaves",
    component: () => import("@/views/Leaves.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/overtime",
    name: "Overtime",
    component: () => import("@/views/Overtime.vue"),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
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
