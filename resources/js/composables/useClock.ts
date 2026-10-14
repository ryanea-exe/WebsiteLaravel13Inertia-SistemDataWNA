import { onMounted, onUnmounted, ref } from 'vue';

export function useClock() {
    const isMounted = ref(false);
    const timeFormatted = ref('--:--:-- WIB');
    const dateFormatted = ref('Memuat waktu...');

    const dateFormatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });

    const updateClock = () => {
        const now = new Date();
        dateFormatted.value = dateFormatter.format(now);
        timeFormatted.value = `${timeFormatter.format(now).replace(/\./g, ':')} WIB`;
    };

    let intervalId: ReturnType<typeof setInterval> | null = null;

    onMounted(() => {
        isMounted.value = true;
        updateClock();
        intervalId = setInterval(updateClock, 1000);
    });

    onUnmounted(() => {
        if (intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
    });

    return {
        isMounted,
        timeFormatted,
        dateFormatted,
    };
}
