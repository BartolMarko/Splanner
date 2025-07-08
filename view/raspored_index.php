<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<div id="raspored-header">
    <div id="raspored-title-wrapper" class="flex-row">
        <h2 id="raspored-title"></h2>
    </div>

    <div id="filter-checkboxes" class="flex-row">

    </div>

    <div id="buttons-container" class="flex-row">
        <div id="time-buttons" class="flex-row">
            <button class="time-interval" id="day-button">Dan</button>
            <button class="time-interval" id="week-button">Tjedan</button>
            <button class="time-interval" id="month-button">Mjesec</button>
        </div>

        <div id="nav-buttons" class="flex-row">
            <button id="left-button" class="nav-button"><</button>
            <button id="today-button" class="nav-button">Danas</button>
            <button id="right-button" class="nav-button">></button>
        </div>
    </div>

</div>

<div id="raspored-activities-wrapper">
    <div id="raspored-container">
    
    </div>
    
    <div id="activities-container">
            
    </div>
</div>


<script src="view/raspored.js"></script>

<?php require_once __SITE_PATH . '/view/_footer.php'; ?>