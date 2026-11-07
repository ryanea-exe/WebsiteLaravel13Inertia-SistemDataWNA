import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
const printList25746de532e7ccd28ba0fafeeeb2121f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList25746de532e7ccd28ba0fafeeeb2121f.url(options),
    method: 'get',
})

printList25746de532e7ccd28ba0fafeeeb2121f.definition = {
    methods: ["get","head"],
    url: '/permohonan/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
printList25746de532e7ccd28ba0fafeeeb2121f.url = (options?: RouteQueryOptions) => {
    return printList25746de532e7ccd28ba0fafeeeb2121f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
printList25746de532e7ccd28ba0fafeeeb2121f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList25746de532e7ccd28ba0fafeeeb2121f.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
printList25746de532e7ccd28ba0fafeeeb2121f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList25746de532e7ccd28ba0fafeeeb2121f.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
const printList25746de532e7ccd28ba0fafeeeb2121fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList25746de532e7ccd28ba0fafeeeb2121f.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
printList25746de532e7ccd28ba0fafeeeb2121fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList25746de532e7ccd28ba0fafeeeb2121f.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/print-list'
*/
printList25746de532e7ccd28ba0fafeeeb2121fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList25746de532e7ccd28ba0fafeeeb2121f.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList25746de532e7ccd28ba0fafeeeb2121f.form = printList25746de532e7ccd28ba0fafeeeb2121fForm
/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
const printListf530867beb35072ba0838897af3d7dde = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListf530867beb35072ba0838897af3d7dde.url(options),
    method: 'get',
})

printListf530867beb35072ba0838897af3d7dde.definition = {
    methods: ["get","head"],
    url: '/permohonan/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
printListf530867beb35072ba0838897af3d7dde.url = (options?: RouteQueryOptions) => {
    return printListf530867beb35072ba0838897af3d7dde.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
printListf530867beb35072ba0838897af3d7dde.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListf530867beb35072ba0838897af3d7dde.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
printListf530867beb35072ba0838897af3d7dde.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListf530867beb35072ba0838897af3d7dde.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
const printListf530867beb35072ba0838897af3d7ddeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListf530867beb35072ba0838897af3d7dde.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
printListf530867beb35072ba0838897af3d7ddeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListf530867beb35072ba0838897af3d7dde.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::printList
* @see app/Http/Controllers/PermohonanController.php:192
* @route '/permohonan/export-pdf'
*/
printListf530867beb35072ba0838897af3d7ddeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListf530867beb35072ba0838897af3d7dde.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printListf530867beb35072ba0838897af3d7dde.form = printListf530867beb35072ba0838897af3d7ddeForm

/**
* Multiple routes resolve to \App\Http\Controllers\PermohonanController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/permohonan/print-list': printList25746de532e7ccd28ba0fafeeeb2121f,
    '/permohonan/export-pdf': printListf530867beb35072ba0838897af3d7dde,
}

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/permohonan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::index
* @see app/Http/Controllers/PermohonanController.php:22
* @route '/permohonan'
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
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/permohonan/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::create
* @see app/Http/Controllers/PermohonanController.php:110
* @route '/permohonan/create'
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
* @see \App\Http\Controllers\PermohonanController::store
* @see app/Http/Controllers/PermohonanController.php:120
* @route '/permohonan'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/permohonan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PermohonanController::store
* @see app/Http/Controllers/PermohonanController.php:120
* @route '/permohonan'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::store
* @see app/Http/Controllers/PermohonanController.php:120
* @route '/permohonan'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PermohonanController::store
* @see app/Http/Controllers/PermohonanController.php:120
* @route '/permohonan'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PermohonanController::store
* @see app/Http/Controllers/PermohonanController.php:120
* @route '/permohonan'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
export const show = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/permohonan/{permohonan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
show.url = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { permohonan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { permohonan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            permohonan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        permohonan: typeof args.permohonan === 'object'
        ? args.permohonan.id
        : args.permohonan,
    }

    return show.definition.url
            .replace('{permohonan}', parsedArgs.permohonan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
show.get = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
show.head = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
const showForm = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
showForm.get = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::show
* @see app/Http/Controllers/PermohonanController.php:136
* @route '/permohonan/{permohonan}'
*/
showForm.head = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
export const edit = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/permohonan/{permohonan}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
edit.url = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { permohonan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { permohonan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            permohonan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        permohonan: typeof args.permohonan === 'object'
        ? args.permohonan.id
        : args.permohonan,
    }

    return edit.definition.url
            .replace('{permohonan}', parsedArgs.permohonan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
edit.get = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
edit.head = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
const editForm = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
editForm.get = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PermohonanController::edit
* @see app/Http/Controllers/PermohonanController.php:152
* @route '/permohonan/{permohonan}/edit'
*/
editForm.head = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
export const update = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/permohonan/{permohonan}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
update.url = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { permohonan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { permohonan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            permohonan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        permohonan: typeof args.permohonan === 'object'
        ? args.permohonan.id
        : args.permohonan,
    }

    return update.definition.url
            .replace('{permohonan}', parsedArgs.permohonan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
update.put = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
update.patch = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
const updateForm = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
updateForm.put = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PermohonanController::update
* @see app/Http/Controllers/PermohonanController.php:162
* @route '/permohonan/{permohonan}'
*/
updateForm.patch = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\PermohonanController::destroy
* @see app/Http/Controllers/PermohonanController.php:178
* @route '/permohonan/{permohonan}'
*/
export const destroy = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/permohonan/{permohonan}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PermohonanController::destroy
* @see app/Http/Controllers/PermohonanController.php:178
* @route '/permohonan/{permohonan}'
*/
destroy.url = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { permohonan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { permohonan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            permohonan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        permohonan: typeof args.permohonan === 'object'
        ? args.permohonan.id
        : args.permohonan,
    }

    return destroy.definition.url
            .replace('{permohonan}', parsedArgs.permohonan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PermohonanController::destroy
* @see app/Http/Controllers/PermohonanController.php:178
* @route '/permohonan/{permohonan}'
*/
destroy.delete = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PermohonanController::destroy
* @see app/Http/Controllers/PermohonanController.php:178
* @route '/permohonan/{permohonan}'
*/
const destroyForm = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PermohonanController::destroy
* @see app/Http/Controllers/PermohonanController.php:178
* @route '/permohonan/{permohonan}'
*/
destroyForm.delete = (args: { permohonan: number | { id: number } } | [permohonan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const PermohonanController = { printList, index, create, store, show, edit, update, destroy }

export default PermohonanController