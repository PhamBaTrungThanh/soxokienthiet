<?php
namespace App;

require "./bootstrap.php";

use App\Models\Lottery;

/**
 * __construct
 */
$lottery = Lottery::latest()->get();
var_dump(blank($lottery));
echo $blade->make("fetch");
