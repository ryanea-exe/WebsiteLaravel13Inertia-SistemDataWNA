import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
*/
export const printList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

printList.definition = {
    methods: ["get","head"],
    url: '/penerbitan/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
*/
printList.url = (options?: RouteQueryOptions) => {
    return printList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
*/
printList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
*/
printList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
*/
const printListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
*/
printListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/print-list'
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
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
*/
export const exportPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

exportPdf.definition = {
    methods: ["get","head"],
    url: '/penerbitan/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
*/
exportPdf.url = (options?: RouteQueryOptions) => {
    return exportPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
*/
exportPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
*/
exportPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportPdf.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
*/
const exportPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
*/
exportPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportPdf.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::exportPdf
* @see app/Http/Controllers/PenerbitanController.php:192
* @route '/penerbitan/export-pdf'
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
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/penerbitan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::index
* @see app/Http/Controllers/PenerbitanController.php:22
* @route '/penerbitan'
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
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/penerbitan/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:110
* @route '/penerbitan/create'
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
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:120
* @route '/penerbitan'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/penerbitan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:120
* @route '/penerbitan'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:120
* @route '/penerbitan'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:120
* @route '/penerbitan'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:120
* @route '/penerbitan'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
export const show = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/penerbitan/{penerbitan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
show.url = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { penerbitan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { penerbitan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            penerbitan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        penerbitan: typeof args.penerbitan === 'object'
        ? args.penerbitan.id
        : args.penerbitan,
    }

    return show.definition.url
            .replace('{penerbitan}', parsedArgs.penerbitan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
show.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
show.head = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
const showForm = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
showForm.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:136
* @route '/penerbitan/{penerbitan}'
*/
showForm.head = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
export const edit = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/penerbitan/{penerbitan}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
edit.url = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { penerbitan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { penerbitan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            penerbitan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        penerbitan: typeof args.penerbitan === 'object'
        ? args.penerbitan.id
        : args.penerbitan,
    }

    return edit.definition.url
            .replace('{penerbitan}', parsedArgs.penerbitan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
edit.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
edit.head = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
const editForm = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
editForm.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:152
* @route '/penerbitan/{penerbitan}/edit'
*/
editForm.head = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
export const update = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/penerbitan/{penerbitan}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
update.url = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { penerbitan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { penerbitan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            penerbitan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        penerbitan: typeof args.penerbitan === 'object'
        ? args.penerbitan.id
        : args.penerbitan,
    }

    return update.definition.url
            .replace('{penerbitan}', parsedArgs.penerbitan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
update.put = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
update.patch = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
const updateForm = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
updateForm.put = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:162
* @route '/penerbitan/{penerbitan}'
*/
updateForm.patch = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\PenerbitanController::destroy
* @see app/Http/Controllers/PenerbitanController.php:178
* @route '/penerbitan/{penerbitan}'
*/
export const destroy = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/penerbitan/{penerbitan}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PenerbitanController::destroy
* @see app/Http/Controllers/PenerbitanController.php:178
* @route '/penerbitan/{penerbitan}'
*/
destroy.url = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { penerbitan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { penerbitan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            penerbitan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        penerbitan: typeof args.penerbitan === 'object'
        ? args.penerbitan.id
        : args.penerbitan,
    }

    return destroy.definition.url
            .replace('{penerbitan}', parsedArgs.penerbitan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::destroy
* @see app/Http/Controllers/PenerbitanController.php:178
* @route '/penerbitan/{penerbitan}'
*/
destroy.delete = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PenerbitanController::destroy
* @see app/Http/Controllers/PenerbitanController.php:178
* @route '/penerbitan/{penerbitan}'
*/
const destroyForm = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::destroy
* @see app/Http/Controllers/PenerbitanController.php:178
* @route '/penerbitan/{penerbitan}'
*/
destroyForm.delete = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const penerbitan = {
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

export default penerbitan