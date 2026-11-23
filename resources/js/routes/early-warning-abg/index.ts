import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
export const printList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

printList.definition = {
    methods: ["get","head"],
    url: '/early-warning/abg/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
printList.url = (options?: RouteQueryOptions) => {
    return printList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
printList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
printList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
const printListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
printListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::printList
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/print-list'
*/
printListForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList.form = printListForm

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/early-warning/abg/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningAbgController::exportPdf
* @see app/Http/Controllers/EarlyWarningAbgController.php:141
* @route '/early-warning/abg/export-pdf'
*/
exportPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportPdf.form = exportPdfForm

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

const earlyWarningAbg = {
    printList: Object.assign(printList, printList),
    exportPdf: Object.assign(exportPdf, exportPdf),
    index: Object.assign(index, index),
}

export default earlyWarningAbg