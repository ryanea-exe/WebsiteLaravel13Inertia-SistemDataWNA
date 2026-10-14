declare module '@/routes/register' {
    export const store: any;
    export const create: any;
}

declare module '@/routes/two-factor' {
    export const qrCode: any;
    export const recoveryCodes: any;
    export const secretKey: any;
    export const confirm: any;
    export const regenerateRecoveryCodes: any;
    export const enable: any;
    export const disable: any;
}

declare module '@/routes/two-factor/login' {
    export const store: any;
}

declare module '@/routes/verification' {
    export const send: any;
    export const verify: any;
}

declare module '@/routes/password' {
    export const email: any;
    export const request: any;
    export const reset: any;
    export const update: any;
}

declare module '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController' {
    export const PasskeyConfirmationController: any;
    export default PasskeyConfirmationController;
}

declare module '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController' {
    export const destroy: any;
    export const store: any;
    export const PasskeyRegistrationController: any;
    export default PasskeyRegistrationController;
}
