import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
const printList5b098c83f09257d274f992e625372ff8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList5b098c83f09257d274f992e625372ff8.url(options),
    method: 'get',
})

printList5b098c83f09257d274f992e625372ff8.definition = {
    methods: ["get","head"],
    url: '/penerbitan/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
printList5b098c83f09257d274f992e625372ff8.url = (options?: RouteQueryOptions) => {
    return printList5b098c83f09257d274f992e625372ff8.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
printList5b098c83f09257d274f992e625372ff8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList5b098c83f09257d274f992e625372ff8.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
printList5b098c83f09257d274f992e625372ff8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList5b098c83f09257d274f992e625372ff8.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
const printList5b098c83f09257d274f992e625372ff8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList5b098c83f09257d274f992e625372ff8.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
printList5b098c83f09257d274f992e625372ff8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList5b098c83f09257d274f992e625372ff8.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/print-list'
*/
printList5b098c83f09257d274f992e625372ff8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList5b098c83f09257d274f992e625372ff8.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList5b098c83f09257d274f992e625372ff8.form = printList5b098c83f09257d274f992e625372ff8Form
/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
const printList65115401ef8f88bf547a04e78ad4cb88 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList65115401ef8f88bf547a04e78ad4cb88.url(options),
    method: 'get',
})

printList65115401ef8f88bf547a04e78ad4cb88.definition = {
    methods: ["get","head"],
    url: '/penerbitan/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
printList65115401ef8f88bf547a04e78ad4cb88.url = (options?: RouteQueryOptions) => {
    return printList65115401ef8f88bf547a04e78ad4cb88.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
printList65115401ef8f88bf547a04e78ad4cb88.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList65115401ef8f88bf547a04e78ad4cb88.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
printList65115401ef8f88bf547a04e78ad4cb88.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList65115401ef8f88bf547a04e78ad4cb88.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
const printList65115401ef8f88bf547a04e78ad4cb88Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList65115401ef8f88bf547a04e78ad4cb88.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
printList65115401ef8f88bf547a04e78ad4cb88Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList65115401ef8f88bf547a04e78ad4cb88.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::printList
* @see app/Http/Controllers/PenerbitanController.php:191
* @route '/penerbitan/export-pdf'
*/
printList65115401ef8f88bf547a04e78ad4cb88Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList65115401ef8f88bf547a04e78ad4cb88.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList65115401ef8f88bf547a04e78ad4cb88.form = printList65115401ef8f88bf547a04e78ad4cb88Form

/**
* Multiple routes resolve to \App\Http\Controllers\PenerbitanController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/penerbitan/print-list': printList5b098c83f09257d274f992e625372ff8,
    '/penerbitan/export-pdf': printList65115401ef8f88bf547a04e78ad4cb88,
}

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
* @see app/Http/Controllers/PenerbitanController.php:109
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
* @see app/Http/Controllers/PenerbitanController.php:109
* @route '/penerbitan/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:109
* @route '/penerbitan/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:109
* @route '/penerbitan/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:109
* @route '/penerbitan/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:109
* @route '/penerbitan/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::create
* @see app/Http/Controllers/PenerbitanController.php:109
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
* @see app/Http/Controllers/PenerbitanController.php:119
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
* @see app/Http/Controllers/PenerbitanController.php:119
* @route '/penerbitan'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:119
* @route '/penerbitan'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:119
* @route '/penerbitan'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PenerbitanController::store
* @see app/Http/Controllers/PenerbitanController.php:119
* @route '/penerbitan'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:135
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
* @see app/Http/Controllers/PenerbitanController.php:135
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
* @see app/Http/Controllers/PenerbitanController.php:135
* @route '/penerbitan/{penerbitan}'
*/
show.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:135
* @route '/penerbitan/{penerbitan}'
*/
show.head = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:135
* @route '/penerbitan/{penerbitan}'
*/
const showForm = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:135
* @route '/penerbitan/{penerbitan}'
*/
showForm.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::show
* @see app/Http/Controllers/PenerbitanController.php:135
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
* @see app/Http/Controllers/PenerbitanController.php:151
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
* @see app/Http/Controllers/PenerbitanController.php:151
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
* @see app/Http/Controllers/PenerbitanController.php:151
* @route '/penerbitan/{penerbitan}/edit'
*/
edit.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:151
* @route '/penerbitan/{penerbitan}/edit'
*/
edit.head = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:151
* @route '/penerbitan/{penerbitan}/edit'
*/
const editForm = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:151
* @route '/penerbitan/{penerbitan}/edit'
*/
editForm.get = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PenerbitanController::edit
* @see app/Http/Controllers/PenerbitanController.php:151
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
* @see app/Http/Controllers/PenerbitanController.php:161
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
* @see app/Http/Controllers/PenerbitanController.php:161
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
* @see app/Http/Controllers/PenerbitanController.php:161
* @route '/penerbitan/{penerbitan}'
*/
update.put = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:161
* @route '/penerbitan/{penerbitan}'
*/
update.patch = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\PenerbitanController::update
* @see app/Http/Controllers/PenerbitanController.php:161
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
* @see app/Http/Controllers/PenerbitanController.php:161
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
* @see app/Http/Controllers/PenerbitanController.php:161
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
* @see app/Http/Controllers/PenerbitanController.php:177
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
* @see app/Http/Controllers/PenerbitanController.php:177
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
* @see app/Http/Controllers/PenerbitanController.php:177
* @route '/penerbitan/{penerbitan}'
*/
destroy.delete = (args: { penerbitan: number | { id: number } } | [penerbitan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PenerbitanController::destroy
* @see app/Http/Controllers/PenerbitanController.php:177
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
* @see app/Http/Controllers/PenerbitanController.php:177
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

const PenerbitanController = { printList, index, create, store, show, edit, update, destroy }

export default PenerbitanController