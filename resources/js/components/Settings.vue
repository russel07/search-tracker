<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <h1>General Settings</h1>
        <el-card>
          <template #header>
            <div class="card-header">
              <span>General Settings Item</span>
            </div>
          </template>
        </el-card>
      </div>
    </el-main>
    <el-footer>
      <Footer />
    </el-footer>
  </el-container>
</template>

<script>
import { onMounted, ref } from "vue";
import { useAppHelper } from "../Composable/appHelper";
import AlertMessage from "../Composable/AlertMessage";
import { loader } from '../Composable/Loader';
import Footer from "./Footer.vue";
import Header from "./Header";
export default {
  name: "Settings",
  components: {
    Header,
    Footer,
  },
  setup() {
    const { get, post } = useAppHelper();
    const { success, error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const app_vars = window.wplms_cleanup_pro_app_vars;

    const fetchSettings = async () => {
      startLoading();
      try {
        const response = await get("admin-settings");

        if (response) {
          wp_pages.value = response.wp_pages;
        }
      } catch ( err ) {
        error('Error fetching settings:'+ err.response?.data.message);
      }
      stopLoading();
    };

    onMounted(() => {
      fetchSettings();
    });

    return {
      app_vars,
      fetchSettings,
    };
  },
};
</script>
<style scoped>
.dashboard-container {
  padding: 20px;
  max-width: 50%;
}

.full-height {
  min-height: 100vh;
}

.el-col {
  padding: 10px;
}
</style>
