import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/pengaturan',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::index
* @see app/Http/Controllers/SettingController.php:22
* @route '/pengaturan'
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
* @see \App\Http\Controllers\SettingController::update
* @see app/Http/Controllers/SettingController.php:43
* @route '/pengaturan'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/pengaturan',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingController::update
* @see app/Http/Controllers/SettingController.php:43
* @route '/pengaturan'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingController::update
* @see app/Http/Controllers/SettingController.php:43
* @route '/pengaturan'
*/
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingController::update
* @see app/Http/Controllers/SettingController.php:43
* @route '/pengaturan'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingController::update
* @see app/Http/Controllers/SettingController.php:43
* @route '/pengaturan'
*/
updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

update.form = updateForm

const pengaturan = {
    index: Object.assign(index, index),
    update: Object.assign(update, update),
}

export default pengaturan