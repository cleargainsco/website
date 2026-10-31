import { useId } from 'react';

export function BrushRing({ className }: { className?: string }) {
    const gradientId = `brush-${useId().replace(/[^a-zA-Z0-9-]/g, '')}`;

    return (
        <svg viewBox="0 0 200 200" aria-hidden="true" className={className}>
            <defs>
                <linearGradient id={gradientId} x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stopColor="#2e8ca4" />
                    <stop offset="0.45" stopColor="#0f4c5c" />
                    <stop offset="1" stopColor="#c9a96b" />
                </linearGradient>
            </defs>
            <circle
                cx="100"
                cy="100"
                r="84"
                fill="none"
                stroke={`url(#${gradientId})`}
                strokeWidth="7"
                strokeLinecap="round"
                strokeDasharray="300 228"
                transform="rotate(150 100 100)"
                opacity="0.5"
            />
            <circle
                cx="100"
                cy="100"
                r="78"
                fill="none"
                stroke="#c9a96b"
                strokeWidth="1.2"
                strokeDasharray="380 110"
                transform="rotate(200 100 100)"
                opacity="0.7"
            />
        </svg>
    );
}
