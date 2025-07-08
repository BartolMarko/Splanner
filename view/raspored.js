const DEFAULT_MIN_HOUR = 7;
const DEFAULT_MAX_HOUR = 22;

const DANI = ['Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
const DANI_SKR = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
const MJESECI = ['Siječanj', 'Veljača', 'Ožujak', 'Travanj', 'Svibanj', 'Lipanj', 
              'Srpanj', 'Kolovoz', 'Rujan', 'Listopad', 'Studeni', 'Prosinac'];

const COLORS = [
    '#e6194b', // red
    '#ffe119', // yellow
    '#3cb44b', // green
    '#4363d8', // blue
    '#46f0f0', // cyan
    '#bcf60c', // lime
    '#911eb4', // purple
    '#f032e6', // magenta
    '#f58231', // orange
    '#fabebe',  // pink
];

const TERMINI_URL_BASE = window.location.href + "/termini";
const USER_INFO_URL = window.location.href + "/userinfo";

const $dayButton = $('#day-button');
const $weekButton = $('#week-button');
const $monthButton = $('#month-button');
const $DEFAULT_BUTTON = $weekButton;

const $leftButton = $('#left-button');
const $todayButton = $('#today-button');
const $rightButton = $('#right-button');

const $rasporedTitle = $('#raspored-title');
const $rasporedContainer = $('#raspored-container');
const $activitiesContainer = $('#activities-container');
const $filterCheckboxes = $('#filter-checkboxes');

let userInfo = null;
let checkBoxCount = 0, idToIndex = {}, indexToId = {};
let latestActivitiesFetched = null, filteredActivities = null;
// latestActivities -> id: aktivnosti, filteredActivities -> lista aktivnosti

let dayToDisplay = new Date(); 
let weekReferenceMonday = getCurrentMonday();
let monthToDisplay = getCurrentMonth();

$( document ).ready(main);

function main() {
    $dayButton.on('click', onPeriodChange);
    $weekButton.on('click', onPeriodChange);
    $monthButton.on('click', onPeriodChange);

    $leftButton.on('click', decreaseReference);
    $rightButton.on('click', increaseReference);
    $todayButton.on('click', resetReference);
    $DEFAULT_BUTTON.addClass('active');
    fetchUserInfo();
}

function fetchUserInfo() {
    $.ajax({
        url: USER_INFO_URL,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            userInfo = data;
            displayFilterCheckboxes();
            displayCompleteSchedule();
        },
        error: function() {
            console.error('Error pri dohvaćanju user info.');
        }
    });
}

function displayFilterCheckboxes() {
    $filterCheckboxes.empty();
    if (userInfo.tip_korisnika === 'roditelj') {
        addCheckBox(userInfo.id_korisnici, "Moje aktivnosti");
        for (const dijete of userInfo.djeca)
            addCheckBox(dijete.id_korisnici, dijete.username);
    } else if (userInfo.tip_korisnika === 'dijete') {
        addCheckBox(userInfo.id_korisnici, "Moje aktivnosti", true);
    }
}

function addCheckBox(id, name, hidden=false) {
    let index = checkBoxCount++;
    idToIndex[id] = index;
    indexToId[index] = id;

    const $checkbox = $('<input>')
        .attr('type', 'checkbox')
        .attr('id', "checkbox-" + index)
        .prop('checked', true)
        .on('change', function() {
            // if ($(this).is(':checked')) {
            //     $(`#checkbox-label-${index}`).css(
            //         'background-color', COLORS[index % COLORS.length]
            //     );
            // } else {
            //     $(`#checkbox-label-${index}`).css(
            //         'background-color', getLighterColor(COLORS[index % COLORS.length])
            //     );
            // }
            filterActivities();
            displayActivities(filteredActivities);
        });
    
    const $label = $('<label>')
        .addClass('checkbox-button')
        .attr('id', "checkbox-label-" + index)
        .attr('for', "checkbox-" + index)
        .text(name)
        .css(getDefaultCssForIndex(index));

    if (hidden) {
        $checkbox.hide();
        $label.hide();
    }

    $filterCheckboxes.append($checkbox);
    $filterCheckboxes.append($label);
    // $filterCheckboxes.append('<br>');
}

function filterActivities() {
    if (latestActivitiesFetched === null) {
        console.warn('Nema aktivnosti za filtriranje.');
        return;
    }
    filteredActivities = [];
    for (const [id, activities] of Object.entries(latestActivitiesFetched)) {
        const index = idToIndex[id];
        if (index === undefined)
            continue;
        const isChecked = $(`#checkbox-${index}`).is(':checked');
        if (isChecked) {
            for (const activity of activities)
                activity.index = index;
            filteredActivities = filteredActivities.concat(activities);
        }
    }
}

function onPeriodChange() {
    if ($(this).hasClass('active'))
        return;

    $(".time-interval").removeClass("active");
    $(this).addClass('active');

    displayCompleteSchedule();
}

function displayCompleteSchedule() {
    $rasporedContainer.empty();

    if ($dayButton.hasClass('active'))
        displayScheduleGrid(DEFAULT_MAX_HOUR - DEFAULT_MIN_HOUR + 1, 1, true);
    else if ($weekButton.hasClass('active'))
        displayScheduleGrid(DEFAULT_MAX_HOUR - DEFAULT_MIN_HOUR + 1, 7, true);
    else if ($monthButton.hasClass('active'))
        displayScheduleGrid(5, 7, false);

    fetchActivitiesAndDisplay();
}

function displayScheduleGrid(rows, columns, displayHours) {
    const $table = $('<table>').addClass('raspored-table');
    const $thead = $('<thead>');
    const $trHead = $('<tr>');

    if (displayHours)
        $trHead.append($('<th>').addClass('hours-column').text('Sat'));

    for (let i = 0; i < columns; i++) {
        $th = $('<th>').attr('id', `day-${i}`).text(DANI[i])
        if (displayHours) {
            $th.append($('<br>'));
            $th.append($(`<span id="date-day-${i}">`));
        }
        $trHead.append($th);
    }
    $thead.append($trHead);
    $table.append($thead);

    const $tbody = $('<tbody>');
    for (let row = 0; row < rows; row++) {
        const $tr = $('<tr>');
        if (displayHours) {
            $tr.attr('id', `time-${DEFAULT_MIN_HOUR + row}`);
            const hour = DEFAULT_MIN_HOUR + row;
            const timeStr = `${getTwoDigitNumber(hour)}:00 - ${getTwoDigitNumber(hour + 1)}:00`;
            $tr.append($('<td>').addClass('hours-column').text(timeStr));
        }
        for (let col = 0; col < columns; col++)
            $tr.append($('<td>'));
        $tbody.append($tr);
    }
    $table.append($tbody);
    $rasporedContainer.append($table);
}

function fetchActivitiesAndDisplay() {
    let start, end;
    if ($dayButton.hasClass('active'))
        start = end = dayToDisplay;
    else if ($weekButton.hasClass('active')) {
        start = new Date(weekReferenceMonday);
        end = new Date(weekReferenceMonday);
        end.setDate(start.getDate() + 6);
    } else if ($monthButton.hasClass('active')) {
        start = getFirstDayOfMonth(monthToDisplay.year, monthToDisplay.month);
        end = getLastDayOfMonth(monthToDisplay.year, monthToDisplay.month);
    }

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
            latestActivitiesFetched = activities;
            filterActivities();
            adjustGrid(activities);
            displayActivities(filteredActivities);
        },
        error: function() {
            console.error('Error pri dohvaćanju aktivnosti.');
        }
    });
}

function adjustGrid(activities) {
    if ($dayButton.hasClass('active'))
        adjustDayGrid(DEFAULT_MIN_HOUR, DEFAULT_MAX_HOUR);
    else if ($weekButton.hasClass('active'))
        adjustWeekGrid(DEFAULT_MIN_HOUR, DEFAULT_MAX_HOUR);
    else if ($monthButton.hasClass('active'))
        adjustMonthGrid();
}

function adjustDayGrid(min_hour, max_hour) {
    const dateStr = getDateString(dayToDisplay);
    $rasporedTitle.text(dateStr);

    const dayNum = getTwoDigitNumber(dayToDisplay.getDate());
    const monthNum = getTwoDigitNumber(dayToDisplay.getMonth() + 1);
    
    $(`th#day-0`).text(DANI[getDayOfWeekIndex(dayToDisplay)]);
    $(`th#day-0`).append($('<br>'));
    $(`th#day-0`).append($(`<span id="date-day-0">${dayNum}.${monthNum}</span>`));
}

function adjustWeekGrid(min_hour, max_hour) {
    const start = new Date(weekReferenceMonday);
    const end = new Date(weekReferenceMonday);
    end.setDate(start.getDate() + 6);
    const today = new Date();

    const startStr = getDateString(start);
    const endStr = getDateString(end);

    $rasporedTitle.text(`${startStr} - ${endStr}`);

    let todayIndex = -1;
    for (let i = 0; i < DANI.length; i++) {
        let date = new Date(weekReferenceMonday);
        date.setDate(weekReferenceMonday.getDate() + i);

        if (datesSame(date, today))
            todayIndex = i;

        const dayNum = getTwoDigitNumber(date.getDate());
        const monthNum = getTwoDigitNumber(date.getMonth() + 1);
        $(`#date-day-${i}`).text(`${dayNum}.${monthNum}`)
    }
    $('td').removeClass('today');
    if (todayIndex !== -1) {
        for (let hour = min_hour; hour <= max_hour; hour++) {
            const $timeCell = $(`tr#time-${hour} td:nth-child(${todayIndex + 2})`);
            $timeCell.addClass('today');
        }
    }
}

function adjustMonthGrid() {
    const monthName = MJESECI[monthToDisplay.month];
    const year = monthToDisplay.year;
    $rasporedTitle.text(`${monthName} ${year}`);
}

function displayActivities(activities) {
    $activitiesContainer.empty();
    parseActivityDates(activities);
    if ($dayButton.hasClass('active'))
        displayDayActivities(activities);
    else if ($weekButton.hasClass('active'))
        displayWeekActivities(activities);
}

function displayDayActivities(activities) {
    handleActivityOverlaps(activities);
    for (const activity of activities) {
        calculateDayActivityXPosition(activity);
        calculateWeekActivityYPosition(activity);
        displayActivity(activity);
    }
}

function displayWeekActivities(activities) {
    handleActivityOverlaps(activities);
    for (const activity of activities) {
        calculateWeekActivityXPosition(activity);
        calculateWeekActivityYPosition(activity);
        displayActivity(activity);
    }
}

function calculateDayActivityXPosition(activity) {
    const $th = $(`th#day-0`);
    let leftPos = $th.offset().left - $rasporedContainer.offset().left;
    leftPos += $th.outerWidth() * activity.leftBorder;
    const width = $th.outerWidth() * activity.widthRatio;

    activity.left = leftPos / $rasporedContainer.width() * 100;
    activity.width = width / $rasporedContainer.width() * 100;
}

function calculateWeekActivityXPosition(activity) {
    const dayIndex = getDayOfWeekIndex(new Date(activity.datum));
    const $th = $(`th#day-${dayIndex}`);
    let leftPos = $th.offset().left - $rasporedContainer.offset().left;
    leftPos += $th.outerWidth() * activity.leftBorder;
    const width = $th.outerWidth() * activity.widthRatio;

    activity.left = leftPos / $rasporedContainer.width() * 100;
    activity.width = width / $rasporedContainer.width() * 100;
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
    let bottomPos = tableRowEnd.offset().top - $rasporedContainer.offset().top;
    bottomPos += (endMinute / 60) * tableRowEnd.height();

    activity.top = topPos / $rasporedContainer.height() * 100;
    activity.height = (bottomPos - topPos) / $rasporedContainer.height() * 100;
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
        .css(getDefaultCssForIndex(activity.index))
        .html(`
            <span class="activity-time">${startTime} - ${endTime}</span><br>
            <span class="activity-title">${activity.ime_aktivnosti}</span><br>
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
    if ($dayButton.hasClass('active'))
        dayToDisplay.setDate(dayToDisplay.getDate() + 1);
    else if ($weekButton.hasClass('active'))
        weekReferenceMonday.setDate(weekReferenceMonday.getDate() + 7);
    else if ($monthButton.hasClass('active')) {
        monthToDisplay.month += 1;
        if (monthToDisplay.month > 11) {
            monthToDisplay.month = 0;
            monthToDisplay.year += 1;
        }
    }
    fetchActivitiesAndDisplay();
}

function decreaseReference() {
    if ($dayButton.hasClass('active'))
        dayToDisplay.setDate(dayToDisplay.getDate() - 1);
    else if ($weekButton.hasClass('active'))
        weekReferenceMonday.setDate(weekReferenceMonday.getDate() - 7);
    else if ($monthButton.hasClass('active')) {
        monthToDisplay.month -= 1;
        if (monthToDisplay.month < 0) {
            monthToDisplay.month = 11;
            monthToDisplay.year -= 1;
        }
    }
    fetchActivitiesAndDisplay();
}

function resetReference() {
    if ($dayButton.hasClass('active'))
        dayToDisplay = new Date();
    else if ($weekButton.hasClass('active'))
        weekReferenceMonday = getCurrentMonday();
    else if ($monthButton.hasClass('active'))
        monthToDisplay = getCurrentMonth();
    fetchActivitiesAndDisplay();
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

function getFirstDayOfMonth(year, month) {
    return new Date(year, month, 1);
}

function getLastDayOfMonth(year, month) {
    return new Date(year, month + 1, 0);
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

function getDateString(date) {
    return `${getTwoDigitNumber(date.getDate())}.${getTwoDigitNumber(date.getMonth() + 1)}.${date.getFullYear()}`;
}

function getDayOfWeekIndex(date) {
    return (date.getDay() + 6) % 7;
}

function getDefaultCssForIndex(index) {
    const color = COLORS[index % COLORS.length];
    return {
        'background-color': color,
        'border-color': getDarkerColor(color),
        'color': 'white'
    }
}

function getDarkerColor(color) {
    return chroma(color).darken(0.5).hex();
}

function getLighterColor(color) {
    return chroma(color).brighten(0.5).hex();
}