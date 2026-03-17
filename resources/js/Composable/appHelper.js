import { useRestApi } from '../http/rest_request';

export function useAppHelper() {
    const { get, post, del, put, patch } = useRestApi();

    function ucFirst(text) {
        return text[0].toUpperCase() + text.slice(1).toLowerCase();
    }

    function ucWords(text) {
        return text
            .split(/\s+/)
            .map((word) => word[0].toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }

    return {
        get,
        post,
        del,
        put,
        patch,
        ucFirst,
        ucWords
    };
}