const DEFAULT_MIN_HOUR = 7;
const DEFAULT_MAX_HOUR = 22;

const DANI = ['Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
const DANI_SKR = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
const MJESECI = ['Siječanj', 'Veljača', 'Ožujak', 'Travanj', 'Svibanj', 'Lipanj', 
              'Srpanj', 'Kolovoz', 'Rujan', 'Listopad', 'Studeni', 'Prosinac'];

const TERMINI_URL_BASE = window.location.href + "/termini";

const $weekButton = $('#week-button');
const $monthButton = $('#month-button');

const $leftButton = $('#left-button');
const $todayButton = $('#today-button');
const $rightButton = $('#right-button');
const $rasporedContainer = $('#raspored-container');
const $activitiesContainer = $('#activities-container');

let weekReferenceMonday = getCurrentMonday();
let monthToDisplay = getCurrentMonth();

$( document ).ready(main);

function main() {
    $weekButton.on('click', displayWeekSchedule);
    $monthButton.on('click', displayMonthSchedule);

    $leftButton.on('click', decreaseReference);
    $rightButton.on('click', increaseReference);
    $todayButton.on('click', resetReference);
    displayWeekSchedule();
}

function displayWeekSchedule() {
    $(".time-interval").removeClass("active");
    $weekButton.addClass('active');
    $rasporedContainer.empty();
    $activitiesContainer.empty();

    const start = new Date(weekReferenceMonday);
    const end = new Date(weekReferenceMonday);
    end.setDate(start.getDate() + 6);

    const startStr = `${getTwoDigitNumber(start.getDate())}.${getTwoDigitNumber(start.getMonth() + 1)}.${start.getFullYear()}`;
    const endStr = `${getTwoDigitNumber(end.getDate())}.${getTwoDigitNumber(end.getMonth() + 1)}.${end.getFullYear()}`;

    $rasporedContainer.append(
        `<h2 class="week-range">${startStr} - ${endStr}</h2>`
    );

    fetchWeekActivitiesAndDisplay();
}

function displayMonthSchedule() {
    $(".time-interval").removeClass("active");
    $monthButton.addClass('active');
    $rasporedContainer.empty();

    const monthName = MJESECI[monthToDisplay.month];
    const year = monthToDisplay.year;
    $rasporedContainer.append(
        `<h2 class="month-title">${monthName} ${year}</h2>`
    );

    displayMonthGrid();
}

function fetchWeekActivitiesAndDisplay() {
    const start = new Date(weekReferenceMonday);
    const end = new Date(weekReferenceMonday);
    end.setDate(start.getDate() + 6);
    const datumOd = `${start.getFullYear()}-${getTwoDigitNumber(start.getMonth() + 1)}-${getTwoDigitNumber(start.getDate())}`;
    const datumDo = `${end.getFullYear()}-${getTwoDigitNumber(end.getMonth() + 1)}-${getTwoDigitNumber(end.getDate())}`;

    $.ajax({
        url: TERMINI_URL_BASE,
        method: 'GET',
        data: {
            datumOd: datumOd,
            datumDo: datumDo,
        },
        dataType: 'json',
        success: function(activities) {
            displayWeekGrid(DEFAULT_MIN_HOUR, DEFAULT_MAX_HOUR);
            displayWeekActivities(activities);
        },
        error: function() {
            console.error('Error pri dohvaćanju aktivnosti.');
        }
    });
}

function displayWeekGrid(min_hour, max_hour) {
    const $table = $('<table>').addClass('raspored-table');
    const $thead = $('<thead>');
    const $trHead = $('<tr>');
    $trHead.append($('<th>').text('Sat'));

    const today = new Date();
    let todayIndex = -1;
    for (let i = 0; i < DANI.length; i++) {
        const day = DANI[i];
        let date = new Date(weekReferenceMonday);
        date.setDate(weekReferenceMonday.getDate() + i);

        if (datesSame(date, today))
            todayIndex = i;

        const dayNum = getTwoDigitNumber(date.getDate());
        const monthNum = getTwoDigitNumber(date.getMonth() + 1);
        const $th = $('<th>')
            .attr('id', `day-${i}`)
            .html(`${day}<br><span class="date">${dayNum}.${monthNum}.</span>`);
        $trHead.append($th);
    }
    $thead.append($trHead);
    $table.append($thead);

    const $tbody = $('<tbody>');
    for (let hour = min_hour; hour <= max_hour; hour++) {
        const time = `${hour}:00`;
        const $tr = $('<tr id="time-' + hour + '">`);');
        $tr.append($('<td>').text(time));
        for (let i = 0; i < DANI.length; i++) {
            const $emptyField = $('<td>');
            if (i === todayIndex)
                $emptyField.addClass('today');
            $tr.append($emptyField);
        }
        $tbody.append($tr);
    }
    $table.append($tbody);
    $rasporedContainer.append($table);
}

function displayWeekActivities(activities) {
    parseActivityDates(activities);
    handleActivityOverlaps(activities);
    for (const activity of activities) {
        calculateWeekActivityYPosition(activity);
        calculateWeekActivityXPosition(activity);
        displayActivity(activity);
    }
}

function calculateWeekActivityYPosition(activity) {
    const startHour = activity.datePoc.getHours();
    const startMinute = activity.datePoc.getMinutes();
    const endHour = activity.dateKraj.getHours();
    const endMinute = activity.dateKraj.getMinutes();

    const tableRowStart = $(`tr#time-${startHour}`);
    let topPos = tableRowStart.offset().top - $rasporedContainer.offset().top;
    topPos += (startMinute / 60) * tableRowStart.height();

    const tableRowEnd = $(`tr#time-${endHour}`);
    const height = tableRowEnd.offset().top - tableRowStart.offset().top + (endMinute / 60) * tableRowEnd.height();

    activity.top = topPos / $rasporedContainer.height() * 100;
    activity.height = height / $rasporedContainer.height() * 100;
}

function calculateWeekActivityXPosition(activity) {
    const date = new Date(activity.datum);
    const dayIndex = (date.getDay() + 6) % 7;
    const $th = $(`th#day-${dayIndex}`);
    let leftPos = $th.offset().left - $rasporedContainer.offset().left;
    leftPos += $th.outerWidth() * activity.leftBorder;
    const width = $th.outerWidth() * activity.widthRatio;

    activity.left = leftPos / $rasporedContainer.width() * 100;
    activity.width = width / $rasporedContainer.width() * 100;
}

function displayActivity(activity) {
    const startTime = getTwoDigitNumber(activity.datePoc.getHours()) + ':' + getTwoDigitNumber(activity.datePoc.getMinutes());
    const endTime = getTwoDigitNumber(activity.dateKraj.getHours()) + ':' + getTwoDigitNumber(activity.dateKraj.getMinutes());
    const $activityDiv = $('<div>')
        .addClass('activity')
        .css({
            position: 'absolute',
            top: activity.top + '%',
            height: activity.height + '%',
            left: activity.left + '%',
            width: activity.width + '%'
        })
        .html(`
            <span class="activity-time">${startTime} - ${endTime}</span><br>
            <span class="activity-title">${activity.comment}</span><br>
            <span class="activity-dvorana">${activity.dvorana}</span>
        `);
    $activitiesContainer.append($activityDiv);
}


function displayMonthGrid() {
    const $table = $('<table>').addClass('raspored-table');
    const $thead = $('<thead>');
    const $trHead = $('<tr>');

    for (let i = 0; i < DANI.length; i++) {
        const day = DANI[i];
        $trHead.append($('<th>').text(day));
    }
    $thead.append($trHead);
    $table.append($thead);

    const monthDays = new Date(monthToDisplay.year, monthToDisplay.month + 1, 0).getDate();
    const firstDayOfMonth = new Date(monthToDisplay.year, monthToDisplay.month, 1).getDay();
    
    const $tbody = $('<tbody>');
    let $tr = $('<tr>');
    
    for (let i = 1; i < firstDayOfMonth; i++) {
        $tr.append($('<td>'));
    }

    for (let day = 1; day <= monthDays; day++) {
        if ($tr.children().length === DANI.length) {
            $tbody.append($tr);
            $tr = $('<tr>');
        }
        const dateStr = `${getTwoDigitNumber(day)}.${getTwoDigitNumber(monthToDisplay.month + 1)}.${monthToDisplay.year}`;
        $tr.append($('<td>').html(`<span class="date">${dateStr}</span>`));
    }

    while ($tr.children().length < DANI.length) {
        $tr.append($('<td>'));
    }
    
    $tbody.append($tr);
    $table.append($tbody);
    
    $rasporedContainer.append($table);
}

function increaseReference() {
    if ($weekButton.hasClass('active')) {
        weekReferenceMonday.setDate(weekReferenceMonday.getDate() + 7);
        displayWeekSchedule();
    }
    else if ($monthButton.hasClass('active')) {
        monthToDisplay.month += 1;
        if (monthToDisplay.month > 11) {
            monthToDisplay.month = 0;
            monthToDisplay.year += 1;
        }
        displayMonthSchedule();
    }
}

function decreaseReference() {
    if ($weekButton.hasClass('active')) {
        weekReferenceMonday.setDate(weekReferenceMonday.getDate() - 7);
        displayWeekSchedule();
    }
    else if ($monthButton.hasClass('active')) {
        monthToDisplay.month -= 1;
        if (monthToDisplay.month < 0) {
            monthToDisplay.month = 11;
            monthToDisplay.year -= 1;
        }
        displayMonthSchedule();
    }
}

function resetReference() {
    // TODO: ostavit ovako ili uvijek i tjedan i mjesec resetat
    if ($weekButton.hasClass('active')) {
        weekReferenceMonday = getCurrentMonday();
        displayWeekSchedule();
    }
    else if ($monthButton.hasClass('active')) {
        monthToDisplay = getCurrentMonth();
        displayMonthSchedule();
    }
}

function getCurrentMonday() {
    const today = new Date();
    const dayOfWeek = today.getDay();
    const daysSinceMonday = (dayOfWeek + 6) % 7;
    let monday = new Date(today);
    monday.setDate(today.getDate() - daysSinceMonday);
    return monday;
}

function getCurrentMonth() {
    const today = new Date();
    return {
        month: today.getMonth(),
        year: today.getFullYear()
    };
}

function datesSame(date1, date2) {
    return date1.getDate() === date2.getDate() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getFullYear() === date2.getFullYear();
}

function handleActivityOverlaps(activities) {
    if (activities.length === 0)
        return;
    activities.sort((a, b) => {
        return a.datePoc - b.datePoc;
    });
    let highestEndDate = activities[0].dateKraj;
    let group = [];
    for (let activity of activities) {
        if (activity.datePoc < highestEndDate) {
            group.push(activity);
            if (activity.dateKraj > highestEndDate)
                highestEndDate = activity.dateKraj;

        } else {
            handleGroupOverlap(group);
            group = [activity];
            highestEndDate = activity.dateKraj;
        }
    }
    handleGroupOverlap(group);
}

function handleGroupOverlap(group) {
    const events = [];
    for (let i = 0; i < group.length; i++) {
        const activity = group[i];
        events.push({ time: activity.datePoc, type: 1, index: i });
        events.push({ time: activity.dateKraj, type: -1, index: i });
    }
    events.sort((a, b) => {
        if (a.time - b.time !== 0) 
            return a.time - b.time;
        return a.type - b.type;
    });

    let maxOverlap = 0;
    let current = 0;
    for (const event of events) {
        current += event.type;
        maxOverlap = Math.max(maxOverlap, current);
    }

    let freeStarts = new Set();
    for (let i = 0; i < maxOverlap; i++) 
        freeStarts.add(i);

    for (const event of events) {
        if (event.type === 1) {
            const activity = group[event.index];
            const startIndex = freeStarts.values().next().value;
            freeStarts.delete(startIndex);
            activity.startIndex = startIndex;
            activity.leftBorder = startIndex / maxOverlap;
        } else {
            const activity = group[event.index];
            freeStarts.add(activity.startIndex);
            freeStarts = new Set([...freeStarts].sort((a, b) => a - b));
        }
    }
    calculateActivityWidths(group);
    console.log(group);
}

function calculateActivityWidths(group) {
    for (const activity of group) {
        let rightBorder = 1.0;
        for (const otherActivity of group) {
            if (checkOverlap(activity, otherActivity) && otherActivity.leftBorder > activity.leftBorder)
                rightBorder = Math.min(rightBorder, otherActivity.leftBorder);
        }
        activity.widthRatio = rightBorder - activity.leftBorder;
    }
}

function parseActivityDates(activities) {
    for (const activity of activities) {
        activity.datePoc = parseDate(activity.datum, activity.vrijeme_poc);
        activity.dateKraj = parseDate(activity.datum, activity.vrijeme_kraj);
    }
}

function parseDate(dateString, timeString) {
    const [day, month, year] = dateString.split('-').map(Number);
    const [hours, minutes, seconds] = timeString.split(':').map(Number);
    return new Date(year, month - 1, day, hours, minutes, seconds);
}

function getTwoDigitNumber(num) {
    return num.toString().padStart(2, '0');
}

function checkOverlap(activity1, activity2) {
    return activity1.datePoc < activity2.dateKraj && activity2.datePoc < activity1.dateKraj;
}