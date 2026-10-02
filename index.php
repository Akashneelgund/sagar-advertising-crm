<?php
/**
 * SAGAR ADVERTISING - CRM & Quotation Management System
 * Front Controller
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0'); // Set to 1 during local debug if required

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/core/App.php';

// Dispatch application
App::run();
