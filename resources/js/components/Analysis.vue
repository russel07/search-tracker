<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <el-card>
          <template #header>
            <div class="card-header with-search">
              <h2>All Search terms</h2>
              <el-input
                  v-model="search"
                  placeholder="Search terms..."
                  style="width: 300px;"
                  clearable
              >
                  <template #prefix>
                      <el-icon><Search /></el-icon>
                  </template>
              </el-input>
            </div>
          </template>
          <div class="overview-stats">
            <el-table :data="filteredData" style="width: 100%">
              <el-table-column prop="search_term" label="Search Term"/>
              <el-table-column prop="search_course" label="Course" />
              <el-table-column prop="ip_address" label="IP Address" />
              <el-table-column prop="created_at" label="Last Searched At">
                <template #default="{ row }">
                  {{ new Date(row.created_at).toLocaleString() }}
                </template>
              </el-table-column>
            </el-table>
          </div>
          <el-pagination @current-change="handlePageChange" @size-change="handleSizeChange"
              :current-page="currentPage" :page-size="pageSize" :background="true" :total="totalItems"
              layout="total, sizes, prev, pager, next" />
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
    const currentPage = ref(1);
    const pageSize = ref(10);
    const totalItems = ref(10);
    const data = ref([]);
    const search = ref('');
    // Computed property for filtered jobs
    const filteredData = computed(() => {
        if (! search.value ) return data.value;
        return data.value.filter(term => 
            term.search_terms.toLowerCase().includes(search.value.toLowerCase())
        );
    });

    const fetchSearchData = async () => {
      startLoading();
      try {
        const response = await get(`get-data?page=${currentPage.value}&per_page=${pageSize.value}&search=${search.value}`);
        data.value = response.data;
        currentPage.value = response.current_page;
        pageSize.value = response.per_page;
        totalItems.value = response.total_items;
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const handlePageChange = (newPage) => {
        currentPage.value = newPage;
        fetchSearchData();
    };

    const handleSizeChange = (newPageSize) => {
        pageSize.value = newPageSize;
        if (pageSize.value > totalItems.value) {
            currentPage.value = 1;
        }
        fetchSearchData();
    };

    onMounted(() => {
      fetchSearchData();
    });

    return {
      search,
      currentPage,
      pageSize,
      totalItems,
      data,
      filteredData,
      handlePageChange,
      handleSizeChange,
    };
  },
};
</script>

<style scoped>
.dashboard-container {
  padding: 0 20px;
}

.with-search {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
