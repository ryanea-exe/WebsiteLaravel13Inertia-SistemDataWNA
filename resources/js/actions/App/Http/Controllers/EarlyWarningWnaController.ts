import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
const printListeb4960bfe4f9265a4ec7e7468bc8b972 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListeb4960bfe4f9265a4ec7e7468bc8b972.url(options),
    method: 'get',
})

printListeb4960bfe4f9265a4ec7e7468bc8b972.definition = {
    methods: ["get","head"],
    url: '/early-warning-wna/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printListeb4960bfe4f9265a4ec7e7468bc8b972.url = (options?: RouteQueryOptions) => {
    return printListeb4960bfe4f9265a4ec7e7468bc8b972.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printListeb4960bfe4f9265a4ec7e7468bc8b972.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListeb4960bfe4f9265a4ec7e7468bc8b972.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printListeb4960bfe4f9265a4ec7e7468bc8b972.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListeb4960bfe4f9265a4ec7e7468bc8b972.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
const printListeb4960bfe4f9265a4ec7e7468bc8b972Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListeb4960bfe4f9265a4ec7e7468bc8b972.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printListeb4960bfe4f9265a4ec7e7468bc8b972Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListeb4960bfe4f9265a4ec7e7468bc8b972.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printListeb4960bfe4f9265a4ec7e7468bc8b972Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListeb4960bfe4f9265a4ec7e7468bc8b972.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printListeb4960bfe4f9265a4ec7e7468bc8b972.form = printListeb4960bfe4f9265a4ec7e7468bc8b972Form
/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
const printList2f1ffd21dce544981a5b1afdb22c82c1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList2f1ffd21dce544981a5b1afdb22c82c1.url(options),
    method: 'get',
})

printList2f1ffd21dce544981a5b1afdb22c82c1.definition = {
    methods: ["get","head"],
    url: '/early-warning-wna/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
printList2f1ffd21dce544981a5b1afdb22c82c1.url = (options?: RouteQueryOptions) => {
    return printList2f1ffd21dce544981a5b1afdb22c82c1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
printList2f1ffd21dce544981a5b1afdb22c82c1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList2f1ffd21dce544981a5b1afdb22c82c1.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
printList2f1ffd21dce544981a5b1afdb22c82c1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList2f1ffd21dce544981a5b1afdb22c82c1.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
const printList2f1ffd21dce544981a5b1afdb22c82c1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList2f1ffd21dce544981a5b1afdb22c82c1.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
printList2f1ffd21dce544981a5b1afdb22c82c1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList2f1ffd21dce544981a5b1afdb22c82c1.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
printList2f1ffd21dce544981a5b1afdb22c82c1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList2f1ffd21dce544981a5b1afdb22c82c1.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList2f1ffd21dce544981a5b1afdb22c82c1.form = printList2f1ffd21dce544981a5b1afdb22c82c1Form
/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
const printListb1c04af624a2368d8c9bba9d88f3be67 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

printListb1c04af624a2368d8c9bba9d88f3be67.definition = {
    methods: ["get","head"],
    url: '/early-warning/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67.url = (options?: RouteQueryOptions) => {
    return printListb1c04af624a2368d8c9bba9d88f3be67.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
const printListb1c04af624a2368d8c9bba9d88f3be67Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListb1c04af624a2368d8c9bba9d88f3be67.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printListb1c04af624a2368d8c9bba9d88f3be67.form = printListb1c04af624a2368d8c9bba9d88f3be67Form
/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
const printList9b0b864cc8f96a1830a0a1245f616879 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

printList9b0b864cc8f96a1830a0a1245f616879.definition = {
    methods: ["get","head"],
    url: '/early-warning/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879.url = (options?: RouteQueryOptions) => {
    return printList9b0b864cc8f96a1830a0a1245f616879.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
const printList9b0b864cc8f96a1830a0a1245f616879Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList9b0b864cc8f96a1830a0a1245f616879.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList9b0b864cc8f96a1830a0a1245f616879.form = printList9b0b864cc8f96a1830a0a1245f616879Form

/**
* Multiple routes resolve to \App\Http\Controllers\EarlyWarningWnaController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/early-warning-wna/print-list': printListeb4960bfe4f9265a4ec7e7468bc8b972,
    '/early-warning-wna/export-pdf': printList2f1ffd21dce544981a5b1afdb22c82c1,
    '/early-warning/print-list': printListb1c04af624a2368d8c9bba9d88f3be67,
    '/early-warning/export-pdf': printList9b0b864cc8f96a1830a0a1245f616879,
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
const indexffcd4947e2fb0705dc860771a86fafb6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexffcd4947e2fb0705dc860771a86fafb6.url(options),
    method: 'get',
})

indexffcd4947e2fb0705dc860771a86fafb6.definition = {
    methods: ["get","head"],
    url: '/early-warning-wna',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
indexffcd4947e2fb0705dc860771a86fafb6.url = (options?: RouteQueryOptions) => {
    return indexffcd4947e2fb0705dc860771a86fafb6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
indexffcd4947e2fb0705dc860771a86fafb6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexffcd4947e2fb0705dc860771a86fafb6.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
indexffcd4947e2fb0705dc860771a86fafb6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexffcd4947e2fb0705dc860771a86fafb6.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
const indexffcd4947e2fb0705dc860771a86fafb6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: indexffcd4947e2fb0705dc860771a86fafb6.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
indexffcd4947e2fb0705dc860771a86fafb6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: indexffcd4947e2fb0705dc860771a86fafb6.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
indexffcd4947e2fb0705dc860771a86fafb6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: indexffcd4947e2fb0705dc860771a86fafb6.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

indexffcd4947e2fb0705dc860771a86fafb6.form = indexffcd4947e2fb0705dc860771a86fafb6Form
/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
const index41e6406b76446fc68438060dcfce0ced = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index41e6406b76446fc68438060dcfce0ced.url(options),
    method: 'get',
})

index41e6406b76446fc68438060dcfce0ced.definition = {
    methods: ["get","head"],
    url: '/early-warning',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
index41e6406b76446fc68438060dcfce0ced.url = (options?: RouteQueryOptions) => {
    return index41e6406b76446fc68438060dcfce0ced.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
index41e6406b76446fc68438060dcfce0ced.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index41e6406b76446fc68438060dcfce0ced.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
index41e6406b76446fc68438060dcfce0ced.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index41e6406b76446fc68438060dcfce0ced.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
const index41e6406b76446fc68438060dcfce0cedForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index41e6406b76446fc68438060dcfce0ced.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
index41e6406b76446fc68438060dcfce0cedForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index41e6406b76446fc68438060dcfce0ced.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning'
*/
index41e6406b76446fc68438060dcfce0cedForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index41e6406b76446fc68438060dcfce0ced.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index41e6406b76446fc68438060dcfce0ced.form = index41e6406b76446fc68438060dcfce0cedForm

/**
* Multiple routes resolve to \App\Http\Controllers\EarlyWarningWnaController::index, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `index['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const index = {
    '/early-warning-wna': indexffcd4947e2fb0705dc860771a86fafb6,
    '/early-warning': index41e6406b76446fc68438060dcfce0ced,
}

const EarlyWarningWnaController = { printList, index }

export default EarlyWarningWnaController