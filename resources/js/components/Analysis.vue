<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <h1>Analysis</h1>
        <el-card>
          <template #header>
            <div class="card-header">
              <span>Current Overview</span>
            </div>
          </template>
          <div class="overview-stats">
            <el-row :gutter="20" class="stat-row">
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Courses:</strong> {{ stats.total_courses || 0 }}
                </div>
              </el-col>
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Units:</strong> {{ stats.total_units || 0 }}
                </div>
              </el-col>
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Quizzes:</strong> {{ stats.total_quizzes || 0 }}
                </div>
              </el-col>
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Questions:</strong> {{ stats.total_questions || 0 }}
                </div>
              </el-col>
            </el-row>
            <el-row :gutter="20" class="stat-row" style="margin-top:1rem;">
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Media Files:</strong> {{ stats.total_media || 0 }}
                </div>
              </el-col>
              <el-col :span="6">
                <div class="stat-item">
                  <strong>DB Size:</strong> {{ stats.db_size || '–' }}
                </div>
              </el-col>
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Upload Size:</strong> {{ stats.upload_size || '–' }}
                </div>
              </el-col>
              <el-col :span="6">
                <div class="stat-item">
                  <strong>Unused Data:</strong> {{ stats.potential_cleanup || '–' }}
                </div>
              </el-col>
            </el-row>
            <div class="overview-actions" style="margin-top:1.5rem;">
              <el-button type="primary" @click="onRunScan">Run Full Scan</el-button>
              <el-button @click="onViewReport">View Last Report</el-button>
            </div>
          </div>
        </el-card>
      </div>
    </el-main>
    <el-footer>
      <Footer />
    </el-footer>
  </el-container>
</template>

<script>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAppHelper } from '../Composable/appHelper';
import AlertMessage from "../Composable/AlertMessage";
import { loader } from '../Composable/Loader';
import Footer from "./Footer.vue";
import Header from "./Header";

export default {
  name: 'Analysis',
  components: {
    Header,
    Footer,
  },
  setup() {
    const { get } = useAppHelper();
    const { error } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const route = useRoute();
    const app_vars = window.wplms_cleanup_pro_app_vars;
    const stats = ref({});

    const fetchStats = async () => {
      startLoading();
      try {
        const response = await get('dashboard-overview');
        stats.value = response;
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const onRunScan = async () => {
      startLoading();
      try {
        const response = await get('run-full-scan');
        stats.value = response.stats || stats.value;
        AlertMessage().success('Full scan completed');
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const onViewReport = async () => {
      startLoading();
      try {
        const response = await get('last-report');
        if (response.report) {
          stats.value = response.report;
          AlertMessage().info('Loaded last report');
        }
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    onMounted(() => {
      fetchStats();
    });

    return {
      stats,
      onRunScan,
      onViewReport,
    };
  },
};
</script>
