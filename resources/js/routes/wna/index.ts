import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
*/
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/wna/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
*/
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
*/
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
*/
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
*/
const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
*/
exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::exportMethod
* @see app/Http/Controllers/WnaController.php:321
* @route '/wna/export'
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
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
*/
export const printList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

printList.definition = {
    methods: ["get","head"],
    url: '/wna/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
*/
printList.url = (options?: RouteQueryOptions) => {
    return printList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
*/
printList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
*/
printList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
*/
const printListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
*/
printListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::printList
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/print-list'
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
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
*/
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/wna/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
*/
exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
*/
exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
*/
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
*/
const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
*/
exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::exportPdf
* @see app/Http/Controllers/WnaController.php:420
* @route '/wna/export-pdf'
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
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
export const card = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: card.url(args, options),
    method: 'get',
})

card.definition = {
    methods: ["get","head"],
    url: '/wna/{wna}/card',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
card.url = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { wna: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { wna: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            wna: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        wna: typeof args.wna === 'object'
        ? args.wna.id
        : args.wna,
    }

    return card.definition.url
            .replace('{wna}', parsedArgs.wna.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
card.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: card.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
card.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: card.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
const cardForm = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: card.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
cardForm.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: card.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::card
* @see app/Http/Controllers/WnaController.php:308
* @route '/wna/{wna}/card'
*/
cardForm.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: card.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

card.form = cardForm

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
export const photo = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

photo.definition = {
    methods: ["get","head"],
    url: '/wna/{wna}/photo',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
photo.url = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { wna: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { wna: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            wna: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        wna: typeof args.wna === 'object'
        ? args.wna.id
        : args.wna,
    }

    return photo.definition.url
            .replace('{wna}', parsedArgs.wna.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
photo.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
photo.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: photo.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
const photoForm = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
photoForm.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::photo
* @see app/Http/Controllers/WnaController.php:259
* @route '/wna/{wna}/photo'
*/
photoForm.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/wna',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::index
* @see app/Http/Controllers/WnaController.php:30
* @route '/wna'
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
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/wna/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::create
* @see app/Http/Controllers/WnaController.php:110
* @route '/wna/create'
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
* @see \App\Http\Controllers\WnaController::store
* @see app/Http/Controllers/WnaController.php:120
* @route '/wna'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/wna',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WnaController::store
* @see app/Http/Controllers/WnaController.php:120
* @route '/wna'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::store
* @see app/Http/Controllers/WnaController.php:120
* @route '/wna'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WnaController::store
* @see app/Http/Controllers/WnaController.php:120
* @route '/wna'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WnaController::store
* @see app/Http/Controllers/WnaController.php:120
* @route '/wna'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
export const show = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/wna/{wna}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
show.url = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { wna: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { wna: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            wna: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        wna: typeof args.wna === 'object'
        ? args.wna.id
        : args.wna,
    }

    return show.definition.url
            .replace('{wna}', parsedArgs.wna.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
show.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
show.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
const showForm = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
showForm.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::show
* @see app/Http/Controllers/WnaController.php:157
* @route '/wna/{wna}'
*/
showForm.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
export const edit = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/wna/{wna}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
edit.url = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { wna: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { wna: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            wna: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        wna: typeof args.wna === 'object'
        ? args.wna.id
        : args.wna,
    }

    return edit.definition.url
            .replace('{wna}', parsedArgs.wna.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
edit.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
edit.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
const editForm = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
editForm.get = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WnaController::edit
* @see app/Http/Controllers/WnaController.php:173
* @route '/wna/{wna}/edit'
*/
editForm.head = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
export const update = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/wna/{wna}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
update.url = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { wna: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { wna: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            wna: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        wna: typeof args.wna === 'object'
        ? args.wna.id
        : args.wna,
    }

    return update.definition.url
            .replace('{wna}', parsedArgs.wna.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
update.put = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
update.patch = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
const updateForm = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
updateForm.put = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WnaController::update
* @see app/Http/Controllers/WnaController.php:183
* @route '/wna/{wna}'
*/
updateForm.patch = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\WnaController::destroy
* @see app/Http/Controllers/WnaController.php:235
* @route '/wna/{wna}'
*/
export const destroy = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/wna/{wna}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\WnaController::destroy
* @see app/Http/Controllers/WnaController.php:235
* @route '/wna/{wna}'
*/
destroy.url = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { wna: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { wna: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            wna: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        wna: typeof args.wna === 'object'
        ? args.wna.id
        : args.wna,
    }

    return destroy.definition.url
            .replace('{wna}', parsedArgs.wna.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WnaController::destroy
* @see app/Http/Controllers/WnaController.php:235
* @route '/wna/{wna}'
*/
destroy.delete = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\WnaController::destroy
* @see app/Http/Controllers/WnaController.php:235
* @route '/wna/{wna}'
*/
const destroyForm = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WnaController::destroy
* @see app/Http/Controllers/WnaController.php:235
* @route '/wna/{wna}'
*/
destroyForm.delete = (args: { wna: number | { id: number } } | [wna: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const wna = {
    export: Object.assign(exportMethod, exportMethod),
    printList: Object.assign(printList, printList),
    exportPdf: Object.assign(exportPdf, exportPdf),
    card: Object.assign(card, card),
    photo: Object.assign(photo, photo),
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default wna