import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults, validateParameters } from './../../wayfinder'
/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
export const icon = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: icon.url(args, options),
    method: 'get',
})

icon.definition = {
    methods: ["get","head"],
    url: '/pengaturan/icon/{slot?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
icon.url = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { slot: args }
    }

    if (Array.isArray(args)) {
        args = {
            slot: args[0],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "slot",
    ])

    const parsedArgs = {
        slot: args?.slot,
    }

    return icon.definition.url
            .replace('{slot?}', parsedArgs.slot?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
icon.get = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: icon.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
icon.head = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: icon.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
const iconForm = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icon.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
iconForm.get = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icon.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingController::icon
* @see app/Http/Controllers/SettingController.php:121
* @route '/pengaturan/icon/{slot?}'
*/
iconForm.head = (args?: { slot?: string | number } | [slot: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: icon.url(args, {
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