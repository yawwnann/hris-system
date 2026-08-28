import { defineStore } from "pinia";
import api from "@/lib/axios";
import { ref } from "vue";
import router from "@/router";

export const useAuthStore = defineStore("auth", () => {
  const user = ref<any>(null);
  const token = ref(localStorage.getItem("access_token") || "");
  

  const login = async (credentials: any) => {
    const { data } = await api.post("/login", credentials);
    token.value = data.access_token;
    user.value = data.user;
    localStorage.setItem("access_token", data.access_token);
  };

  const fetchUser = async () => {
    if (!token.value) return null;
    try {
      const { data } = await api.get("/me");
      user.value = data;
      return data;
    } catch (e) {
      logout();
    }
  };

  const logout = async () => {
    if (token.value) {
      try {
        await api.post("/logout");
      } catch (e) {}
    }
    user.value = null;
    token.value = "";
    localStorage.removeItem("access_token");
    if (router) router.push("/login");
  };

  return { user, token, login, fetchUser, logout };
});
