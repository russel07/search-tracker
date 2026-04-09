<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <el-card>
          <template #header>
            <div class="card-header">
              <h2>Search Filter Settings</h2>
            </div>
          </template>

          <el-form label-position="top" class="settings-form">
            <el-form-item label="Filter IPs (comma separated)">
              <el-input
                v-model="filterIps"
                type="textarea"
                :rows="4"
                placeholder="Example: 127.0.0.1, 192.168.1.20"
              />
            </el-form-item>

            <el-form-item label="Filter Words (comma separated)">
              <el-input
                v-model="filterWords"
                type="textarea"
                :rows="4"
                placeholder="Example: free, test, sample"
              />
            </el-form-item>

            <el-button type="primary" @click="saveSettings">Save Settings</el-button>
          </el-form>
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
    const filterIps = ref('');
    const filterWords = ref('');

    const fetchSettings = async () => {
      startLoading();
      try {
        const response = await get("settings");
        filterIps.value = response?.filter_ips || '';
        filterWords.value = response?.filter_words || '';
      } catch ( err ) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const saveSettings = async () => {
      startLoading();
      try {
        const response = await post('settings', {
          filter_ips: filterIps.value,
          filter_words: filterWords.value,
        });

        filterIps.value = response?.filter_ips || '';
        filterWords.value = response?.filter_words || '';
        success(response?.message || 'Settings saved successfully.');
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    onMounted(() => {
      fetchSettings();
    });

    return {
      filterIps,
      filterWords,
      fetchSettings,
      saveSettings,
    };
  },
};
</script>
<style scoped>
.dashboard-container {
  padding: 0 20px;
}

.settings-form {
  max-width: 760px;
}

.full-height {
  min-height: 100vh;
}

.card-header h2 {
  margin: 0;
}
</style>
