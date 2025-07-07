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

    const startStr = `${start.getDate().toString().padStart(2, '0')}.${(start.getMonth() + 1).toString().padStart(2, '0')}.${start.getFullYear()}`;
    const endStr = `${end.getDate().toString().padStart(2, '0')}.${(end.getMonth() + 1).toString().padStart(2, '0')}.${end.getFullYear()}`;

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
    const datumOd = `${start.getFullYear()}-${(start.getMonth() + 1).toString().padStart(2, '0')}-${start.getDate().toString().padStart(2, '0')}`;
    const datumDo = `${end.getFullYear()}-${(end.getMonth() + 1).toString().padStart(2, '0')}-${end.getDate().toString().padStart(2, '0')}`;

    const a=5;
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

        const dayNum = date.getDate().toString().padStart(2, '0');
        const monthNum = (date.getMonth() + 1).toString().padStart(2, '0');
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
    console.log(activities);
    for (const activity of activities) {
        calculateWeekActivityYPosition(activity);
        calculateWeekActivityXPosition(activity);
        displayActivity(activity);
        console.log(activity);
    }
}

function calculateWeekActivityYPosition(activity) {
    const startHour = activity.vrijeme_poc.split(':')[0];
    const startMinute = parseInt(activity.vrijeme_poc.split(':')[1], 10);
    const endHour = activity.vrijeme_kraj.split(':')[0];
    const endMinute = parseInt(activity.vrijeme_kraj.split(':')[1], 10);

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
    const leftPos = $th.offset().left - $rasporedContainer.offset().left;
    const width = $th.outerWidth();

    activity.left = leftPos / $rasporedContainer.width() * 100;
    activity.width = width / $rasporedContainer.width() * 100;
}

function displayActivity(activity) {

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
            <span class="activity-time">${activity.vrijeme_poc} - ${activity.vrijeme_kraj}</span><br>
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
        const dateStr = `${day.toString().padStart(2, '0')}.${(monthToDisplay.month + 1).toString().padStart(2, '0')}.${monthToDisplay.year}`;
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