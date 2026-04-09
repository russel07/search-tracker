<template>
  <el-header>
    <div class="header-content">
      <el-menu :default-active="activeIndex" class="el-menu-demo" mode="horizontal" @select="handleSelect"
        :router="true">
        <el-menu-item index="analytics" :to="{ path: '/analytics' }">
          <el-icon><DataAnalysis /></el-icon>Analytics
        </el-menu-item>
        <el-menu-item index="settings" :to="{ path: '/settings' }">
          <el-icon><Setting /></el-icon>Settings
        </el-menu-item>
      </el-menu>
      <div class="h-6" />
    </div>
  </el-header>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Setting, DataAnalysis } from '@element-plus/icons-vue';

export default {
  name: 'Header',
  components: { Setting, DataAnalysis },
  setup() {
    const route = useRoute(); // Get the current route
    const router = useRouter();
    const activeIndex = ref('analytics');

    // Set the active menu item based on the current route path
    const setActiveMenu = (path) => {
      if (path.includes('/settings')) {
        activeIndex.value = 'settings';
      } else if (path.includes('/analytics')) {
        activeIndex.value = 'analytics';
      }else {
        activeIndex.value = 'analytics';
      }
    };

    // Call setActiveMenu on component mount
    onMounted(() => {
      setActiveMenu(route.path);
    });

    // Watch the route for changes and update the active menu item accordingly
    watch(route, (newRoute) => {
      setActiveMenu(newRoute.path);
    });

    // Handle manual tab selection from the menu
    const handleSelect = (key) => {
      activeIndex.value = key;
      // Navigate to the selected route when a menu item is clicked
      router.push(`/${key}`);
    };

    return {
      activeIndex,
      handleSelect
    };
  }
};
</script>
