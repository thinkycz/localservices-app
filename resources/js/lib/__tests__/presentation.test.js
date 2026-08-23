import { describe, expect, it } from 'vitest';
import { bookingStatus, formatCurrency, formatDate } from '../presentation';

describe('shared presentation helpers', () => {
    it('presents booking states consistently in both locales', () => {
        expect(bookingStatus('confirmed')).toEqual({ label: 'Potvrzeno', tone: 'brand' });
        expect(bookingStatus('cancelled', 'en')).toEqual({ label: 'Cancelled', tone: 'danger' });
    });

    it('keeps currencies separate and locale-aware', () => {
        expect(formatCurrency(1250, 'CZK', 'cs-CZ')).toContain('1\u00a0250');
        expect(formatCurrency(45, 'EUR', 'en-IE')).toContain('€45');
    });

    it('formats dates without hardcoded US ordering', () => {
        expect(formatDate('2026-08-24T12:00:00Z', 'cs-CZ')).toContain('24. 8. 2026');
    });
});
