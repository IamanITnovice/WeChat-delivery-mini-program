import axios from 'axios';
import qs from 'qs';
import router from '@/router';
import configObj from "@/config";
import { useUserStore } from "@/store";
import { ElMessage } from 'element-plus';
let { baseURL, tokenName, contentType, withCredentials, responseType } = configObj;

axios.defaults.headers['Content-Type'] = contentType; //配置请求头
axios.defaults.baseURL = baseURL;
axios.defaults.withCredentials = withCredentials;
axios.defaults.responseType = responseType;

//POST传参序列化(添加请求拦截器)
axios.interceptors.request.use((config) => {
  //在发送请求之前做某件事
  const userStore = useUserStore();
	const { token, userInfo } = userStore;
  // 使用 Authorization Bearer token
  if (token) {
    config.headers['Authori-Zation'] = `Bearer ${token}`;
  }
  config.headers["AppID"] = userInfo && userInfo.AppID;
  if (config.method === 'post' && !config.headers.uploadImg) {
    config.data = qs.stringify(config.data);
  }
  return config;
}, (error) => {
  console.log('错误的传参')
  return Promise.reject(error);
});

//返回状态判断(添加响应拦截器)
axios.interceptors.response.use((res) => {
  // 如果是 blob 类型的响应（文件下载），直接返回
  if (res.config.responseType === 'blob') {
    return res;
  }

  //未登陆
  if (res.data.code !== 1) {
    console.log('未登录状态')
    if(res.data.code === 0){
		ElMessage({
			showClose: true,
			message: res.data.msg,
			type: "error",
		});
      return Promise.reject(res.data);
    }else if(res.data.code){
		const userStore = useUserStore();
		const { afterLogout } = userStore;
		afterLogout();
		router.push({
			path: '/login',
		})
    }
  }else{
    return res.data;
  }
}, (error) => {
	ElMessage({
		showClose: true,
		message: '接口请求异常，请稍后再试~',
		type: "error"
	});
  return Promise.reject(error);
});

/**
 * 返回一个Promise(发送post请求)
 * errorback是否错误回调
 */
export function _post(url, params, errorback) {
  return new Promise((resolve, reject) => {
    axios.post(url, params)
      .then(response => {
        resolve(response);
      })
      .catch((error) => {
        errorback && reject(error);
      })
  })
}

/**
 * 返回一个Promise(发送get请求)
 * errorback是否错误回调
 */
export function _get(url, param, errorback) {
  return new Promise((resolve, reject) => {
    axios.get(url, {
        params: param
      })
      .then(response => {
        resolve(response)
      })
      .catch((error) => {
        errorback && reject(error);
      })
  })
}
/**
 * 返回一个Promise(发送上传请求)
 * errorback是否错误回调
 */
export function _upload(url, formData, errorback)
{
    return new Promise((resolve, reject) =>
    {
        let headers = {
          "Content-Type": "multipart/form-data",
          "uploadImg": true,
        }
        axios.post(url, formData, { headers })
            .then(response =>
            {
                resolve(response);
            })
            .catch((error) =>
            {
                reject(error);
            })
    })
}

function getFileName(headers = {}) {
  const disposition = headers['content-disposition'] || headers['Content-Disposition'] || '';
  const match = disposition.match(/filename\*?=(?:UTF-8''|")?([^\";]+)/i);
  if (!match || !match[1]) {
    return `download-${Date.now()}.xlsx`;
  }
  return decodeURIComponent(match[1]).replace(/"/g, '');
}

function saveBlobFile(response) {
  const blob = new Blob([response.data]);
  const fileName = getFileName(response.headers);
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
}

/**
 * 返回一个Promise(下载文件)
 * errorback是否错误回调
 */
export function _download(url, param, errorback) {
  return new Promise((resolve, reject) => {
    axios.get(url, {
        params: param,
        responseType: 'blob'
      })
      .then(response => {
        // 检查响应是否是JSON错误信息
        const contentType = response.headers['content-type'] || '';
        if (contentType.includes('application/json')) {
          // 如果是JSON，说明是错误响应，需要解析
          const reader = new FileReader();
          reader.onload = function() {
            try {
              const result = JSON.parse(reader.result);
              if (result.code !== 1) {
                ElMessage({
                  showClose: true,
                  message: result.msg || '导出失败',
                  type: "error",
                });
                errorback && reject(result);
              }
            } catch (e) {
              saveBlobFile(response);
              resolve(response);
            }
          };
          reader.readAsText(response.data);
        } else {
          // 正常的文件下载
          saveBlobFile(response);
          resolve(response);
        }
      })
      .catch((error) => {
        errorback && reject(error);
      })
  })
}

export default {
  _post,
  _get,
  _upload,
  _download
}
