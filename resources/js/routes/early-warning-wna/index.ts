import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
export const printList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

printList.definition = {
    methods: ["get","head"],
    url: '/early-warning-wna/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printList.url = (options?: RouteQueryOptions) => {
    return printList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
const printListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
*/
printListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::printList
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/print-list'
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
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/early-warning-wna/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
*/
exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::exportPdf
* @see app/Http/Controllers/EarlyWarningWnaController.php:96
* @route '/early-warning-wna/export-pdf'
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
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/early-warning-wna',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EarlyWarningWnaController::index
* @see app/Http/Controllers/EarlyWarningWnaController.php:19
* @route '/early-warning-wna'
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

const earlyWarningWna = {
    printList: Object.assign(printList, printList),
    exportPdf: Object.assign(exportPdf, exportPdf),
    index: Object.assign(index, index),
}

export default earlyWarningWna