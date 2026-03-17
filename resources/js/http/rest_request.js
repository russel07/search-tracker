import axios from 'axios';

const eGiftCardRestRequest = (method, route, data = {}) => {
    const url = `${window.wplms_cleanup_pro_app_vars.rest_info.rest_url}/${route}`;

    const headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-WP-Nonce': window.wplms_cleanup_pro_app_vars.rest_info.nonce
    };

    // Set query timestamp if data is not empty
    if (Object.keys(data).length !== 0) {
        data.query_timestamp = Date.now();
    }

    // Configure axios request
    const config = {
        url: url,
        method: method.toLowerCase(),
        headers: headers,
        // For GET requests, attach data as params
        [method.toLowerCase() === 'get' ? 'params' : 'data']: data,
    };

    // Send request using axios
    return axios(config)
        .then(response => response.data)
        .catch(error => {
            console.error(`Error during ${method} request to ${route}:`, error);
            throw error;
        });
};

export function useRestApi() {
    function get(route, data) {
        return eGiftCardRestRequest('GET', route, data);
    }

    function post(route, data) {
        return eGiftCardRestRequest('POST', route, data);
    }

    function del(route, data) {
        return eGiftCardRestRequest('DELETE', route, data);
    }

    function put(route, data) {
        return eGiftCardRestRequest('PUT', route, data);
    }

    function patch(route, data) {
        return eGiftCardRestRequest('PATCH', route, data);
    }

    return {
        get,
        post,
        del,
        put,
        patch,
    };
}


// Set up Axios interceptors globally
(() => {
    // Request Interceptor: Add the current nonce to every request header
    axios.interceptors.request.use(config => {
        config.headers['X-WP-Nonce'] = window.wplms_cleanup_pro_app_vars.rest_info.nonce;
        return config;
    }, error => {
        return Promise.reject(error);
    });

    // Response Interceptor: Handle response and update nonce if available
    axios.interceptors.response.use(response => {
        // Check and update nonce from response headers
        const nonce = response.headers['x-wp-nonce'];
        if (nonce) {
            window.wplms_cleanup_pro_app_vars.rest_info.nonce = nonce;
        }
        return response;
    }, error => {
        // Error handling for specific status codes
        if (error.response) {
            const statusCode = Number(error.response.status);
            let message = '';

            if (statusCode > 423 && statusCode < 410) {
                // Handle custom error conditions for status codes between 410 and 423
                if (error.response.data) {
                    message = error.response.data.message || error.response.data.error;
                }
                return Promise.reject(message);
            } else if (error.response.data && error.response.data.code === 'rest_cookie_invalid_nonce') {
                // Handle invalid nonce error: Renew the nonce with an AJAX request
                return axios.post(window.wplms_cleanup_pro_app_vars.ajax_url, {
                    action: 'amc_renew_rest/renew_rest_nonce',
                }).then(response => {
                    // Update the nonce in global variables
                    window.wplms_cleanup_pro_app_vars.rest_info.nonce = response.data.nonce;

                    // Retry the original request with the updated nonce
                    error.config.headers['X-WP-Nonce'] = response.data.nonce;
                    return axios(error.config);
                });
            }
        }
        return Promise.reject(error);
    });
})();