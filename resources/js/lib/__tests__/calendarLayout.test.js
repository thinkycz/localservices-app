import { describe, expect, it } from 'vitest';
import { layoutBookingsForDay } from '../calendarLayout';

const booking = (id, hour, minute, duration, date = '2026-08-24') => ({
    id,
    fullDate: date,
    startHour: hour,
    startMin: minute,
    duration,
    colorType: 'blue',
});

describe('calendar overlap layout', () => {
    it('puts simultaneous bookings into separate columns', () => {
        const result = layoutBookingsForDay([
            booking(1, 9, 0, 60),
            booking(2, 9, 30, 60),
            booking(3, 10, 0, 30),
        ], '2026-08-24');

        expect(result.map(({ id, columnIndex, columnCount }) => ({ id, columnIndex, columnCount }))).toEqual([
            { id: 1, columnIndex: 0, columnCount: 2 },
            { id: 2, columnIndex: 1, columnCount: 2 },
            { id: 3, columnIndex: 0, columnCount: 2 },
        ]);
    });

    it('starts a fresh full-width group after the overlap ends', () => {
        const result = layoutBookingsForDay([
            booking(1, 9, 0, 30),
            booking(2, 9, 0, 30),
            booking(3, 10, 0, 30),
        ], '2026-08-24');

        expect(result.find(({ id }) => id === 3)).toMatchObject({ columnIndex: 0, columnCount: 1 });
    });
});
