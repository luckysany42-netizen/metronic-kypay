<template>
  <div class="d-flex flex-column gap-7">

    <!-- Stats -->
    <div class="row g-5">
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-45px">
              <span class="symbol-label bg-light-primary"><i class="bi bi-people-fill text-primary fs-2"></i></span>
            </div>
            <div>
              <div class="text-muted fs-8">Total User</div>
              <div class="fw-bolder fs-3">{{ stats.total }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-45px">
              <span class="symbol-label bg-light-success"><i class="bi bi-person-check-fill text-success fs-2"></i></span>
            </div>
            <div>
              <div class="text-muted fs-8">User Aktif</div>
              <div class="fw-bolder fs-3 text-success">{{ stats.active }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-45px">
              <span class="symbol-label bg-light-danger"><i class="bi bi-person-x-fill text-danger fs-2"></i></span>
            </div>
            <div>
              <div class="text-muted fs-8">Disuspend</div>
              <div class="fw-bolder fs-3 text-danger">{{ stats.suspended }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body d-flex align-items-center gap-4 p-6">
            <div class="symbol symbol-45px">
              <span class="symbol-label bg-light-warning"><i class="bi bi-person-plus-fill text-warning fs-2"></i></span>
            </div>
            <div>
              <div class="text-muted fs-8">Daftar Hari Ini</div>
              <div class="fw-bolder fs-3 text-warning">{{ stats.new_today }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-header border-0 pt-6">
        <div class="card-title">
          <h3 class="fw-bold mb-0">Daftar User</h3>
        </div>
        <div class="card-toolbar gap-3">
          <select v-model="filterStatus" class="form-select form-select-solid form-select-sm w-auto" @change="fetchUsers">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="suspended">Disuspend</option>
          </select>
          <input v-model="search" @input="onSearch" type="text" class="form-control form-control-solid form-control-sm w-200px" placeholder="Cari nama / email / no HP..." />
        </div>
      </div>
      <div class="card-body pt-0">

        <div v-if="loading" class="text-center py-15">
          <span class="spinner-border text-primary"></span>
        </div>

        <div v-else-if="users.length === 0" class="text-center py-15">
          <i class="bi bi-inbox fs-3x text-muted mb-4 d-block"></i>
          <div class="text-muted">Tidak ada user ditemukan</div>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
            <thead>
              <tr class="fw-bold text-muted bg-light">
                <th class="ps-4">User</th>
                <th>No HP</th>
                <th>Wallet</th>
                <th>Saldo</th>
                <th>Status Wallet</th>
                <th>Bergabung</th>
                <th class="text-end pe-4">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-40px">
                      <img v-if="user.avatar" :src="avatarUrl(user.avatar)" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" />
                      <span v-else class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                    <div>
                      <div class="fw-bold fs-7">{{ user.name }}</div>
                      <div class="text-muted fs-8">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="fs-7">{{ user.phone ?? '-' }}</td>
                <td class="fs-8 text-muted">{{ user.wallet_number ?? '-' }}</td>
                <td class="fw-bold text-primary fs-7">{{ formatRupiah(user.balance) }}</td>
                <td>
                  <span class="badge" :class="user.wallet_status === 'active' ? 'badge-light-success' : 'badge-light-danger'">
                    {{ user.wallet_status === 'active' ? 'Aktif' : user.wallet_status === 'suspended' ? 'Disuspend' : '-' }}
                  </span>
                </td>
                <td class="fs-8 text-muted">{{ formatDate(user.created_at) }}</td>
                <td class="text-end pe-4">
                  <button class="btn btn-sm btn-icon btn-light-primary me-2" @click="openDetail(user)" title="Detail">
                    <i class="bi bi-eye fs-6"></i>
                  </button>
                  <button v-if="user.wallet_status === 'active'" class="btn btn-sm btn-icon btn-light-danger" @click="openSuspend(user)" title="Suspend">
                    <i class="bi bi-slash-circle fs-6"></i>
                  </button>
                  <button v-else-if="user.wallet_status === 'suspended'" class="btn btn-sm btn-icon btn-light-success" @click="openUnsuspend(user)" title="Aktifkan">
                    <i class="bi bi-check-circle fs-6"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4" v-if="meta.last_page > 1">
          <div class="text-muted fs-7">{{ meta.total }} user</div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light" :disabled="meta.current_page === 1" @click="changePage(meta.current_page - 1)">
              <i class="bi bi-chevron-left"></i>
            </button>
            <span class="btn btn-sm btn-primary">{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button class="btn btn-sm btn-light" :disabled="meta.current_page === meta.last_page" @click="changePage(meta.current_page + 1)">
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- ===== MODAL DETAIL USER ===== -->
    <div v-if="detailModal && selectedUser" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="detailModal = false">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detail User</h5>
            <button class="btn-close" @click="detailModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex align-items-center gap-5 mb-7 pb-6 border-bottom">
              <div class="symbol symbol-70px">
                <img v-if="selectedUser.avatar" :src="avatarUrl(selectedUser.avatar)" class="rounded-circle" style="width:70px;height:70px;object-fit:cover;" />
                <span v-else class="symbol-label bg-light-primary text-primary fw-bold fs-2">
                  {{ selectedUser.name?.charAt(0)?.toUpperCase() }}
                </span>
              </div>
              <div>
                <div class="fw-bolder fs-4">{{ selectedUser.name }}</div>
                <div class="text-muted fs-7">{{ selectedUser.email }}</div>
                <div class="text-muted fs-7">{{ selectedUser.phone ?? 'No HP belum diisi' }}</div>
              </div>
              <div class="ms-auto text-end">
                <div class="text-muted fs-8">Saldo</div>
                <div class="fw-bolder fs-2 text-primary">{{ formatRupiah(selectedUser.balance) }}</div>
                <span class="badge mt-1" :class="selectedUser.wallet_status === 'active' ? 'badge-light-success' : 'badge-light-danger'">
                  {{ selectedUser.wallet_status === 'active' ? '● Aktif' : '● Disuspend' }}
                </span>
              </div>
            </div>

            <div class="row g-5">
              <div class="col-md-6">
                <div class="bg-light rounded-2 p-5">
                  <div class="fw-bold text-muted fs-8 mb-3">INFO WALLET</div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fs-7">Nomor Wallet</span>
                    <span class="fw-bold fs-7">{{ selectedUser.wallet_number ?? '-' }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fs-7">PIN</span>
                    <span class="fw-bold fs-7" :class="selectedUser.pin_set ? 'text-success' : 'text-danger'">
                      {{ selectedUser.pin_set ? '✓ Sudah diatur' : '✗ Belum diatur' }}
                    </span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fs-7">Limit Transfer/Hari</span>
                    <span class="fw-bold fs-7">{{ formatRupiah(selectedUser.daily_transfer_limit) }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted fs-7">Limit Top Up/Hari</span>
                    <span class="fw-bold fs-7">{{ formatRupiah(selectedUser.daily_topup_limit) }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-light rounded-2 p-5">
                  <div class="fw-bold text-muted fs-8 mb-3">STATISTIK TRANSAKSI</div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fs-7">Total Transaksi</span>
                    <span class="fw-bold fs-7">{{ selectedUser.total_transactions ?? 0 }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fs-7">Total Top Up</span>
                    <span class="fw-bold fs-7 text-success">{{ formatRupiah(selectedUser.total_topup) }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted fs-7">Total Transfer Keluar</span>
                    <span class="fw-bold fs-7 text-danger">{{ formatRupiah(selectedUser.total_transfer_out) }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted fs-7">Total Pembayaran</span>
                    <span class="fw-bold fs-7 text-warning">{{ formatRupiah(selectedUser.total_payment) }}</span>
                  </div>
                </div>
              </div>

              <!-- Transaksi Terakhir -->
              <div class="col-12" v-if="selectedUser.recent_transactions?.length">
                <div class="fw-bold text-muted fs-8 mb-3">5 TRANSAKSI TERAKHIR</div>
                <div class="d-flex flex-column gap-2">
                  <div v-for="trx in selectedUser.recent_transactions" :key="trx.id"
                    class="d-flex align-items-center gap-3 bg-light rounded-2 p-3">
                    <div class="symbol symbol-30px">
                      <span class="symbol-label" :class="trx.is_credit ? 'bg-light-success' : 'bg-light-danger'">
                        <i class="bi fs-7" :class="trx.is_credit ? 'bi-arrow-down-left text-success' : 'bi-arrow-up-right text-danger'"></i>
                      </span>
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-bold fs-8">{{ trx.description }}</div>
                      <div class="text-muted fs-9">{{ formatDate(trx.created_at) }}</div>
                    </div>
                    <div class="fw-bold fs-7" :class="trx.is_credit ? 'text-success' : 'text-danger'">
                      {{ trx.is_credit ? '+' : '-' }}{{ formatRupiah(trx.amount) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="detailModal = false">Tutup</button>
            <button v-if="selectedUser.wallet_status === 'active'" class="btn btn-danger" @click="detailModal = false; openSuspend(selectedUser)">
              <i class="bi bi-slash-circle me-2"></i>Suspend User
            </button>
            <button v-else-if="selectedUser.wallet_status === 'suspended'" class="btn btn-success" @click="detailModal = false; openUnsuspend(selectedUser)">
              <i class="bi bi-check-circle me-2"></i>Aktifkan User
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL SUSPEND ===== -->
    <div v-if="suspendModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="suspendModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">Suspend User</h5>
            <button class="btn-close" @click="suspendModal = false"></button>
          </div>
          <div class="modal-body text-center">
            <i class="bi bi-slash-circle-fill text-danger mb-3 d-block" style="font-size:3rem;"></i>
            <div class="fw-bold fs-6 mb-1">{{ selectedUser?.name }}</div>
            <div class="text-muted fs-8 mb-4">Wallet akan dinonaktifkan. User tidak bisa bertransaksi.</div>
            <label class="form-label text-start d-block">Alasan Suspend</label>
            <textarea v-model="suspendReason" class="form-control form-control-solid" rows="3" placeholder="Wajib isi alasan..."></textarea>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="suspendModal = false">Batal</button>
            <button class="btn btn-danger" @click="confirmSuspend" :disabled="actionLoading || !suspendReason.trim()">
              <span v-if="actionLoading" class="spinner-border spinner-border-sm me-2"></span>
              Ya, Suspend
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MODAL UNSUSPEND ===== -->
    <div v-if="unsuspendModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5)" @click.self="unsuspendModal = false">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-success">Aktifkan Kembali</h5>
            <button class="btn-close" @click="unsuspendModal = false"></button>
          </div>
          <div class="modal-body text-center">
            <i class="bi bi-check-circle-fill text-success mb-3 d-block" style="font-size:3rem;"></i>
            <div class="fw-bold fs-6 mb-1">{{ selectedUser?.name }}</div>
            <div class="text-muted fs-8">Wallet akan diaktifkan kembali dan user bisa bertransaksi.</div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-light" @click="unsuspendModal = false">Batal</button>
            <button class="btn btn-success" @click="confirmUnsuspend" :disabled="actionLoading">
              <span v-if="actionLoading" class="spinner-border spinner-border-sm me-2"></span>
              Ya, Aktifkan
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";

const loading       = ref(true);
const actionLoading = ref(false);
const users         = ref<any[]>([]);
const stats         = ref({ total: 0, active: 0, suspended: 0, new_today: 0 });
const meta          = ref({ current_page: 1, last_page: 1, total: 0 });
const filterStatus  = ref("");
const search        = ref("");
const currentPage   = ref(1);
const selectedUser  = ref<any>(null);
const detailModal   = ref(false);
const suspendModal  = ref(false);
const unsuspendModal = ref(false);
const suspendReason = ref("");

let searchTimeout: any = null;

const formatRupiah = (val: any) => "Rp " + Number(val || 0).toLocaleString("id-ID");
const formatDate   = (d: string) =>
  new Date(d).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });

const avatarUrl = (path: string): string => {
  if (!path) return "";
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  const base = import.meta.env.VITE_APP_API_URL?.replace("/api", "") ?? "http://127.0.0.1:8000";
  return `${base}/storage/${path}`;
};

const fetchUsers = async () => {
  loading.value = true;
  try {
    const params: any = { page: currentPage.value };
    if (filterStatus.value) params.status = filterStatus.value;
    if (search.value)       params.search = search.value;
    const { data } = await ApiService.query("admin/users", { params });
    users.value  = data.data;
    stats.value  = data.stats;
    meta.value   = data.meta;
  } finally { loading.value = false; }
};

const onSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { currentPage.value = 1; fetchUsers(); }, 400);
};

const changePage  = (p: number) => { currentPage.value = p; fetchUsers(); };
const openDetail  = (u: any)    => { selectedUser.value = u; detailModal.value = true; };
const openSuspend = (u: any)    => { selectedUser.value = u; suspendReason.value = ""; suspendModal.value = true; };
const openUnsuspend = (u: any)  => { selectedUser.value = u; unsuspendModal.value = true; };

const confirmSuspend = async () => {
  actionLoading.value = true;
  try {
    await ApiService.post(`admin/users/${selectedUser.value.id}/suspend`, { reason: suspendReason.value });
    suspendModal.value = false;
    fetchUsers();
  } catch (e: any) {
    alert(e.response?.data?.message ?? "Gagal suspend.");
  } finally { actionLoading.value = false; }
};

const confirmUnsuspend = async () => {
  actionLoading.value = true;
  try {
    await ApiService.post(`admin/users/${selectedUser.value.id}/unsuspend`, {});
    unsuspendModal.value = false;
    fetchUsers();
  } catch (e: any) {
    alert(e.response?.data?.message ?? "Gagal mengaktifkan.");
  } finally { actionLoading.value = false; }
};

onMounted(fetchUsers);
</script>