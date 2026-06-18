import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export interface Notification {
    id: string;
    user_id: string;
    type: 'ticket' | 'ticket_reply' | 'demo' | 'subscription' | 'payment';
    title: string;
    body: string;
    link: string | null;
    reference_id: string | null;
    reference_type: string | null;
    is_read: boolean;
    created_at: string;
    updated_at: string;
}

/**
 * Play a short notification chime using Web Audio API.
 * No external audio files needed.
 */
let audioCtx: AudioContext | null = null;

function playNotificationSound() {
    try {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        const now = audioCtx.currentTime;

        // Two-tone chime: C5 → E5 (pleasant ascending)
        const oscillators = [
            { freq: 523.25, start: now, duration: 0.15 },      // C5
            { freq: 659.25, start: now + 0.12, duration: 0.2 }, // E5
        ];

        for (const { freq, start, duration } of oscillators) {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, start);

            gain.gain.setValueAtTime(0, start);
            gain.gain.linearRampToValueAtTime(0.15, start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, start + duration);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(start);
            osc.stop(start + duration);
        }
    } catch {
        // Audio not supported — ignore silently
    }
}

// Module-level — track ID yang sudah pernah bunyi sound-nya agar tidak double
let soundPlayedForIds = new Set<string>();

export function useNotifications(pollingIntervalMs = 20000) {
    const unreadCount = ref(0);
    const notifications = ref<Notification[]>([]);
    const showDropdown = ref(false);
    const loading = ref(false);
    const error = ref<string | null>(null);

    let pollingTimer: ReturnType<typeof setInterval> | null = null;

    async function fetchNotifications() {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get('/api/notifications/unread');
            const data: Notification[] = response.data.data;
            notifications.value = data;
            unreadCount.value = response.data.unread_count;

            // Bunyi hanya untuk ID yang belum pernah bunyi sebelumnya
            for (const notif of data) {
                if (!soundPlayedForIds.has(notif.id)) {
                    playNotificationSound();
                    soundPlayedForIds.add(notif.id);
                    break; // cukup 1 bunyi per batch
                }
            }
        } catch (e: any) {
            error.value = 'Gagal memuat notifikasi';
            console.error('Fetch notifications error:', e);
        } finally {
            loading.value = false;
        }
    }

    async function markAsRead(notif: Notification) {
        try {
            await axios.post(`/api/notifications/${notif.id}/read`);
            notifications.value = notifications.value.filter(n => n.id !== notif.id);
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (e) {
            console.error('Mark as read error:', e);
        }
    }

    async function markAllAsRead() {
        try {
            await axios.post('/api/notifications/read-all');
            notifications.value = [];
            unreadCount.value = 0;
        } catch (e) {
            console.error('Mark all as read error:', e);
        }
    }

    function startPolling() {
        stopPolling();
        fetchNotifications();
        pollingTimer = setInterval(fetchNotifications, pollingIntervalMs);
    }

    function stopPolling() {
        if (pollingTimer !== null) {
            clearInterval(pollingTimer);
            pollingTimer = null;
        }
    }

    function toggleDropdown() {
        showDropdown.value = !showDropdown.value;
        if (showDropdown.value) {
            fetchNotifications();
        }
    }

    function closeDropdown() {
        showDropdown.value = false;
    }

    onMounted(() => {
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        unreadCount,
        notifications,
        showDropdown,
        loading,
        error,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        startPolling,
        stopPolling,
        toggleDropdown,
        closeDropdown,
    };
}
