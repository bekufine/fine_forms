export function debounce(fn, wait = 1500) {
    let timeoutId = null;

    function debounced(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(this, args), wait);
    }

    debounced.cancel = () => clearTimeout(timeoutId);

    return debounced;
}
