import { useState } from 'react';
import { formatDate, formatShortDate } from '@/lib/dates';
import { formatMoney } from '@/lib/money';
import type { DailyOrders } from '@/types/dashboard';

const CHART_HEIGHT = 160;

export function OrdersChart({ days }: { days: DailyOrders[] }) {
    const [hovered, setHovered] = useState<number | null>(null);
    const peak = Math.max(1, ...days.map((day) => day.orders));
    const axisMax = Math.max(2, Math.ceil(peak / 2) * 2);
    const totalOrders = days.reduce((sum, day) => sum + day.orders, 0);
    const active = hovered === null ? null : days[hovered];

    return (
        <div>
            <div className="relative">
                <div
                    className="pointer-events-none absolute inset-x-0 top-0 flex flex-col justify-between text-xs text-muted-foreground"
                    style={{ height: CHART_HEIGHT }}
                    aria-hidden
                >
                    {[axisMax, axisMax / 2, 0].map((tick) => (
                        <div key={tick} className="flex items-center gap-2">
                            <span className="w-5 text-right tabular-nums">
                                {tick}
                            </span>
                            <span className="h-px flex-1 bg-border" />
                        </div>
                    ))}
                </div>

                <div
                    className="relative ml-7 flex items-end gap-0.5"
                    style={{ height: CHART_HEIGHT }}
                    onMouseLeave={() => setHovered(null)}
                >
                    {days.map((day, index) => (
                        <button
                            key={day.date}
                            type="button"
                            className="group flex h-full flex-1 cursor-default items-end justify-center focus-visible:outline-none"
                            onMouseEnter={() => setHovered(index)}
                            onFocus={() => setHovered(index)}
                            onBlur={() => setHovered(null)}
                            aria-label={`${formatDate(day.date)}: ${day.orders} ${day.orders === 1 ? 'order' : 'orders'}, ${formatMoney(day.value)}`}
                        >
                            <span
                                className="w-full max-w-6 rounded-t-[4px] bg-[#2e8ca4] transition-opacity group-hover:opacity-80 group-focus-visible:ring-2 group-focus-visible:ring-ring"
                                style={{
                                    height:
                                        day.orders === 0
                                            ? 0
                                            : Math.max(
                                                  4,
                                                  (day.orders / axisMax) *
                                                      CHART_HEIGHT,
                                              ),
                                }}
                            />
                        </button>
                    ))}

                    {active && hovered !== null && (
                        <div
                            className="pointer-events-none absolute -top-2 z-10 -translate-x-1/2 -translate-y-full rounded-lg border bg-popover px-3 py-2 text-xs whitespace-nowrap text-popover-foreground shadow-md"
                            style={{
                                left: `${((hovered + 0.5) / days.length) * 100}%`,
                            }}
                        >
                            <p className="font-medium">
                                {formatDate(active.date)}
                            </p>
                            <p className="text-muted-foreground">
                                {active.orders}{' '}
                                {active.orders === 1 ? 'order' : 'orders'} ·{' '}
                                {formatMoney(active.value)}
                            </p>
                        </div>
                    )}
                </div>
            </div>

            <div className="mt-2 ml-7 flex justify-between text-xs text-muted-foreground">
                <span>{days[0] && formatShortDate(days[0].date)}</span>
                <span>{days.at(-1) && formatShortDate(days.at(-1)!.date)}</span>
            </div>

            {totalOrders === 0 && (
                <p className="mt-4 text-center text-sm text-muted-foreground">
                    No orders in the last {days.length} days yet.
                </p>
            )}
        </div>
    );
}
