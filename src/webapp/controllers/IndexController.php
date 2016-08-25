<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\NewsItem;

class IndexController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->render('index.twig');
    }
}
