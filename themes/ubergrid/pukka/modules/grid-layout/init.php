<?php
    define('GRID_URI', PUKKA_URI .'/'. PUKKA_MODULES_DIR_NAME .'/grid-layout');

    define('PUKKA_DEFAULT_BOX_NO', 15);

    include_once('include/featured.content.class.php');
    include_once('include/functions.php');


    $pukka_featured_content = new PukkaFeaturedContent(); // Front page content