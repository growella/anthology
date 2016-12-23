<?php
/**
 * Plugin Name: Anthology
 * Description: Automatically cross-link WordPress content in the same series.
 * Version:     0.1.0
 * Author:      Growella
 * Author URI:  https://growella.com
 * License:     MIT
 *
 * @package Growella\Anthology
 * @author  Growella
 */

namespace Growella\Anthology;

define( 'ANTHOLOGY_VERSION', '0.1.0' );

require_once __DIR__ . '/includes/core.php';
require_once __DIR__ . '/includes/taxonomy.php';
