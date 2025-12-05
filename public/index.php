<?php

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Path to the application directory
require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();

// Load the framework bootstrap file
require rtrim($paths->systemDirectory, '\\/') . DIRECTORY_SEPARATOR . 'bootstrap.php';
