import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
const printListe8db53cc5103cd9e62cce4fdb15b0abd = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListe8db53cc5103cd9e62cce4fdb15b0abd.url(options),
    method: 'get',
})

printListe8db53cc5103cd9e62cce4fdb15b0abd.definition = {
    methods: ["get","head"],
    url: '/early-warning/abg/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
printListe8db53cc5103cd9e62cce4fdb15b0abd.url = (options?: RouteQueryOptions) => {
    return printListe8db53cc5103cd9e62cce4fdb15b0abd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
printListe8db53cc5103cd9e62cce4fdb15b0abd.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListe8db53cc5103cd9e62cce4fdb15b0abd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
printListe8db53cc5103cd9e62cce4fdb15b0abd.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListe8db53cc5103cd9e62cce4fdb15b0abd.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
const printListe8db53cc5103cd9e62cce4fdb15b0abdForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListe8db53cc5103cd9e62cce4fdb15b0abd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
printListe8db53cc5103cd9e62cce4fdb15b0abdForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListe8db53cc5103cd9e62cce4fdb15b0abd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/print-list'
*/
printListe8db53cc5103cd9e62cce4fdb15b0abdForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListe8db53cc5103cd9e62cce4fdb15b0abd.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printListe8db53cc5103cd9e62cce4fdb15b0abd.form = printListe8db53cc5103cd9e62cce4fdb15b0abdForm
/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
const printList2843d18dd658024909fc6440f9d8fe5c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList2843d18dd658024909fc6440f9d8fe5c.url(options),
    method: 'get',
})

printList2843d18dd658024909fc6440f9d8fe5c.definition = {
    methods: ["get","head"],
    url: '/early-warning/abg/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
printList2843d18dd658024909fc6440f9d8fe5c.url = (options?: RouteQueryOptions) => {
    return printList2843d18dd658024909fc6440f9d8fe5c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
printList2843d18dd658024909fc6440f9d8fe5c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList2843d18dd658024909fc6440f9d8fe5c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
printList2843d18dd658024909fc6440f9d8fe5c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList2843d18dd658024909fc6440f9d8fe5c.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
const printList2843d18dd658024909fc6440f9d8fe5cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList2843d18dd658024909fc6440f9d8fe5c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
printList2843d18dd658024909fc6440f9d8fe5cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList2843d18dd658024909fc6440f9d8fe5c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:145
* @route '/early-warning/abg/export-pdf'
*/
printList2843d18dd658024909fc6440f9d8fe5cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList2843d18dd658024909fc6440f9d8fe5c.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList2843d18dd658024909fc6440f9d8fe5c.form = printList2843d18dd658024909fc6440f9d8fe5cForm

/**
* Multiple routes resolve to \App\Http\Controllers\EarlyWarningAbgController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/early-warning/abg/print-list': printListe8db53cc5103cd9e62cce4fdb15b0abd,
    '/early-warning/abg/export-pdf': printList2843d18dd658024909fc6440f9d8fe5c,
}

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/early-warning/abg',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::index
* @see app/Http/Controllers/EarlyWarningAbgController.php:18
* @route '/early-warning/abg'
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

const EarlyWarningAbgController = { printList, index }

export default EarlyWarningAbgController