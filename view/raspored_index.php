<?php require_once __SITE_PATH . '/view/_header.php'; ?>

<div id="buttons-container">
    <div id="time-buttons">
        <button class="time-interval" id="week-button">Tjedan</button>
        <button class="time-interval" id="month-button">Mjesec</button>
    </div>

    <div id="nav-buttons">
        <button id="left-button" class="nav-button"><</button>
        <button id="today-button" class="nav-button">Danas</button>
        <button id="right-button" class="nav-button">></button>
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