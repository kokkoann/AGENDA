document.addEventListener('DOMContentLoaded', function() {
    const calendarElement = document.getElementById('calendar');
    const today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    function renderCalendar(month, year) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        let calendarHTML = '<table>';
        calendarHTML += '<tr>';
        for (let i = 0; i < 7; i++) {
            const dayName = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            calendarHTML += `<th>${dayName[i]}</th>`;
        }
        calendarHTML += '</tr><tr>';

        for (let i = 0; i < firstDay.getDay(); i++) {
            calendarHTML += '<td></td>';
        }

        for (let i = 1; i <= lastDay.getDate(); i++) {
            const date = new Date(year, month, i);
            const isToday = date.toDateString() === today.toDateString();
            const dayClass = isToday ? 'today' : 'day';
            calendarHTML += `<td class="${dayClass}">${i}</td>`;

            if (date.getDay() === 6) {
                calendarHTML += '</tr><tr>';
            }
        }

        calendarHTML += '</tr></table>';
        calendarElement.innerHTML = calendarHTML;
    }

    renderCalendar(currentMonth, currentYear);
});
