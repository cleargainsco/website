export function formatDate(isoDate: string): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeZone: 'UTC',
    }).format(new Date(`${isoDate.slice(0, 10)}T00:00:00Z`));
}

export function formatDateTime(isoDateTime: string): string {
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(isoDateTime));
}

export function formatShortDate(isoDate: string): string {
    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        timeZone: 'UTC',
    }).format(new Date(`${isoDate.slice(0, 10)}T00:00:00Z`));
}
