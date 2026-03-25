<template>
  <el-container class="full-height">
    <el-main class="main-center">
      <Header />
      <div class="dashboard-container">
        <el-card>
          <template #header>
            <div class="card-header with-search">
              <h2>All Search terms</h2>
            </div>
          </template>

          <div class="actions-row">
            <el-button
              v-if="selectedRows.length"
              type="success"
              @click="exportSelected"
            >
              Export Selected
            </el-button>
            <el-button
              v-else
              type="success"
              @click="exportAll"
            >
              Export All
            </el-button>
            <el-button
              v-if="selectedRows.length"
              type="danger"
              @click="deleteSelected"
            >
              Delete Selected
            </el-button>
            <el-button
              v-else
              type="danger"
              @click="deleteAll"
            >
              Delete All
            </el-button>
            <div class="date-range-wrap">
              <span class="date-range-label">Date Range</span>
              <el-date-picker
                v-model="dateRange"
                type="daterange"
                unlink-panels
                range-separator="To"
                start-placeholder="Start date"
                end-placeholder="End date"
                clearable
              />
            </div>
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

          <div class="overview-stats">
            <el-table
              :data="filteredData"
              style="width: 100%"
              @selection-change="handleSelectionChange"
            >
              <el-table-column type="selection" width="55" />
              <el-table-column prop="search_term" label="Search Term"/>
              <el-table-column prop="search_course" label="Course" />
              <el-table-column prop="created_at" label="Last Searched At">
                <template #default="{ row }">
                  {{ new Date(row.created_at).toLocaleString() }}
                </template>
              </el-table-column>
              <el-table-column prop="ip_address" label="IP Address" />
            </el-table>
          </div>

          <template #footer> 
            <div class="card-footer footer-with-buttons">  
              <el-pagination
                @current-change="handlePageChange"
                @size-change="handleSizeChange"
                :current-page="currentPage"
                :page-size="pageSize"
                :background="true"
                :total="totalItems"
                layout="total, sizes, prev, pager, next"
              />
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
import { onMounted, ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAppHelper } from '../Composable/appHelper';
import AlertMessage from "../Composable/AlertMessage";
import { loader } from '../Composable/Loader';
import Footer from "./Footer.vue";
import Header from "./Header";
import { Search } from '@element-plus/icons-vue';

export default {
  name: 'Analysis',
  components: { Header, Footer, Search },
  setup() {
    const { get, post } = useAppHelper(); // Add post for delete
    const { error, success } = AlertMessage();
    const { startLoading, stopLoading } = loader();
    const route = useRoute();
    const router = useRouter();

    const currentPage = ref(1);
    const pageSize = ref(10);
    const totalItems = ref(10);
    const data = ref([]);
    const search = ref('');
    const dateRange = ref(null);

    const selectedRows = ref([]); // track selected rows

    const filteredData = computed(() => {
      let rows = data.value;

      if (search.value) {
        rows = rows.filter(term =>
          term.search_term.toLowerCase().includes(search.value.toLowerCase())
        );
      }

      if (dateRange.value && dateRange.value[0] && dateRange.value[1]) {
        const start = new Date(dateRange.value[0]);
        const end = new Date(dateRange.value[1]);
        end.setHours(23, 59, 59, 999);
        rows = rows.filter(term => {
          const d = new Date(term.created_at);
          return d >= start && d <= end;
        });
      }

      return rows;
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

    const handleSelectionChange = (rows) => {
      selectedRows.value = rows;
    };

    const getFileNameFromDisposition = (headerValue, fallback) => {
      if (!headerValue) {
        return fallback;
      }

      const match = headerValue.match(/filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i);
      const rawFileName = match?.[1] || match?.[2];

      if (!rawFileName) {
        return fallback;
      }

      return decodeURIComponent(rawFileName).trim();
    };

    const downloadCsv = async (route, payload = {}, fallbackFileName = 'search_terms.csv') => {
      const url = `${window.wplms_cleanup_pro_app_vars.rest_info.rest_url}/${route}`;
      const response = await axios({
        url,
        method: 'post',
        data: payload,
        responseType: 'blob',
        headers: {
          'X-WP-Nonce': window.wplms_cleanup_pro_app_vars.rest_info.nonce,
        },
      });

      const disposition = response.headers?.['content-disposition'];
      const fileName = getFileNameFromDisposition(disposition, fallbackFileName);
      const blob = new Blob([response.data], { type: response.data.type || 'text/csv;charset=utf-8;' });
      const blobUrl = URL.createObjectURL(blob);
      const link = document.createElement('a');

      link.href = blobUrl;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(blobUrl);
    };

    const exportSelected = async () => {
      if (!selectedRows.value.length) {
        error('Please select at least one row to export.');
        return;
      }

      try {
        startLoading();
        const ids = selectedRows.value.map((r) => r.id);
        await downloadCsv('export-search-terms-selected', { ids }, 'selected_search_terms.csv');
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const formatLocalDate = (date) => {
      if (!date) return null;
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    const exportAll = async () => {
      try {
        startLoading();
        let formattedDateRange = null;
        if (dateRange.value && dateRange.value[0] && dateRange.value[1]) {
          formattedDateRange = [
            formatLocalDate(dateRange.value[0]),
            formatLocalDate(dateRange.value[1]),
          ];
        }
        await downloadCsv('export-search-terms', {
          search: search.value,
          date_range: formattedDateRange,
        }, 'search_terms.csv');
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const deleteSelected = async () => {
      if (!selectedRows.value.length) return;
      const ids = selectedRows.value.map(r => r.id); // assuming each row has `id`
      try {
        startLoading();
        await post('delete-search-terms', { ids });
        success('Selected search terms deleted successfully!');
        selectedRows.value = [];
        fetchSearchData();
      } catch (err) {
        error(err.response?.data?.message || err.message);
      }
      stopLoading();
    };

    const handlePageChange = (newPage) => {
      router.push({
        query: {
          ...route.query,
          page: String(newPage),
          per_page: String(pageSize.value),
        },
      });
    };

    const handleSizeChange = (newPageSize) => {
      router.push({
        query: {
          ...route.query,
          page: '1',
          per_page: String(newPageSize),
        },
      });
    };

    watch(
      () => route.query,
      (query) => {
        const parsedPage = Number.parseInt(query.page, 10);
        const parsedPerPage = Number.parseInt(query.per_page, 10);

        currentPage.value = Number.isFinite(parsedPage) && parsedPage > 0 ? parsedPage : 1;
        pageSize.value = Number.isFinite(parsedPerPage) && parsedPerPage > 0 ? parsedPerPage : 10;
        fetchSearchData();
      },
      { immediate: true }
    );

    onMounted(() => {
      if (!route.query.page || !route.query.per_page) {
        router.replace({
          query: {
            ...route.query,
            page: String(currentPage.value),
            per_page: String(pageSize.value),
          },
        });
      }
    });

    return {
      search, dateRange, currentPage, pageSize, totalItems, data, filteredData,
      handlePageChange, handleSizeChange,
      selectedRows, handleSelectionChange, deleteSelected,
      exportSelected, exportAll
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
.actions-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 15px;
}
.actions-row .el-button {
  margin: 0;
}
.footer-with-buttons{
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  padding-top: 10px;
}
.date-range-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  max-width: 340px;
  min-width: 0;
  flex: 1 1 280px;
}
.date-range-wrap .el-date-editor {
  max-width: 100%;
  min-width: 0;
  flex: 1;
}
.date-range-label {
  white-space: nowrap;
  font-size: 13px;
  color: #606266;
  font-weight: 500;
}
</style>