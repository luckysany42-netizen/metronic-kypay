import type { App } from "vue";
import type { AxiosResponse } from "axios";
import axios from "axios";
import VueAxios from "vue-axios";
import JwtService from "@/core/services/JwtService";

/**
 * @description service to call HTTP request via Axios
 */
class ApiService {
  /**
   * @description property to share vue instance
   */
  public static vueInstance: App;

  /**
   * @description initialize vue axios
   */
  public static init(app: App<Element>) {
    ApiService.vueInstance = app;
    ApiService.vueInstance.use(VueAxios, axios);
    
    // Get API URL from env
    // Priority: 1) VITE_APP_API_URL env var, 2) window.location.origin for relative, 3) fallback /api
    let apiUrl = import.meta.env.VITE_APP_API_URL;
    
    if (!apiUrl || apiUrl === 'undefined') {
      // Fallback: use /api as default
      apiUrl = '/api';
    }
    
    ApiService.vueInstance.axios.defaults.baseURL = apiUrl;
    
    console.log('🔧 ApiService initialized with baseURL:', apiUrl);
    console.log('🔍 Debug - VITE_APP_API_URL:', import.meta.env.VITE_APP_API_URL);
    console.log('🔍 Debug - Full env:', import.meta.env);

    // ✅ Auto-attach token ke SEMUA request tanpa perlu panggil setHeader() manual
    ApiService.vueInstance.axios.interceptors.request.use((config) => {
      const token = JwtService.getToken();
      if (token) {
        config.headers["Authorization"] = `Token ${token}`;
        config.headers["Accept"] = "application/json";
      }
      console.log('📤 Axios Request:', {url: config.url, baseURL: ApiService.vueInstance.axios.defaults.baseURL, fullUrl: config.baseURL + config.url});
      return config;
    });
  }

  /**
   * @description set the default HTTP request headers
   * @deprecated Tidak perlu dipanggil manual lagi, sudah otomatis via interceptor
   */
  public static setHeader(): void {
    ApiService.vueInstance.axios.defaults.headers.common[
      "Authorization"
    ] = `Token ${JwtService.getToken()}`;
    ApiService.vueInstance.axios.defaults.headers.common["Accept"] =
      "application/json";
  }

  /**
   * @description send the GET HTTP request
   * @param resource: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static query(resource: string, params: any): Promise<AxiosResponse> {
    return ApiService.vueInstance.axios.get(resource, params);
  }

  /**
   * @description send the GET HTTP request
   * @param resource: string
   * @param slug: string
   * @returns Promise<AxiosResponse>
   */
  public static get(
    resource: string,
    slug = "" as string
  ): Promise<AxiosResponse> {
    return ApiService.vueInstance.axios.get(`${resource}/${slug}`);
  }

  /**
   * @description set the POST HTTP request
   * @param resource: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static post(resource: string, params: any): Promise<AxiosResponse> {
    return ApiService.vueInstance.axios.post(`${resource}`, params);
  }

  /**
   * @description send the UPDATE HTTP request
   * @param resource: string
   * @param slug: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static update(
    resource: string,
    slug: string,
    params: any
  ): Promise<AxiosResponse> {
    return ApiService.vueInstance.axios.put(`${resource}/${slug}`, params);
  }

  /**
   * @description Send the PUT HTTP request
   * @param resource: string
   * @param params: AxiosRequestConfig
   * @returns Promise<AxiosResponse>
   */
  public static put(resource: string, params: any): Promise<AxiosResponse> {
    return ApiService.vueInstance.axios.put(`${resource}`, params);
  }

  /**
   * @description Send the DELETE HTTP request
   * @param resource: string
   * @returns Promise<AxiosResponse>
   */
  public static delete(resource: string): Promise<AxiosResponse> {
    return ApiService.vueInstance.axios.delete(resource);
  }
}

export default ApiService;