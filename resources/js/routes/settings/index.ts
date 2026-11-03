import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
export const icon = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: icon.url(options),
    method: 'get',
})

icon.definition = {
    methods: ["get","head"],
    url: '/pengaturan/icon',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
icon.url = (options?: RouteQueryOptions) => {
    return icon.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
icon.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: icon.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
icon.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: icon.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
const iconForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icon.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
iconForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icon.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:92
* @route '/pengaturan/icon'
*/
iconForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icon.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

icon.form = iconForm

const settings = {
    icon: Object.assign(icon, icon),
}

export default settings