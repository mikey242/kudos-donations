export function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1)
}

export function camelize(str) {
    return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function (word, index) {
        return index === 0 ? word.toLowerCase() : word.toUpperCase()
    }).replace(/\s+/g, '')
}

export function getTabName() {
    const searchParams = new URLSearchParams(window.location.search)
    if (searchParams.has('tabName')) {
        return searchParams.get('tabName')
    }
    return 'mollie'
}

export function updateQueryStringParameter(key, value) {
    if ('URLSearchParams' in window) {
        let searchParams = new URLSearchParams(window.location.search)
        searchParams.set(key, value)
        let newRelativePathQuery = window.location.pathname + '?' + searchParams.toString()
        history.replaceState(null, '', newRelativePathQuery)
    }
}