import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
*/
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/abg/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
*/
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
*/
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
*/
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
*/
const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
*/
exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::exportMethod
* @see app/Http/Controllers/AbgController.php:296
* @route '/abg/export'
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
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
const printList7de8b412ed23da45c9e3a6a0c835d37c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList7de8b412ed23da45c9e3a6a0c835d37c.url(options),
    method: 'get',
})

printList7de8b412ed23da45c9e3a6a0c835d37c.definition = {
    methods: ["get","head"],
    url: '/abg/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
printList7de8b412ed23da45c9e3a6a0c835d37c.url = (options?: RouteQueryOptions) => {
    return printList7de8b412ed23da45c9e3a6a0c835d37c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
printList7de8b412ed23da45c9e3a6a0c835d37c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList7de8b412ed23da45c9e3a6a0c835d37c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
printList7de8b412ed23da45c9e3a6a0c835d37c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList7de8b412ed23da45c9e3a6a0c835d37c.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
const printList7de8b412ed23da45c9e3a6a0c835d37cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList7de8b412ed23da45c9e3a6a0c835d37c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
printList7de8b412ed23da45c9e3a6a0c835d37cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList7de8b412ed23da45c9e3a6a0c835d37c.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/print-list'
*/
printList7de8b412ed23da45c9e3a6a0c835d37cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList7de8b412ed23da45c9e3a6a0c835d37c.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList7de8b412ed23da45c9e3a6a0c835d37c.form = printList7de8b412ed23da45c9e3a6a0c835d37cForm
/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
const printListc9b43774099142c8863828d932eeeb8a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListc9b43774099142c8863828d932eeeb8a.url(options),
    method: 'get',
})

printListc9b43774099142c8863828d932eeeb8a.definition = {
    methods: ["get","head"],
    url: '/abg/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
printListc9b43774099142c8863828d932eeeb8a.url = (options?: RouteQueryOptions) => {
    return printListc9b43774099142c8863828d932eeeb8a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
printListc9b43774099142c8863828d932eeeb8a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListc9b43774099142c8863828d932eeeb8a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
printListc9b43774099142c8863828d932eeeb8a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListc9b43774099142c8863828d932eeeb8a.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
const printListc9b43774099142c8863828d932eeeb8aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListc9b43774099142c8863828d932eeeb8a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
printListc9b43774099142c8863828d932eeeb8aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListc9b43774099142c8863828d932eeeb8a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::printList
* @see app/Http/Controllers/AbgController.php:388
* @route '/abg/export-pdf'
*/
printListc9b43774099142c8863828d932eeeb8aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListc9b43774099142c8863828d932eeeb8a.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printListc9b43774099142c8863828d932eeeb8a.form = printListc9b43774099142c8863828d932eeeb8aForm

/**
* Multiple routes resolve to \App\Http\Controllers\AbgController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/abg/print-list': printList7de8b412ed23da45c9e3a6a0c835d37c,
    '/abg/export-pdf': printListc9b43774099142c8863828d932eeeb8a,
}

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
export const photo = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

photo.definition = {
    methods: ["get","head"],
    url: '/abg/{abg}/photo',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
photo.url = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { abg: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { abg: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            abg: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        abg: typeof args.abg === 'object'
        ? args.abg.id
        : args.abg,
    }

    return photo.definition.url
            .replace('{abg}', parsedArgs.abg.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
photo.get = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
photo.head = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: photo.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
const photoForm = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
photoForm.get = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photo.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::photo
* @see app/Http/Controllers/AbgController.php:247
* @route '/abg/{abg}/photo'
*/
photoForm.head = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/abg',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::index
* @see app/Http/Controllers/AbgController.php:29
* @route '/abg'
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
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/abg/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::create
* @see app/Http/Controllers/AbgController.php:98
* @route '/abg/create'
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
* @see \App\Http\Controllers\AbgController::store
* @see app/Http/Controllers/AbgController.php:108
* @route '/abg'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/abg',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AbgController::store
* @see app/Http/Controllers/AbgController.php:108
* @route '/abg'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::store
* @see app/Http/Controllers/AbgController.php:108
* @route '/abg'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AbgController::store
* @see app/Http/Controllers/AbgController.php:108
* @route '/abg'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AbgController::store
* @see app/Http/Controllers/AbgController.php:108
* @route '/abg'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
export const show = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/abg/{abg}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
show.url = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { abg: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { abg: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            abg: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        abg: typeof args.abg === 'object'
        ? args.abg.id
        : args.abg,
    }

    return show.definition.url
            .replace('{abg}', parsedArgs.abg.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
show.get = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
show.head = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
const showForm = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
showForm.get = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::show
* @see app/Http/Controllers/AbgController.php:145
* @route '/abg/{abg}'
*/
showForm.head = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
export const edit = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/abg/{abg}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
edit.url = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { abg: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { abg: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            abg: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        abg: typeof args.abg === 'object'
        ? args.abg.id
        : args.abg,
    }

    return edit.definition.url
            .replace('{abg}', parsedArgs.abg.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
edit.get = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
edit.head = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
const editForm = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
editForm.get = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AbgController::edit
* @see app/Http/Controllers/AbgController.php:161
* @route '/abg/{abg}/edit'
*/
editForm.head = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
export const update = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/abg/{abg}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
update.url = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { abg: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { abg: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            abg: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        abg: typeof args.abg === 'object'
        ? args.abg.id
        : args.abg,
    }

    return update.definition.url
            .replace('{abg}', parsedArgs.abg.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
update.put = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
update.patch = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
const updateForm = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
updateForm.put = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AbgController::update
* @see app/Http/Controllers/AbgController.php:171
* @route '/abg/{abg}'
*/
updateForm.patch = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\AbgController::destroy
* @see app/Http/Controllers/AbgController.php:223
* @route '/abg/{abg}'
*/
export const destroy = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/abg/{abg}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\AbgController::destroy
* @see app/Http/Controllers/AbgController.php:223
* @route '/abg/{abg}'
*/
destroy.url = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { abg: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { abg: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            abg: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        abg: typeof args.abg === 'object'
        ? args.abg.id
        : args.abg,
    }

    return destroy.definition.url
            .replace('{abg}', parsedArgs.abg.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AbgController::destroy
* @see app/Http/Controllers/AbgController.php:223
* @route '/abg/{abg}'
*/
destroy.delete = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\AbgController::destroy
* @see app/Http/Controllers/AbgController.php:223
* @route '/abg/{abg}'
*/
const destroyForm = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AbgController::destroy
* @see app/Http/Controllers/AbgController.php:223
* @route '/abg/{abg}'
*/
destroyForm.delete = (args: { abg: number | { id: number } } | [abg: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const AbgController = { exportMethod, printList, photo, index, create, store, show, edit, update, destroy, export: exportMethod }

export default AbgController