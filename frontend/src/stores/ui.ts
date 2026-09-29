import { defineStore } from "pinia";
import { ref } from "vue";

export const useUiStore = defineStore("ui", () => {
  const mobileMenuOpen = ref(false);

  const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
  };

  const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
  };

  return { mobileMenuOpen, toggleMobileMenu, closeMobileMenu };
});
