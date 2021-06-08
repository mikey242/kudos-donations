export function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1)
}

export function camelize(str) {
    return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function (word, index) {
        return index === 0 ? word.toLowerCase() : word.toUpperCase()
    }).replace(/\s+/g, '')
}

export function getQueryVar(key, fallback = null) {
    const searchParams = new URLSearchParams(window.location.search)
    if (searchParams.has(key)) {
        return searchParams.get(key)
    }
    return fallback
}

export function updateQueryParameter(key, value) {
    if ('URLSearchParams' in window) {
        let searchParams = new URLSearchParams(window.location.search)
        searchParams.set(key, value)
        let newRelativePathQuery = window.location.pathname + '?' + searchParams.toString()
        history.replaceState(null, '', newRelativePathQuery)
    }
}

export function getStyle(style) {
    return getComputedStyle(document.documentElement).getPropertyValue(style)
}

export function setAttributes(el, attrs) {
    Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, value))
}