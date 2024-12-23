<?php
require 'config/constants.php';

//destroy all session variables and redirect to login page
session_destroy();
header('location: ' . ROOT_URL);
die();