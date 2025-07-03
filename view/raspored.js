const DEFAULT_MIN_HOUR = 7;
const DEFAULT_MAX_HOUR = 22;

const DANI = ['Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
const DANI_SKR = ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'];
const MJESECI = ['Siječanj', 'Veljača', 'Ožujak', 'Travanj', 'Svibanj', 'Lipanj', 
              'Srpanj', 'Kolovoz', 'Rujan', 'Listopad', 'Studeni', 'Prosinac'];

const weekButton = $('#week-button');
const monthButton = $('#month-button');

const leftButton = $('#left-button');
const todayButton = $('#today-button');
const rightButton = $('#right-button');

let weekReferenceMonday = getCurrentMonday();
let monthToDisplay = getCurrentMonth();

$( document ).ready(main);

function main() {
    weekButton.on('click', displayWeekSchedule);
    monthButton.on('click', displayMonthSchedule);

    leftButton.on('click', decreaseReference);
    rightButton.on('click', increaseReference);
    todayButton.on('click', resetReference);
    displayWeekSchedule();
}

function displayWeekSchedule(min_hour = DEFAULT_MIN_HOUR, max_hour = DEFAULT_MAX_HOUR) {
    $(".time-interval").removeClass("active");
    weekButton.addClass('active');
    $('#raspored-container').empty();

    const start = new Date(weekReferenceMonday);
    const end = new Date(weekReferenceMonday);
    end.setDate(start.getDate() + 6);

    const startStr = `${start.getDate().toString().padStart(2, '0')}.${(start.getMonth() + 1).toString().padStart(2, '0')}.${start.getFullYear()}`;
    const endStr = `${end.getDate().toString().padStart(2, '0')}.${(end.getMonth() + 1).toString().padStart(2, '0')}.${end.getFullYear()}`;

    $('#raspored-container').append(
        `<h2 class="week-range">${startStr} - ${endStr}</h2>`
    );

    displayWeekGrid(min_hour, max_hour);
}

function displayMonthSchedule() {
    $(".time-interval").removeClass("active");
    monthButton.addClass('active');
    $('#raspored-container').empty();

    const monthName = MJESECI[monthToDisplay.month];
    const year = monthToDisplay.year;
    $('#raspored-container').append(
        `<h2 class="month-title">${monthName} ${year}</h2>`
    );

    displayMonthGrid();
}

function displayWeekGrid(min_hour, max_hour) {
    const $table = $('<table>').addClass('raspored-table');
    const $thead = $('<thead>');
    const $trHead = $('<tr>');
    $trHead.append($('<th>').text('Sat'));

    for (let i = 0; i < DANI.length; i++) {
        const day = DANI[i];
        let date = new Date(weekReferenceMonday);
        date.setDate(weekReferenceMonday.getDate() + i);
        const dayNum = date.getDate().toString().padStart(2, '0');
        const monthNum = (date.getMonth() + 1).toString().padStart(2, '0');
        const $th = $('<th>').html(`${day}<br><span class="date">${dayNum}.${monthNum}.</span>`);
        $trHead.append($th);
    }
    $thead.append($trHead);
    $table.append($thead);

    const $tbody = $('<tbody>');
    for (let hour = min_hour; hour <= max_hour; hour++) {
        const time = `${hour}:00`;
        const $tr = $('<tr>');
        $tr.append($('<td>').text(time));
        for (let i = 0; i < DANI.length; i++) {
            const $emptyField = $('<td>');
            $tr.append($emptyField);
        }
        $tbody.append($tr);
    }
    $table.append($tbody);
    $('#raspored-container').append($table);
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
    
    $('#raspored-container').append($table);
}

function increaseReference() {
    if (weekButton.hasClass('active')) {
        weekReferenceMonday.setDate(weekReferenceMonday.getDate() + 7);
        displayWeekSchedule();
    }
    else if (monthButton.hasClass('active')) {
        monthToDisplay.month += 1;
        if (monthToDisplay.month > 11) {
            monthToDisplay.month = 0;
            monthToDisplay.year += 1;
        }
        displayMonthSchedule();
    }
}

function decreaseReference() {
    if (weekButton.hasClass('active')) {
        weekReferenceMonday.setDate(weekReferenceMonday.getDate() - 7);
        displayWeekSchedule();
    }
    else if (monthButton.hasClass('active')) {
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
    if (weekButton.hasClass('active')) {
        weekReferenceMonday = getCurrentMonday();
        displayWeekSchedule();
    }
    else if (monthButton.hasClass('active')) {
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