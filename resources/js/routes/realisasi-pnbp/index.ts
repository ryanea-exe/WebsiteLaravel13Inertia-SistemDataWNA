import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
*/
export const printList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

printList.definition = {
    methods: ["get","head"],
    url: '/realisasi-pnbp/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
*/
printList.url = (options?: RouteQueryOptions) => {
    return printList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
*/
printList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
*/
printList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
*/
const printListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
*/
printListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::printList
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/print-list'
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
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
*/
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/realisasi-pnbp/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
*/
exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
*/
exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
*/
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
*/
const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
*/
exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::exportPdf
* @see app/Http/Controllers/RealisasiPnbpController.php:187
* @route '/realisasi-pnbp/export-pdf'
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
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/realisasi-pnbp',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::index
* @see app/Http/Controllers/RealisasiPnbpController.php:23
* @route '/realisasi-pnbp'
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

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/realisasi-pnbp/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::create
* @see app/Http/Controllers/RealisasiPnbpController.php:105
* @route '/realisasi-pnbp/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\RealisasiPnbpController::store
* @see app/Http/Controllers/RealisasiPnbpController.php:115
* @route '/realisasi-pnbp'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/realisasi-pnbp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::store
* @see app/Http/Controllers/RealisasiPnbpController.php:115
* @route '/realisasi-pnbp'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::store
* @see app/Http/Controllers/RealisasiPnbpController.php:115
* @route '/realisasi-pnbp'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::store
* @see app/Http/Controllers/RealisasiPnbpController.php:115
* @route '/realisasi-pnbp'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::store
* @see app/Http/Controllers/RealisasiPnbpController.php:115
* @route '/realisasi-pnbp'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
export const show = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/realisasi-pnbp/{realisasi_pnbp}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
show.url = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { realisasi_pnbp: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { realisasi_pnbp: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            realisasi_pnbp: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        realisasi_pnbp: typeof args.realisasi_pnbp === 'object'
        ? args.realisasi_pnbp.id
        : args.realisasi_pnbp,
    }

    return show.definition.url
            .replace('{realisasi_pnbp}', parsedArgs.realisasi_pnbp.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
show.get = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
show.head = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
const showForm = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
showForm.get = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::show
* @see app/Http/Controllers/RealisasiPnbpController.php:131
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
showForm.head = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
export const edit = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/realisasi-pnbp/{realisasi_pnbp}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
edit.url = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { realisasi_pnbp: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { realisasi_pnbp: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            realisasi_pnbp: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        realisasi_pnbp: typeof args.realisasi_pnbp === 'object'
        ? args.realisasi_pnbp.id
        : args.realisasi_pnbp,
    }

    return edit.definition.url
            .replace('{realisasi_pnbp}', parsedArgs.realisasi_pnbp.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
edit.get = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
edit.head = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
const editForm = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
editForm.get = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::edit
* @see app/Http/Controllers/RealisasiPnbpController.php:147
* @route '/realisasi-pnbp/{realisasi_pnbp}/edit'
*/
editForm.head = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
export const update = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/realisasi-pnbp/{realisasi_pnbp}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
update.url = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { realisasi_pnbp: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { realisasi_pnbp: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            realisasi_pnbp: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        realisasi_pnbp: typeof args.realisasi_pnbp === 'object'
        ? args.realisasi_pnbp.id
        : args.realisasi_pnbp,
    }

    return update.definition.url
            .replace('{realisasi_pnbp}', parsedArgs.realisasi_pnbp.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
update.put = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
update.patch = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
const updateForm = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
updateForm.put = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::update
* @see app/Http/Controllers/RealisasiPnbpController.php:157
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
updateForm.patch = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\RealisasiPnbpController::destroy
* @see app/Http/Controllers/RealisasiPnbpController.php:173
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
export const destroy = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/realisasi-pnbp/{realisasi_pnbp}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\RealisasiPnbpController::destroy
* @see app/Http/Controllers/RealisasiPnbpController.php:173
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
destroy.url = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { realisasi_pnbp: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { realisasi_pnbp: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            realisasi_pnbp: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        realisasi_pnbp: typeof args.realisasi_pnbp === 'object'
        ? args.realisasi_pnbp.id
        : args.realisasi_pnbp,
    }

    return destroy.definition.url
            .replace('{realisasi_pnbp}', parsedArgs.realisasi_pnbp.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RealisasiPnbpController::destroy
* @see app/Http/Controllers/RealisasiPnbpController.php:173
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
destroy.delete = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::destroy
* @see app/Http/Controllers/RealisasiPnbpController.php:173
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
const destroyForm = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RealisasiPnbpController::destroy
* @see app/Http/Controllers/RealisasiPnbpController.php:173
* @route '/realisasi-pnbp/{realisasi_pnbp}'
*/
destroyForm.delete = (args: { realisasi_pnbp: number | { id: number } } | [realisasi_pnbp: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const realisasiPnbp = {
    printList: Object.assign(printList, printList),
    exportPdf: Object.assign(exportPdf, exportPdf),
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default realisasiPnbp