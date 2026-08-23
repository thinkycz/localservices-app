export const bookingStatuses = {
    pending: { label: { cs: 'Čeká na potvrzení', en: 'Pending' }, tone: 'warning' },
    confirmed: { label: { cs: 'Potvrzeno', en: 'Confirmed' }, tone: 'brand' },
    completed: { label: { cs: 'Dokončeno', en: 'Completed' }, tone: 'success' },
    cancelled: { label: { cs: 'Zrušeno', en: 'Cancelled' }, tone: 'danger' },
};

export function bookingStatus(status, locale = 'cs') {
    const item = bookingStatuses[status] ?? { label: { cs: status, en: status }, tone: 'neutral' };
    return { label: item.label[locale === 'en' ? 'en' : 'cs'], tone: item.tone };
}

export function formatCurrency(amount, currency = 'CZK', locale = 'cs-CZ') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        maximumFractionDigits: Number(amount) % 1 === 0 ? 0 : 2,
    }).format(Number(amount ?? 0));
}

export function formatDate(value, locale = 'cs-CZ') {
    return new Intl.DateTimeFormat(locale, { dateStyle: 'medium' }).format(new Date(value));
}
