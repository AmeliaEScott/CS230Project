<?php

$app->get(
    '/',
    function() use($app) {
        $app->render('index.html', array(
          'sessionId' => $app->getCookie('sessid')
        ));
    }
);
