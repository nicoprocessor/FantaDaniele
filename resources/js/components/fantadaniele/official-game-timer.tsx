import { useEffect, useRef, useState } from 'react';

type Props = { closesAt: string; serverNow: string; onExpire: () => void };

function remainingSeconds(closesAt: string, serverNow: string): number {
    return Math.max(
        0,
        Math.floor((Date.parse(closesAt) - Date.parse(serverNow)) / 1000),
    );
}

function formatDuration(totalSeconds: number): string {
    const days = Math.floor(totalSeconds / 86_400);
    const hours = Math.floor((totalSeconds % 86_400) / 3_600);
    const minutes = Math.floor((totalSeconds % 3_600) / 60);
    const seconds = totalSeconds % 60;

    return `${days}g ${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s`;
}

export function OfficialGameTimer({ closesAt, serverNow, onExpire }: Props) {
    const [seconds, setSeconds] = useState(() =>
        remainingSeconds(closesAt, serverNow),
    );
    const durationSeconds = useRef(remainingSeconds(closesAt, serverNow));
    const onExpireRef = useRef(onExpire);
    const hasExpired = useRef(false);

    useEffect(() => {
        onExpireRef.current = onExpire;
    }, [onExpire]);

    useEffect(() => {
        const startedAt = performance.now();
        const updateTimer = (): void => {
            const elapsedSeconds = Math.floor(
                (performance.now() - startedAt) / 1_000,
            );
            const nextSeconds = Math.max(
                0,
                durationSeconds.current - elapsedSeconds,
            );

            setSeconds(nextSeconds);

            if (nextSeconds === 0 && !hasExpired.current) {
                hasExpired.current = true;
                onExpireRef.current();
            }
        };
        const interval = window.setInterval(updateTimer, 1_000);
        window.addEventListener('visibilitychange', updateTimer);
        updateTimer();

        return () => {
            window.clearInterval(interval);
            window.removeEventListener('visibilitychange', updateTimer);
        };
    }, []);

    return (
        <time
            dateTime={closesAt}
            className="font-mono text-2xl font-black tabular-nums"
            data-testid="official-game-timer"
        >
            {formatDuration(seconds)}
        </time>
    );
}
