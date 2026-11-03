import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
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
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67.url = (options?: RouteQueryOptions) => {
    return printListb1c04af624a2368d8c9bba9d88f3be67.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/print-list'
*/
const printListb1c04af624a2368d8c9bba9d88f3be67Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/print-list'
*/
printListb1c04af624a2368d8c9bba9d88f3be67Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListb1c04af624a2368d8c9bba9d88f3be67.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
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
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
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
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879.url = (options?: RouteQueryOptions) => {
    return printList9b0b864cc8f96a1830a0a1245f616879.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/export-pdf'
*/
const printList9b0b864cc8f96a1830a0a1245f616879Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
* @route '/early-warning/export-pdf'
*/
printList9b0b864cc8f96a1830a0a1245f616879Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList9b0b864cc8f96a1830a0a1245f616879.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::printList
* @see app/Http/Controllers/EarlyWarningController.php:93
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
* Multiple routes resolve to \App\Http\Controllers\EarlyWarningController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/early-warning/print-list': printListb1c04af624a2368d8c9bba9d88f3be67,
    '/early-warning/export-pdf': printList9b0b864cc8f96a1830a0a1245f616879,
}

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/early-warning',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningController::index
* @see app/Http/Controllers/EarlyWarningController.php:19
* @route '/early-warning'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const EarlyWarningController = { printList, index }

export default EarlyWarningController