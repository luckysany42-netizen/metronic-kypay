import { ref } from "vue";
import { defineStore } from "pinia";
import ApiService from "@/core/services/ApiService";
import JwtService from "@/core/services/JwtService";

export interface User {
  name: string;
  email: string;
  password: string;
  api_token: string;
  role: string;
  phone?: string;
  avatar?: string;
  bio?: string;
  job_title?: string;
  company?: string;
}

export const useAuthStore = defineStore("auth", () => {
  const errors = ref({});
  const user = ref<User>({} as User);
  const isAuthenticated = ref(!!JwtService.getToken());

  function setAuth(authUser: User) {
    isAuthenticated.value = true;
    user.value = authUser;
    errors.value = {};
    JwtService.saveToken(user.value.api_token);
  }

  function setError(error: any) {
    errors.value = { ...error };
  }

  function purgeAuth() {
    isAuthenticated.value = false;
    user.value = {} as User;
    errors.value = [];
    JwtService.destroyToken();
  }

  function login(credentials: { phone: string; password: string }) {
    return ApiService.post("login", credentials)
      .then(({ data }) => {
        console.log('✅ Login success:', data);
        setAuth(data);
      })
      .catch(({ response }) => {
        console.error('❌ Login error:', response?.status, response?.data);
        const errorData = response?.data?.errors || response?.data || { login: 'Login gagal' };
        setError(errorData);
      });
  }

  function adminLogin(credentials: { email: string; password: string }) {
    return ApiService.post("admin/login", credentials)
      .then(({ data }) => {
        console.log('✅ Admin login success:', data);
        setAuth(data);
      })
      .catch(({ response }) => {
        console.error('❌ Admin login error:', response?.status, response?.data);
        const errorData = response?.data?.errors || response?.data || { login: 'Login gagal' };
        setError(errorData);
      });
  }

  function logout() { purgeAuth(); }

  function register(credentials: User) {
    return ApiService.post("register", credentials)
      .then(({ data }) => { setAuth(data); })
      .catch(({ response }) => { setError(response.data.errors); });
  }

  function adminRegister(credentials: User) {
    return ApiService.post("admin/register", credentials)
      .then(({ data }) => { setAuth(data); })
      .catch(({ response }) => { setError(response.data.errors); });
  }

  function forgotPassword(credentials: { email: string }) {
    return ApiService.post("forgot-password", credentials)
      .then(() => { errors.value = {}; })
      .catch(({ response }) => { setError(response.data.errors); });
  }

  function resetPassword(credentials: {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    return ApiService.post("reset-password", credentials)
      .then(() => { errors.value = {}; })
      .catch(({ response }) => { setError(response.data.errors); });
  }

  // ==========================================
  // CHANGE PASSWORD (user sedang login)
  // ==========================================
  function changePassword(credentials: {
    current_password: string;
    password: string;
    password_confirmation: string;
  }) {
    return ApiService.post("change-password", credentials)
      .then(() => { errors.value = {}; })
      .catch(({ response }) => { setError(response.data.errors); });
  }

  function updateProfile(profileData: Partial<User>) {
    return ApiService.post("profile/update", profileData)
      .then(({ data }) => { user.value = data; errors.value = {}; })
      .catch(({ response }) => { setError(response.data.errors); });
  }

  function uploadAvatar(file: File) {
    const formData = new FormData();
    formData.append("avatar", file);
    return ApiService.post("profile/avatar", formData)
      .then(({ data }) => { user.value = data.user; errors.value = {}; return data; })
      .catch(({ response }) => { setError(response.data.errors); throw response; });
  }

async function verifyAuth() {
    const token = JwtService.getToken();
    console.log('🔍 verifyAuth() - Token exists?', !!token);
    
    if (token) {
      ApiService.setHeader();
      console.log('🔐 Verifying token:', token.substring(0, 10) + '...');
      
      return ApiService.post("verify_token", { api_token: token })
        .then(({ data }) => { 
          console.log('✅ Token verified successfully');
          console.log('📦 Response data:', data);
          
          // Handle both formats: direct user object or wrapped { success, user }
          const userData = data.user || data;
          console.log('👤 User data:', userData.id, userData.name, userData.role);
          
          // Ensure api_token is preserved
          if (!userData.api_token) {
            userData.api_token = token;
            console.log('🔑 Added token to userData');
          }
          
          setAuth(userData);
        })
        .catch((error) => { 
          console.error('❌ Token verification failed:', error.response?.status, error.response?.data);
          purgeAuth(); 
        });
    } else {
      console.log('⚠️ No token found, purging auth');
      purgeAuth();
    }
  }

  function isAdmin() { return user.value.role === "admin"; }
  function isUser()  { return user.value.role === "user"; }

  return {
    errors, user, isAuthenticated,
    login, adminLogin, logout,
    register, adminRegister,
    forgotPassword, resetPassword, changePassword,
    updateProfile, uploadAvatar,
    verifyAuth, isAdmin, isUser,
  };
});