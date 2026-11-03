import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
const printList93c145b8311d72937a7abb0808ebed8a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList93c145b8311d72937a7abb0808ebed8a.url(options),
    method: 'get',
})

printList93c145b8311d72937a7abb0808ebed8a.definition = {
    methods: ["get","head"],
    url: '/laporan-izin-tinggal/print-list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
printList93c145b8311d72937a7abb0808ebed8a.url = (options?: RouteQueryOptions) => {
    return printList93c145b8311d72937a7abb0808ebed8a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
printList93c145b8311d72937a7abb0808ebed8a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printList93c145b8311d72937a7abb0808ebed8a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
printList93c145b8311d72937a7abb0808ebed8a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printList93c145b8311d72937a7abb0808ebed8a.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
const printList93c145b8311d72937a7abb0808ebed8aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList93c145b8311d72937a7abb0808ebed8a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
printList93c145b8311d72937a7abb0808ebed8aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList93c145b8311d72937a7abb0808ebed8a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/print-list'
*/
printList93c145b8311d72937a7abb0808ebed8aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printList93c145b8311d72937a7abb0808ebed8a.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printList93c145b8311d72937a7abb0808ebed8a.form = printList93c145b8311d72937a7abb0808ebed8aForm
/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
const printListac565c1e01154998859790a389a50219 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListac565c1e01154998859790a389a50219.url(options),
    method: 'get',
})

printListac565c1e01154998859790a389a50219.definition = {
    methods: ["get","head"],
    url: '/laporan-izin-tinggal/export-pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
printListac565c1e01154998859790a389a50219.url = (options?: RouteQueryOptions) => {
    return printListac565c1e01154998859790a389a50219.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
printListac565c1e01154998859790a389a50219.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printListac565c1e01154998859790a389a50219.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
printListac565c1e01154998859790a389a50219.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printListac565c1e01154998859790a389a50219.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
const printListac565c1e01154998859790a389a50219Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListac565c1e01154998859790a389a50219.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
printListac565c1e01154998859790a389a50219Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListac565c1e01154998859790a389a50219.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::printList
* @see app/Http/Controllers/LaporanIzinTinggalController.php:196
* @route '/laporan-izin-tinggal/export-pdf'
*/
printListac565c1e01154998859790a389a50219Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printListac565c1e01154998859790a389a50219.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printListac565c1e01154998859790a389a50219.form = printListac565c1e01154998859790a389a50219Form

/**
* Multiple routes resolve to \App\Http\Controllers\LaporanIzinTinggalController::printList, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `printList['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const printList = {
    '/laporan-izin-tinggal/print-list': printList93c145b8311d72937a7abb0808ebed8a,
    '/laporan-izin-tinggal/export-pdf': printListac565c1e01154998859790a389a50219,
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/laporan-izin-tinggal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::index
* @see app/Http/Controllers/LaporanIzinTinggalController.php:22
* @route '/laporan-izin-tinggal'
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
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/laporan-izin-tinggal/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::create
* @see app/Http/Controllers/LaporanIzinTinggalController.php:114
* @route '/laporan-izin-tinggal/create'
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
* @see \App\Http\Controllers\LaporanIzinTinggalController::store
* @see app/Http/Controllers/LaporanIzinTinggalController.php:124
* @route '/laporan-izin-tinggal'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/laporan-izin-tinggal',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::store
* @see app/Http/Controllers/LaporanIzinTinggalController.php:124
* @route '/laporan-izin-tinggal'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::store
* @see app/Http/Controllers/LaporanIzinTinggalController.php:124
* @route '/laporan-izin-tinggal'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::store
* @see app/Http/Controllers/LaporanIzinTinggalController.php:124
* @route '/laporan-izin-tinggal'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::store
* @see app/Http/Controllers/LaporanIzinTinggalController.php:124
* @route '/laporan-izin-tinggal'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
export const show = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/laporan-izin-tinggal/{laporan_izin_tinggal}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
show.url = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { laporan_izin_tinggal: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { laporan_izin_tinggal: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            laporan_izin_tinggal: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        laporan_izin_tinggal: typeof args.laporan_izin_tinggal === 'object'
        ? args.laporan_izin_tinggal.id
        : args.laporan_izin_tinggal,
    }

    return show.definition.url
            .replace('{laporan_izin_tinggal}', parsedArgs.laporan_izin_tinggal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
show.get = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
show.head = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
const showForm = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
showForm.get = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::show
* @see app/Http/Controllers/LaporanIzinTinggalController.php:140
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
showForm.head = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
export const edit = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
edit.url = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { laporan_izin_tinggal: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { laporan_izin_tinggal: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            laporan_izin_tinggal: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        laporan_izin_tinggal: typeof args.laporan_izin_tinggal === 'object'
        ? args.laporan_izin_tinggal.id
        : args.laporan_izin_tinggal,
    }

    return edit.definition.url
            .replace('{laporan_izin_tinggal}', parsedArgs.laporan_izin_tinggal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
edit.get = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
edit.head = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
const editForm = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
editForm.get = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::edit
* @see app/Http/Controllers/LaporanIzinTinggalController.php:156
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}/edit'
*/
editForm.head = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
export const update = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/laporan-izin-tinggal/{laporan_izin_tinggal}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
update.url = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { laporan_izin_tinggal: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { laporan_izin_tinggal: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            laporan_izin_tinggal: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        laporan_izin_tinggal: typeof args.laporan_izin_tinggal === 'object'
        ? args.laporan_izin_tinggal.id
        : args.laporan_izin_tinggal,
    }

    return update.definition.url
            .replace('{laporan_izin_tinggal}', parsedArgs.laporan_izin_tinggal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
update.put = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
update.patch = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
const updateForm = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
updateForm.put = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::update
* @see app/Http/Controllers/LaporanIzinTinggalController.php:166
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
updateForm.patch = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\LaporanIzinTinggalController::destroy
* @see app/Http/Controllers/LaporanIzinTinggalController.php:182
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
export const destroy = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/laporan-izin-tinggal/{laporan_izin_tinggal}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::destroy
* @see app/Http/Controllers/LaporanIzinTinggalController.php:182
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
destroy.url = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { laporan_izin_tinggal: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { laporan_izin_tinggal: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            laporan_izin_tinggal: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        laporan_izin_tinggal: typeof args.laporan_izin_tinggal === 'object'
        ? args.laporan_izin_tinggal.id
        : args.laporan_izin_tinggal,
    }

    return destroy.definition.url
            .replace('{laporan_izin_tinggal}', parsedArgs.laporan_izin_tinggal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::destroy
* @see app/Http/Controllers/LaporanIzinTinggalController.php:182
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
destroy.delete = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::destroy
* @see app/Http/Controllers/LaporanIzinTinggalController.php:182
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
const destroyForm = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\LaporanIzinTinggalController::destroy
* @see app/Http/Controllers/LaporanIzinTinggalController.php:182
* @route '/laporan-izin-tinggal/{laporan_izin_tinggal}'
*/
destroyForm.delete = (args: { laporan_izin_tinggal: number | { id: number } } | [laporan_izin_tinggal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const LaporanIzinTinggalController = { printList, index, create, store, show, edit, update, destroy }

export default LaporanIzinTinggalController