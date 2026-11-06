import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/affidavit/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportMethod
* @see app/Http/Controllers/AffidavitController.php:303
* @route '/affidavit/export'
*/
exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethod.form = exportMethodForm

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
*/
export const printList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

printList.definition = {
    methods: ["get","head"],
    url: '/affidavit/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
*/
printList.url = (options?: RouteQueryOptions) => {
    return printList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
*/
printList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
*/
printList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
*/
const printListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
*/
printListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::printList
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/print-list'
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
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
*/
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/affidavit/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
*/
exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
*/
exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
*/
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
*/
const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
*/
exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::exportPdf
* @see app/Http/Controllers/AffidavitController.php:399
* @route '/affidavit/export-pdf'
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
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
export const photo = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

photo.definition = {
    methods: ["get","head"],
    url: '/affidavit/{affidavit}/photo',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
photo.url = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { affidavit: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { affidavit: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            affidavit: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        affidavit: typeof args.affidavit === 'object'
        ? args.affidavit.id
        : args.affidavit,
    }

    return photo.definition.url
            .replace('{affidavit}', parsedArgs.affidavit.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
photo.get = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
photo.head = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: photo.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
const photoForm = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
photoForm.get = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::photo
* @see app/Http/Controllers/AffidavitController.php:254
* @route '/affidavit/{affidavit}/photo'
*/
photoForm.head = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

photo.form = photoForm

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/affidavit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::index
* @see app/Http/Controllers/AffidavitController.php:29
* @route '/affidavit'
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
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/affidavit/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::create
* @see app/Http/Controllers/AffidavitController.php:105
* @route '/affidavit/create'
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
* @see \App\Http\Controllers\AffidavitController::store
* @see app/Http/Controllers/AffidavitController.php:115
* @route '/affidavit'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/affidavit',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AffidavitController::store
* @see app/Http/Controllers/AffidavitController.php:115
* @route '/affidavit'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::store
* @see app/Http/Controllers/AffidavitController.php:115
* @route '/affidavit'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AffidavitController::store
* @see app/Http/Controllers/AffidavitController.php:115
* @route '/affidavit'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AffidavitController::store
* @see app/Http/Controllers/AffidavitController.php:115
* @route '/affidavit'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
export const show = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/affidavit/{affidavit}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
show.url = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { affidavit: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { affidavit: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            affidavit: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        affidavit: typeof args.affidavit === 'object'
        ? args.affidavit.id
        : args.affidavit,
    }

    return show.definition.url
            .replace('{affidavit}', parsedArgs.affidavit.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
show.get = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
show.head = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
const showForm = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
showForm.get = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::show
* @see app/Http/Controllers/AffidavitController.php:152
* @route '/affidavit/{affidavit}'
*/
showForm.head = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
export const edit = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/affidavit/{affidavit}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
edit.url = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { affidavit: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { affidavit: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            affidavit: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        affidavit: typeof args.affidavit === 'object'
        ? args.affidavit.id
        : args.affidavit,
    }

    return edit.definition.url
            .replace('{affidavit}', parsedArgs.affidavit.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
edit.get = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
edit.head = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
const editForm = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
editForm.get = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AffidavitController::edit
* @see app/Http/Controllers/AffidavitController.php:168
* @route '/affidavit/{affidavit}/edit'
*/
editForm.head = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
export const update = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/affidavit/{affidavit}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
update.url = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { affidavit: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { affidavit: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            affidavit: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        affidavit: typeof args.affidavit === 'object'
        ? args.affidavit.id
        : args.affidavit,
    }

    return update.definition.url
            .replace('{affidavit}', parsedArgs.affidavit.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
update.put = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
update.patch = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
const updateForm = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
updateForm.put = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AffidavitController::update
* @see app/Http/Controllers/AffidavitController.php:178
* @route '/affidavit/{affidavit}'
*/
updateForm.patch = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\AffidavitController::destroy
* @see app/Http/Controllers/AffidavitController.php:230
* @route '/affidavit/{affidavit}'
*/
export const destroy = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/affidavit/{affidavit}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\AffidavitController::destroy
* @see app/Http/Controllers/AffidavitController.php:230
* @route '/affidavit/{affidavit}'
*/
destroy.url = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { affidavit: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { affidavit: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            affidavit: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        affidavit: typeof args.affidavit === 'object'
        ? args.affidavit.id
        : args.affidavit,
    }

    return destroy.definition.url
            .replace('{affidavit}', parsedArgs.affidavit.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AffidavitController::destroy
* @see app/Http/Controllers/AffidavitController.php:230
* @route '/affidavit/{affidavit}'
*/
destroy.delete = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\AffidavitController::destroy
* @see app/Http/Controllers/AffidavitController.php:230
* @route '/affidavit/{affidavit}'
*/
const destroyForm = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AffidavitController::destroy
* @see app/Http/Controllers/AffidavitController.php:230
* @route '/affidavit/{affidavit}'
*/
destroyForm.delete = (args: { affidavit: number | { id: number } } | [affidavit: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const affidavit = {
    export: Object.assign(exportMethod, exportMethod),
    printList: Object.assign(printList, printList),
    exportPdf: Object.assign(exportPdf, exportPdf),
    photo: Object.assign(photo, photo),
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default affidavit