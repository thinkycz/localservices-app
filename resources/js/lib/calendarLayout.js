export function bookingStartMinutes(booking) {
    return Number(booking.startHour) * 60 + Number(booking.startMin);
}

export function bookingEndMinutes(booking) {
    return bookingStartMinutes(booking) + Number(booking.duration);
}

export function layoutOverlapGroup(bookings) {
    const columns = [];
    const layouts = new Map();

    for (const booking of bookings) {
        const start = bookingStartMinutes(booking);
        const end = bookingEndMinutes(booking);
        let columnIndex = columns.findIndex((columnEnd) => columnEnd <= start);

        if (columnIndex === -1) {
            columnIndex = columns.length;
            columns.push(end);
        } else {
            columns[columnIndex] = end;
        }

        layouts.set(booking.id, { ...booking, columnIndex });
    }

    return bookings.map((booking) => ({
        ...layouts.get(booking.id),
        columnCount: columns.length,
    }));
}

export function layoutBookingsForDay(bookings, fullDate) {
    const dayBookings = bookings.filter((booking) => booking.fullDate === fullDate);
    const blocked = dayBookings.filter((booking) => booking.colorType === 'blocked');
    const regular = dayBookings
        .filter((booking) => booking.colorType !== 'blocked')
        .sort((a, b) => bookingStartMinutes(a) - bookingStartMinutes(b)
            || bookingEndMinutes(b) - bookingEndMinutes(a));
    const layouts = [];
    let group = [];
    let groupEnd = -1;

    for (const booking of regular) {
        const start = bookingStartMinutes(booking);
        const end = bookingEndMinutes(booking);

        if (group.length && start >= groupEnd) {
            layouts.push(...layoutOverlapGroup(group));
            group = [];
            groupEnd = -1;
        }

        group.push(booking);
        groupEnd = Math.max(groupEnd, end);
    }

    if (group.length) layouts.push(...layoutOverlapGroup(group));

    return [...blocked, ...layouts];
}
